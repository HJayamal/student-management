<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AdminCrudController extends Controller
{
    /**
     * Only these eight tables can be accessed through the mobile Admin API.
     * The authenticated EnsureAdmin middleware must wrap every route.
     */
    private const RESOURCES = [
        'students' => [
            'table' => 'students',
            'label' => 'Students',
            'search' => ['reg_no', 'name', 'first_name', 'last_name', 'email', 'nic', 'phone'],
            'scope' => 'student_role',
        ],
        'teachers' => [
            'table' => 'teachers',
            'label' => 'Teachers',
            'search' => ['name', 'teacher_name', 'first_name', 'last_name', 'email', 'phone', 'subject'],
        ],
        'admissions' => [
            'table' => 'admissions',
            'label' => 'Admissions',
            'search' => ['name', 'student_name', 'reg_no', 'email', 'admission_no', 'nic'],
        ],
        'courses' => [
            'table' => 'courses',
            'label' => 'Courses',
            'search' => ['course_name', 'course_code', 'name'],
        ],
        'degrees' => [
            'table' => 'degrees',
            'label' => 'Degrees',
            'search' => ['degree_name', 'degree_code', 'name'],
        ],
        'batches' => [
            'table' => 'batches',
            'label' => 'Batches',
            'search' => ['batch_name', 'batch_code', 'name'],
        ],
        'subjects' => [
            'table' => 'subjects',
            'label' => 'Subjects',
            'search' => ['subject_name', 'subject_code', 'course_name', 'teacher'],
        ],
        'exams' => [
            'table' => 'exams',
            'label' => 'Exams',
            'search' => ['exam_name', 'title', 'name', 'subject'],
        ],
    ];

    /**
     * Foreign-key option sources used to render dropdowns in the mobile forms.
     * These are deliberately allow-listed; request data cannot choose a table.
     */
    private const LOOKUP_TABLES = [
        'student_id' => 'students',
        'teacher_id' => 'teachers',
        'admission_id' => 'admissions',
        'course_id' => 'courses',
        'degree_id' => 'degrees',
        'batch_id' => 'batches',
        'subject_id' => 'subjects',
        'exam_id' => 'exams',
    ];

    public function schema(string $resource)
    {
        [$config, $table, $columns] = $this->resolveResource($resource);
        $primaryKey = $this->primaryKey($columns);
        $fields = [];

        foreach ($columns as $column) {
            $name = $column->Field;
            $type = strtolower((string) $column->Type);
            $extra = strtolower((string) $column->Extra);

            if ($name === $primaryKey || in_array($name, [
                'created_at', 'updated_at', 'deleted_at', 'remember_token',
                'email_verified_at', 'image', 'photo', 'profile_image',
            ], true)) {
                continue;
            }

            // Student accounts created here are always student accounts.
            if (($config['scope'] ?? null) === 'student_role' && $name === 'role') {
                continue;
            }

            if (str_contains($type, 'blob') || str_contains($type, 'binary')) {
                continue;
            }

            $field = [
                'name' => $name,
                'label' => Str::of($name)->replace('_', ' ')->title()->toString(),
                'required' => $column->Null === 'NO'
                    && $column->Default === null
                    && !str_contains($extra, 'auto_increment')
                    && $name !== 'password',
                'nullable' => $column->Null === 'YES',
                'default' => $column->Default,
                'sensitive' => $name === 'password',
                'type' => 'text',
                'options' => [],
            ];

            if ($name === 'password') {
                $field['type'] = 'password';
                $field['required'] = true;
                $field['helper'] = 'Enter a temporary password. It will be securely hashed before saving.';
                $fields[] = $field;
                continue;
            }

            $lookupTable = self::LOOKUP_TABLES[$name] ?? null;
            if ($lookupTable && Schema::hasTable($lookupTable)) {
                $field['type'] = 'select';
                $field['options'] = $this->lookupOptions($lookupTable);
                $fields[] = $field;
                continue;
            }

            if (preg_match('/^enum\((.*)\)$/i', $type, $matches)) {
                $values = str_getcsv($matches[1], ',', "'");
                $field['type'] = 'select';
                $field['options'] = array_map(static fn ($value) => [
                    'value' => $value,
                    'label' => Str::of($value)->replace('_', ' ')->title()->toString(),
                ], $values);
                $fields[] = $field;
                continue;
            }

            if (preg_match('/^(tinyint\(1\)|boolean|bool)/i', $type)) {
                $field['type'] = 'select';
                $field['options'] = [
                    ['value' => '1', 'label' => 'Yes'],
                    ['value' => '0', 'label' => 'No'],
                ];
                $fields[] = $field;
                continue;
            }

            if (preg_match('/^date$/', $type)) {
                $field['type'] = 'date';
            } elseif (preg_match('/^(datetime|timestamp)/', $type)) {
                $field['type'] = 'datetime';
            } elseif (preg_match('/^(int|bigint|smallint|mediumint|decimal|float|double)/', $type)) {
                $field['type'] = 'number';
            } elseif (preg_match('/^(text|mediumtext|longtext)/', $type) || in_array($name, ['description', 'address', 'remarks', 'note'], true)) {
                $field['type'] = 'multiline';
            }

            $fields[] = $field;
        }

        return response()->json([
            'resource' => $resource,
            'title' => $config['label'],
            'primary_key' => $primaryKey,
            'fields' => $fields,
        ]);
    }

    public function index(Request $request, string $resource)
    {
        [$config, $table, $columns] = $this->resolveResource($resource);
        $primaryKey = $this->primaryKey($columns);
        $safeColumns = $this->safeOutputColumns($columns);

        $query = DB::table($table)->select($safeColumns);
        $this->applyResourceScope($query, $config, $columns);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $searchColumns = array_values(array_intersect($config['search'] ?? [], array_column($columns, 'Field')));
            if (count($searchColumns) > 0) {
                $query->where(function ($nested) use ($searchColumns, $search) {
                    foreach ($searchColumns as $column) {
                        $nested->orWhere($column, 'LIKE', '%' . $search . '%');
                    }
                });
            }
        }

        $perPage = min(max((int) $request->query('per_page', 30), 1), 100);
        $records = $query->orderBy($primaryKey, 'desc')->paginate($perPage);

        return response()->json([
            'resource' => $resource,
            'title' => $config['label'],
            'records' => $records,
        ]);
    }

    public function store(Request $request, string $resource)
    {
        [$config, $table, $columns] = $this->resolveResource($resource);
        $payload = $this->validatedPayload($request, $config, $table, $columns, null, false);

        if (($config['scope'] ?? null) === 'student_role' && in_array('role', array_column($columns, 'Field'), true)) {
            $payload['role'] = 'student';
        }

        if (array_key_exists('password', $payload) && $payload['password'] !== null && $payload['password'] !== '') {
            $payload['password'] = Hash::make($payload['password']);
        }

        if (in_array('created_at', array_column($columns, 'Field'), true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', array_column($columns, 'Field'), true)) {
            $payload['updated_at'] = now();
        }

        try {
            $id = DB::table($table)->insertGetId($payload);
            return response()->json([
                'message' => $config['label'] . ' record created successfully.',
                'id' => $id,
            ], 201);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json([
                'message' => 'The record could not be saved. Check required fields and duplicate values.',
            ], 422);
        }
    }

    public function update(Request $request, string $resource, string $id)
    {
        [$config, $table, $columns] = $this->resolveResource($resource);
        $primaryKey = $this->primaryKey($columns);

        $query = DB::table($table)->where($primaryKey, $id);
        $this->applyResourceScope($query, $config, $columns);
        if (!$query->exists()) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        $payload = $this->validatedPayload($request, $config, $table, $columns, $id, true);

        if (($config['scope'] ?? null) === 'student_role') {
            unset($payload['role']);
        }

        if (array_key_exists('password', $payload)) {
            if ($payload['password'] === null || $payload['password'] === '') {
                unset($payload['password']);
            } else {
                $payload['password'] = Hash::make($payload['password']);
            }
        }

        if (in_array('updated_at', array_column($columns, 'Field'), true)) {
            $payload['updated_at'] = now();
        }

        if (count($payload) === 0) {
            return response()->json(['message' => 'There are no changes to save.']);
        }

        try {
            $query->update($payload);
            return response()->json(['message' => $config['label'] . ' record updated successfully.']);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json([
                'message' => 'The record could not be updated. Check required fields and duplicate values.',
            ], 422);
        }
    }

    public function destroy(string $resource, string $id)
    {
        [$config, $table, $columns] = $this->resolveResource($resource);
        $primaryKey = $this->primaryKey($columns);

        $query = DB::table($table)->where($primaryKey, $id);
        $this->applyResourceScope($query, $config, $columns);
        if (!$query->exists()) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        try {
            $query->delete();
            return response()->json(['message' => $config['label'] . ' record deleted successfully.']);
        } catch (QueryException $exception) {
            report($exception);
            return response()->json([
                'message' => 'This record cannot be deleted because other records may depend on it. Remove or reassign those records first.',
            ], 409);
        }
    }

    private function resolveResource(string $resource): array
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404, 'Admin resource not found.');
        $config = self::RESOURCES[$resource];
        $table = $config['table'];
        abort_unless(Schema::hasTable($table), 404, "The {$table} table does not exist in this database.");
        $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
        abort_if(count($columns) === 0, 404, "The {$table} table has no columns.");
        return [$config, $table, $columns];
    }

    private function primaryKey(array $columns): string
    {
        foreach ($columns as $column) {
            if (($column->Key ?? null) === 'PRI') {
                return $column->Field;
            }
        }
        return 'id';
    }

    private function safeOutputColumns(array $columns): array
    {
        $hidden = ['password', 'remember_token', 'email_verified_at'];
        return array_values(array_map(
            static fn ($column) => $column->Field,
            array_filter($columns, static fn ($column) => !in_array($column->Field, $hidden, true)
                && !str_contains(strtolower((string) $column->Type), 'blob')
                && !str_contains(strtolower((string) $column->Type), 'binary'))
        ));
    }

    private function applyResourceScope($query, array $config, array $columns): void
    {
        if (($config['scope'] ?? null) === 'student_role'
            && in_array('role', array_column($columns, 'Field'), true)) {
            $query->where('role', 'student');
        }
    }

    private function lookupOptions(string $table): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        $columns = Schema::getColumnListing($table);
        if (!in_array('id', $columns, true)) {
            return [];
        }

        $labelColumn = null;
        foreach (['name', 'student_name', 'teacher_name', 'course_name', 'degree_name', 'batch_name', 'subject_name', 'exam_name', 'title', 'reg_no', 'email'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                $labelColumn = $candidate;
                break;
            }
        }

        $query = DB::table($table)->select('id');
        if ($labelColumn) {
            $query->addSelect($labelColumn);
        }
        if ($table === 'students' && in_array('role', $columns, true)) {
            $query->where('role', 'student');
        }

        return $query->orderBy($labelColumn ?: 'id')->limit(200)->get()->map(function ($row) use ($labelColumn) {
            $label = $labelColumn ? trim((string) ($row->{$labelColumn} ?? '')) : '';
            return [
                'value' => (string) $row->id,
                'label' => $label !== '' ? $label . ' (#' . $row->id . ')' : 'Record #' . $row->id,
            ];
        })->all();
    }

    private function validatedPayload(Request $request, array $config, string $table, array $columns, ?string $id, bool $updating): array
    {
        $primaryKey = $this->primaryKey($columns);
        $blocked = [
            $primaryKey, 'created_at', 'updated_at', 'deleted_at', 'remember_token',
            'email_verified_at', 'image', 'photo', 'profile_image',
        ];
        if (($config['scope'] ?? null) === 'student_role') {
            $blocked[] = 'role';
        }

        $columnNames = array_column($columns, 'Field');
        $editable = array_values(array_diff($columnNames, $blocked));
        $payload = $request->only($editable);

        $rules = [];
        foreach ($columns as $column) {
            $name = $column->Field;
            if (!in_array($name, $editable, true)) {
                continue;
            }

            $type = strtolower((string) $column->Type);
            $isRequired = $column->Null === 'NO'
                && $column->Default === null
                && !str_contains(strtolower((string) $column->Extra), 'auto_increment');
            $fieldRules = [];
            $fieldRules[] = ($isRequired && $name !== 'password') ? 'required' : 'nullable';

            if ($name === 'password') {
                $fieldRules = [$updating ? 'nullable' : 'required', 'string', 'min:6', 'max:255'];
            } elseif ($name === 'email') {
                $fieldRules[] = 'email';
                if (($column->Key ?? null) === 'UNI') {
                    $unique = Rule::unique($table, $name);
                    if ($id !== null) $unique->ignore($id, $primaryKey);
                    $fieldRules[] = $unique;
                }
            } elseif (preg_match('/^enum\((.*)\)$/i', $type, $matches)) {
                $values = str_getcsv($matches[1], ',', "'");
                $fieldRules[] = Rule::in($values);
            } elseif (preg_match('/^(tinyint\(1\)|boolean|bool)/i', $type)) {
                $fieldRules[] = 'boolean';
            } elseif (preg_match('/^(int|bigint|smallint|mediumint)/', $type)) {
                $fieldRules[] = 'integer';
            } elseif (preg_match('/^(decimal|float|double)/', $type)) {
                $fieldRules[] = 'numeric';
            } elseif (preg_match('/^(date|datetime|timestamp)/', $type)) {
                $fieldRules[] = 'date';
            } elseif (str_contains($type, 'char') || str_contains($type, 'text')) {
                $maxLength = 255;
                if (preg_match('/\((\d+)\)/', $type, $lengthMatch)) {
                    $maxLength = (int) $lengthMatch[1];
                }
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:' . min(max($maxLength, 1), 65535);
            }

            if (isset(self::LOOKUP_TABLES[$name]) && Schema::hasTable(self::LOOKUP_TABLES[$name])) {
                $fieldRules[] = 'exists:' . self::LOOKUP_TABLES[$name] . ',id';
            }

            if (($column->Key ?? null) === 'UNI' && $name !== 'email' && $name !== 'password') {
                $unique = Rule::unique($table, $name);
                if ($id !== null) $unique->ignore($id, $primaryKey);
                $fieldRules[] = $unique;
            }

            $rules[$name] = $fieldRules;
        }

        $validator = validator($payload, $rules);
        if ($validator->fails()) {
            abort(response()->json([
                'message' => 'Please check the highlighted fields.',
                'errors' => $validator->errors(),
            ], 422));
        }

        return $validator->validated();
    }
}
