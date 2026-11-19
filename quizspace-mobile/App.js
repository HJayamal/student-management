import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
    ActivityIndicator,
    Alert,
    KeyboardAvoidingView,
    Linking,
    Modal,
    Platform,
    Pressable,
    ScrollView,
    StatusBar,
    StyleSheet,
    Text,
    TextInput,
    View,
} from 'react-native';
import * as SecureStore from 'expo-secure-store';
import * as DocumentPicker from 'expo-document-picker';
import { createApi, getApiError, normalizeApiBaseUrl } from './src/api';
import { formatDate, formatDuration } from './src/format';
import StudentNotificationsScreen from './src/StudentNotificationsScreen';

const DEFAULT_API_BASE = 'http://10.0.2.2:8000/api';

const COLORS = {
    ink: '#18233F',
    muted: '#73809A',
    line: '#E5EAF4',
    background: '#F5F7FC',
    white: '#FFFFFF',
    blue: '#5969F6',
    purple: '#7857E8',
    green: '#16845B',
    greenBg: '#E5F8EF',
    red: '#B42332',
    redBg: '#FFF1F2',
    amber: '#95600A',
    amberBg: '#FFF5D9',
    navy: '#191F3F',
};

const KEYS = {
    base: 'quizspace_api_base',
    token: 'quizspace_auth_token',
    role: 'quizspace_role',
    user: 'quizspace_user',
};

const MAX_MATERIAL_BYTES = 25 * 1024 * 1024;

const ADMIN_STAT_ITEMS = [
    { key: 'students', label: 'Students', symbol: '👥', accent: '#5969F6' },
    { key: 'teachers', label: 'Teachers', symbol: '🧑‍🏫', accent: '#16845B' },
    { key: 'admissions', label: 'Admissions', symbol: '➕', accent: '#7857E8' },
    { key: 'courses', label: 'Courses', symbol: '🎓', accent: '#B7791F' },
    { key: 'degrees', label: 'Degrees', symbol: '🏅', accent: '#0F766E' },
    { key: 'batches', label: 'Batches', symbol: '▦', accent: '#475569' },
    { key: 'subjects', label: 'Subjects', symbol: '📚', accent: '#2563EB' },
    { key: 'exams', label: 'Exams', symbol: '📝', accent: '#C2410C' },
];

const EMPTY_QUESTION = () => ({
    question_text: '',
    option_a: '',
    option_b: '',
    option_c: '',
    option_d: '',
    correct_option: 'A',
    marks: '1',
});

function makeFutureLocalDateTime() {
    const date = new Date(Date.now() + 15 * 60 * 1000);
    const pad = (value) => String(value).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function isUpcomingQuiz(quiz) {
    if (quiz?.status !== 'published' || !quiz?.available_at) return false;

    // Laravel may return a SQL timestamp without a timezone; treat it as UTC.
    const raw = String(quiz.available_at).trim();
    const normalized = raw.includes('T') ? raw : raw.replace(' ', 'T');
    const hasTimezone = /(?:Z|[+-]\d{2}:?\d{2})$/i.test(normalized);
    const availableAt = new Date(hasTimezone ? normalized : `${normalized}Z`);

    return !Number.isNaN(availableAt.getTime()) && availableAt.getTime() > Date.now();
}

function messageFrom(error) {
    const data = error?.response?.data;
    if (data?.errors) {
        const first = Object.values(data.errors).flat()[0];
        if (first) return String(first);
    }
    return getApiError(error);
}

function Field({ label, value, onChangeText, placeholder, secureTextEntry, keyboardType, multiline, editable = true, helperText, autoCapitalize = 'none' }) {
    return (
        <View style={styles.fieldWrap}>
            {!!label && <Text style={styles.fieldLabel}>{label}</Text>}
            <TextInput
                value={value}
                onChangeText={onChangeText}
                placeholder={placeholder}
                placeholderTextColor="#9AA5B8"
                secureTextEntry={secureTextEntry}
                keyboardType={keyboardType || 'default'}
                multiline={multiline}
                editable={editable}
                autoCapitalize={autoCapitalize}
                autoCorrect={false}
                style={[styles.input, multiline && styles.multilineInput, !editable && styles.inputDisabled]}
            />
            {!!helperText && <Text style={styles.helperText}>{helperText}</Text>}
        </View>
    );
}

function DropdownField({
                           label,
                           value,
                           placeholder,
                           options = [],
                           onSelect,
                           disabled = false,
                           helperText,
                           emptyMessage = 'No options available.',
                       }) {
    const [visible, setVisible] = useState(false);

    return (
        <View style={styles.fieldWrap}>
            {!!label && <Text style={styles.fieldLabel}>{label}</Text>}
            <Pressable
                accessibilityRole="button"
                accessibilityState={{ disabled }}
                disabled={disabled}
                onPress={() => setVisible(true)}
                style={[styles.dropdownInput, disabled && styles.inputDisabled]}
            >
                <Text style={[styles.dropdownValue, !value && styles.dropdownPlaceholder]} numberOfLines={2}>
                    {value || placeholder}
                </Text>
                <Text style={styles.dropdownChevron}>⌄</Text>
            </Pressable>
            {!!helperText && <Text style={styles.helperText}>{helperText}</Text>}

            <Modal
                visible={visible}
                transparent
                animationType="fade"
                onRequestClose={() => setVisible(false)}
            >
                <View style={styles.dropdownModalOverlay}>
                    <View style={styles.dropdownModalCard}>
                        <View style={styles.dropdownModalHeader}>
                            <Text style={styles.dropdownModalTitle}>Select {label?.replace(' *', '') || 'an option'}</Text>
                            <Pressable onPress={() => setVisible(false)} hitSlop={10}>
                                <Text style={styles.dropdownModalCloseIcon}>×</Text>
                            </Pressable>
                        </View>
                        {options.length > 0 ? (
                            <ScrollView style={styles.dropdownOptionsScroll} keyboardShouldPersistTaps="handled">
                                {options.map((option) => (
                                    <Pressable
                                        key={option}
                                        onPress={() => {
                                            onSelect(option);
                                            setVisible(false);
                                        }}
                                        style={({ pressed }) => [
                                            styles.dropdownOption,
                                            value === option && styles.dropdownOptionSelected,
                                            pressed && styles.pressed,
                                        ]}
                                    >
                                        <Text style={[
                                            styles.dropdownOptionText,
                                            value === option && styles.dropdownOptionTextSelected,
                                        ]}>
                                            {option}
                                        </Text>
                                        {value === option && <Text style={styles.dropdownOptionCheck}>✓</Text>}
                                    </Pressable>
                                ))}
                            </ScrollView>
                        ) : (
                            <Text style={styles.dropdownEmptyText}>{emptyMessage}</Text>
                        )}
                        <PrimaryButton title="Close" onPress={() => setVisible(false)} variant="secondary" />
                    </View>
                </View>
            </Modal>
        </View>
    );
}

function PrimaryButton({ title, onPress, disabled, variant = 'primary', small = false, loading = false }) {
    const buttonStyle = variant === 'secondary'
        ? styles.buttonSecondary
        : variant === 'danger'
            ? styles.buttonDanger
            : variant === 'subtle'
                ? styles.buttonSubtle
                : styles.buttonPrimary;
    const textStyle = variant === 'secondary'
        ? styles.buttonSecondaryText
        : variant === 'danger'
            ? styles.buttonDangerText
            : variant === 'subtle'
                ? styles.buttonSubtleText
                : styles.buttonPrimaryText;
    return (
        <Pressable
            accessibilityRole="button"
            onPress={onPress}
            disabled={disabled || loading}
            style={({ pressed }) => [buttonStyle, small && styles.buttonSmall, (disabled || loading) && styles.buttonDisabled, pressed && !(disabled || loading) && styles.pressed]}
        >
            {loading ? <ActivityIndicator color={variant === 'primary' ? '#FFFFFF' : COLORS.blue} size="small" /> : <Text style={textStyle}>{title}</Text>}
        </Pressable>
    );
}

function Notice({ text, type = 'error' }) {
    if (!text) return null;
    const noticeStyle = type === 'success' ? styles.noticeSuccess : type === 'warning' ? styles.noticeWarning : styles.noticeError;
    return <View style={[styles.notice, noticeStyle]}><Text style={styles.noticeText}>{text}</Text></View>;
}

function SectionTitle({ title, subtitle, action }) {
    return (
        <View style={styles.sectionTitleRow}>
            <View style={styles.sectionTitleCopy}>
                <Text style={styles.sectionTitle}>{title}</Text>
                {!!subtitle && <Text style={styles.sectionSubtitle}>{subtitle}</Text>}
            </View>
            {action}
        </View>
    );
}

function StatCard({ label, value, accent = COLORS.blue, symbol = '•' }) {
    return (
        <View style={styles.statCard}>
            <View style={[styles.statSymbol, { backgroundColor: `${accent}18` }]}><Text style={[styles.statSymbolText, { color: accent }]}>{symbol}</Text></View>
            <Text style={styles.statValue}>{value}</Text>
            <Text style={styles.statLabel}>{label}</Text>
        </View>
    );
}

function QuizCard({ quiz, onStart, starting }) {
    const available = Boolean(quiz.is_available ?? (quiz.status === 'published'));
    return (
        <View style={styles.card}>
            <View style={styles.cardTopRow}>
                <View style={styles.flexOne}>
                    <Text style={styles.cardTitle}>{quiz.title || 'Untitled quiz'}</Text>
                    <Text style={styles.cardSubtitle}>{quiz.subject || 'General subject'}</Text>
                </View>
                <View style={[styles.statusPill, available ? styles.statusAvailable : styles.statusUpcoming]}>
                    <Text style={[styles.statusText, available ? styles.statusAvailableText : styles.statusUpcomingText]}>{available ? (quiz.status === 'draft' ? 'Draft' : 'Available') : 'Upcoming'}</Text>
                </View>
            </View>
            {!!quiz.description && <Text style={styles.cardDescription}>{quiz.description}</Text>}
            <View style={styles.metaGrid}>
                <View style={styles.metaCell}><Text style={styles.metaLabel}>Duration</Text><Text style={styles.metaValue}>{quiz.duration_minutes ?? '—'} minutes</Text></View>
                <View style={styles.metaCell}><Text style={styles.metaLabel}>Questions</Text><Text style={styles.metaValue}>{quiz.questions_count ?? quiz.questions?.length ?? '—'}</Text></View>
                <View style={styles.metaCell}><Text style={styles.metaLabel}>Course</Text><Text style={styles.metaValue}>{quiz.course || '—'}</Text></View>
                <View style={styles.metaCell}><Text style={styles.metaLabel}>Batch</Text><Text style={styles.metaValue}>{quiz.batch || '—'}</Text></View>
            </View>
            {!!quiz.available_at && <Text style={styles.dateCaption}>Scheduled: {formatDate(quiz.available_at)}</Text>}
            {!!onStart && (
                <PrimaryButton title={starting ? 'Opening quiz…' : available ? 'Start / Resume Quiz' : 'Not available yet'} onPress={() => onStart(quiz)} disabled={!available || starting} loading={starting} />
            )}
        </View>
    );
}

function StudentResultCard({ item }) {
    const percent = Number(item.percentage || 0);
    return (
        <View style={styles.card}>
            <View style={styles.cardTopRow}>
                <View style={styles.flexOne}>
                    <Text style={styles.cardTitle}>{item.quiz_title || item.quiz?.title || 'Quiz result'}</Text>
                    <Text style={styles.cardSubtitle}>{item.subject || item.quiz?.subject || 'General subject'}</Text>
                </View>
                <View style={[styles.scorePill, percent >= 50 ? styles.scoreGood : styles.scoreLow]}><Text style={[styles.scorePillText, percent >= 50 ? styles.scoreGoodText : styles.scoreLowText]}>{percent.toFixed(2)}%</Text></View>
            </View>
            <View style={styles.resultMarksRow}>
                <View><Text style={styles.metaLabel}>Marks</Text><Text style={styles.resultMarks}>{item.marks_obtained ?? 0} / {item.total_marks ?? 0}</Text></View>
                <View><Text style={styles.metaLabel}>Attempt</Text><Text style={styles.metaValue}>#{item.attempt_id ?? item.id ?? '—'}</Text></View>
            </View>
            <Text style={styles.dateCaption}>Submitted: {formatDate(item.submitted_at)}</Text>
        </View>
    );
}

function TeacherSubmissionCard({ item }) {
    const student = item.student || {};
    const quiz = item.quiz || {};
    const answers = item.answers && typeof item.answers === 'object' ? Object.entries(item.answers) : [];
    return (
        <View style={styles.card}>
            <View style={styles.cardTopRow}>
                <View style={styles.flexOne}>
                    <Text style={styles.cardTitle}>{student.name || 'Student'} </Text>
                    <Text style={styles.cardSubtitle}>{quiz.title || 'Quiz submission'}</Text>
                </View>
                {item.percentage !== undefined && item.percentage !== null && (
                    <View style={[styles.scorePill, Number(item.percentage) >= 50 ? styles.scoreGood : styles.scoreLow]}>
                        <Text style={[styles.scorePillText, Number(item.percentage) >= 50 ? styles.scoreGoodText : styles.scoreLowText]}>{Number(item.percentage).toFixed(2)}%</Text>
                    </View>
                )}
            </View>
            <Text style={styles.metaValue}>{student.reg_no || student.email || 'Student account'}</Text>
            <View style={styles.resultMarksRow}>
                <View><Text style={styles.metaLabel}>Marks</Text><Text style={styles.resultMarks}>{item.marks_obtained ?? 'Not provided'}{item.total_marks !== undefined && item.total_marks !== null ? ` / ${item.total_marks}` : ''}</Text></View>
                <View><Text style={styles.metaLabel}>Answers saved</Text><Text style={styles.metaValue}>{answers.length}</Text></View>
            </View>
            <Text style={styles.dateCaption}>Submitted: {formatDate(item.submitted_at)}</Text>
        </View>
    );
}

export default function App() {
    const [booting, setBooting] = useState(true);
    const [baseInput, setBaseInput] = useState(DEFAULT_API_BASE);
    const [apiBase, setApiBase] = useState('');
    const [loginRole, setLoginRole] = useState('student');
    const [role, setRole] = useState(null);
    const [token, setToken] = useState(null);
    const [profile, setProfile] = useState(null);
    const [academic, setAcademic] = useState(null);
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loginBusy, setLoginBusy] = useState(false);
    const [loadingData, setLoadingData] = useState(false);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');
    const [screen, setScreen] = useState('home');
    const [quizzes, setQuizzes] = useState([]);
    const [results, setResults] = useState([]);
    const [adminStats, setAdminStats] = useState({});
    const [adminResource, setAdminResource] = useState(null);
    const [adminMeta, setAdminMeta] = useState(null);
    const [adminRecords, setAdminRecords] = useState([]);
    const [adminModuleLoading, setAdminModuleLoading] = useState(false);
    const [adminSearch, setAdminSearch] = useState('');
    const [adminForm, setAdminForm] = useState({});
    const [adminEditingId, setAdminEditingId] = useState(null);
    const [adminEditorVisible, setAdminEditorVisible] = useState(false);
    const [adminSaving, setAdminSaving] = useState(false);
    const [unreadCount, setUnreadCount] = useState(0);
    const [academicOptions, setAcademicOptions] = useState([]);
    const [academicOptionsLoading, setAcademicOptionsLoading] = useState(false);
    const [academicOptionsError, setAcademicOptionsError] = useState('');
    const [subjects, setSubjects] = useState([]);
    const [subjectsLoading, setSubjectsLoading] = useState(false);
    const [subjectsError, setSubjectsError] = useState('');
    const [materials, setMaterials] = useState([]);
    const [materialsLoading, setMaterialsLoading] = useState(false);
    const [materialsError, setMaterialsError] = useState('');
    const [materialTitle, setMaterialTitle] = useState('');
    const [materialDescription, setMaterialDescription] = useState('');
    const [materialLink, setMaterialLink] = useState('');
    const [selectedMaterialFile, setSelectedMaterialFile] = useState(null);
    const [savingMaterial, setSavingMaterial] = useState(false);
    const [startingQuizId, setStartingQuizId] = useState(null);

    const [activeQuiz, setActiveQuiz] = useState(null);
    const [attemptSession, setAttemptSession] = useState(null);
    const [selectedAnswers, setSelectedAnswers] = useState({});
    const [deadlineMs, setDeadlineMs] = useState(null);
    const [serverOffsetMs, setServerOffsetMs] = useState(0);
    const [remainingSeconds, setRemainingSeconds] = useState(null);
    const [submittingAttempt, setSubmittingAttempt] = useState(false);
    const [attemptSubmitted, setAttemptSubmitted] = useState(false);
    const [attemptResult, setAttemptResult] = useState(null);
    const [attemptError, setAttemptError] = useState('');
    const [saveWarning, setSaveWarning] = useState('');
    const answerSaveChain = useRef(Promise.resolve());
    const submitQuizRef = useRef(null);
    const timeoutTriggeredRef = useRef(false);
    const submittingRef = useRef(false);

    const [quizForm, setQuizForm] = useState(() => ({
        title: '', course: '', degree: '', batch: '', subject: '',
        description: '', duration_minutes: '15', available_at: makeFutureLocalDateTime(),
        questions: [EMPTY_QUESTION()],
    }));
    const [creatingQuiz, setCreatingQuiz] = useState(false);

    const api = useMemo(() => {
        if (!apiBase) return null;
        try { return createApi(apiBase, token); } catch { return null; }
    }, [apiBase, token]);

    const loadMaterials = useCallback(async (showSpinner = true) => {
        if (!api || !role || !token || (role !== 'teacher' && role !== 'student')) return;
        if (showSpinner) setMaterialsLoading(true);
        setMaterialsError('');
        try {
            const endpoint = role === 'teacher' ? '/teacher/materials' : '/student/materials';
            const response = await api.get(endpoint);
            setMaterials(Array.isArray(response.data?.materials) ? response.data.materials : []);
        } catch (loadError) {
            setMaterialsError(messageFrom(loadError));
            setMaterials([]);
        } finally {
            if (showSpinner) setMaterialsLoading(false);
        }
    }, [api, role, token]);

    useEffect(() => {
        if (screen !== 'materials') return;
        void loadMaterials(true);
    }, [screen, loadMaterials]);

    const chooseMaterialFile = async () => {
        try {
            const result = await DocumentPicker.getDocumentAsync({
                type: [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                ],
                copyToCacheDirectory: true,
                multiple: false,
            });
            if (!result.canceled && result.assets?.length) {
                setSelectedMaterialFile(result.assets[0]);
                setMaterialsError('');
            }
        } catch (pickerError) {
            setMaterialsError(pickerError?.message || 'Could not open the document picker.');
        }
    };

    const shareMaterial = async () => {
        if (!api || role !== 'teacher') return;
        setMaterialsError('');
        setError('');

        if (!materialTitle.trim()) {
            setMaterialsError('Enter a title for this lecture material.');
            return;
        }
        const hasExternalLink = materialLink.trim() !== '';
        if (!selectedMaterialFile && !hasExternalLink) {
            setMaterialsError('Choose a document or add an online link. You can also provide both.');
            return;
        }
        if (hasExternalLink && !/^https?:\/\//i.test(materialLink.trim())) {
            setMaterialsError('Enter a valid link starting with https:// or http://.');
            return;
        }

        setSavingMaterial(true);

        try {
            if (!selectedMaterialFile) {
                // Link-only materials continue to use the normal authenticated API client.
                const response = await api.post('/teacher/materials', {
                    title: materialTitle.trim(),
                    description: materialDescription.trim(),
                    external_url: materialLink.trim(),
                }, {
                    headers: { Accept: 'application/json' },
                });

                setNotice(response.data?.message || 'Lecture material link shared with students.');
            } else {
                const originalName = selectedMaterialFile.name || 'lecture-material.pdf';
                const safeName = originalName.replace(/[^a-zA-Z0-9._-]/g, '_');
                const extension = safeName.split('.').pop()?.toLowerCase();
                const fallbackTypes = {
                    pdf: 'application/pdf',
                    doc: 'application/msword',
                    docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ppt: 'application/vnd.ms-powerpoint',
                    pptx: 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                };
                const mimeType = selectedMaterialFile.mimeType || fallbackTypes[extension] || 'application/octet-stream';

                if (typeof selectedMaterialFile.size === 'number' && selectedMaterialFile.size > MAX_MATERIAL_BYTES) {
                    throw new Error('The file is larger than 25 MB. Please choose a smaller file.');
                }

                // Send the picker's file URI directly. React Native reads it natively,
                // so no expo-file-system copy is needed. This avoids the Expo Go
                // "Location ... isn't readable" error.
                const formData = new FormData();
                formData.append('title', materialTitle.trim());
                formData.append('description', materialDescription.trim());
                if (hasExternalLink) formData.append('external_url', materialLink.trim());
                formData.append('file', {
                    uri: selectedMaterialFile.uri,
                    name: safeName,
                    type: mimeType,
                });

                const uploadUrl = `${apiBase.replace(/\/+$/, '')}/teacher/materials`;
                // Use XMLHttpRequest instead of fetch. Newer Expo SDKs replace the global
                // fetch with a version that rejects React Native {uri, name, type} file
                // parts ("Unsupported FormDataPart implementation"). XHR still supports them.
                const uploadResult = await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', uploadUrl);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('Authorization', `Bearer ${token}`);
                    // Do NOT set Content-Type; XHR adds the multipart boundary itself.
                    xhr.timeout = 120000;
                    xhr.onload = () => resolve({ status: xhr.status, body: xhr.responseText });
                    xhr.onerror = () => reject(new Error('Network request failed. Check that the API address is reachable from this device.'));
                    xhr.ontimeout = () => reject(new Error('The upload timed out. Please try again.'));
                    xhr.send(formData);
                });

                let responseBody = {};
                try {
                    responseBody = JSON.parse(uploadResult.body || '{}');
                } catch {
                    responseBody = { message: uploadResult.body || '' };
                }

                if (uploadResult.status < 200 || uploadResult.status >= 300) {
                    const firstValidationError = responseBody.errors
                        ? Object.values(responseBody.errors).flat()[0]
                        : null;
                    throw new Error(
                        firstValidationError
                            ? String(firstValidationError)
                            : (responseBody.message || `Upload failed with HTTP ${uploadResult.status}.`)
                    );
                }

                setNotice(responseBody.message || 'Lecture material uploaded and shared with students.');
            }

            setMaterialTitle('');
            setMaterialDescription('');
            setMaterialLink('');
            setSelectedMaterialFile(null);
            await loadMaterials(false);
        } catch (saveError) {
            setMaterialsError(saveError?.message || messageFrom(saveError));
        } finally {
            setSavingMaterial(false);
        }
    };

    const openMaterial = async (item, resourceType = 'file') => {
        const target = resourceType === 'link' ? item.external_url : item.download_url;
        if (!target) {
            setMaterialsError(resourceType === 'link'
                ? 'This material does not have an external link.'
                : 'This material does not have a downloadable document.');
            return;
        }
        try {
            await Linking.openURL(target);
        } catch {
            setMaterialsError('Could not open this material. Please check the link or try again.');
        }
    };

    const deleteMaterial = (item) => {
        Alert.alert(
            'Delete lecture material?',
            'Students will no longer be able to access this material.',
            [
                { text: 'Cancel', style: 'cancel' },
                { text: 'Delete', style: 'destructive', onPress: async () => {
                        try {
                            await api.delete(`/teacher/materials/${item.id}`);
                            setMaterials((current) => current.filter((entry) => entry.id !== item.id));
                            setNotice('Lecture material deleted successfully.');
                        } catch (deleteError) {
                            setMaterialsError(messageFrom(deleteError));
                        }
                    } },
            ],
        );
    };

    const courseOptions = useMemo(() => (
        [...new Set(academicOptions.map((option) => option.course).filter(Boolean))]
            .sort((a, b) => a.localeCompare(b))
    ), [academicOptions]);

    const degreeOptions = useMemo(() => (
        [...new Set(academicOptions
            .filter((option) => option.course === quizForm.course)
            .map((option) => option.degree)
            .filter(Boolean))]
            .sort((a, b) => a.localeCompare(b))
    ), [academicOptions, quizForm.course]);

    const batchOptions = useMemo(() => (
        [...new Set(academicOptions
            .filter((option) => option.course === quizForm.course && option.degree === quizForm.degree)
            .map((option) => option.batch)
            .filter(Boolean))]
            .sort((a, b) => a.localeCompare(b))
    ), [academicOptions, quizForm.course, quizForm.degree]);

    const subjectOptions = useMemo(() => (
        subjects
            .filter((subject) =>
                String(subject.course_name || '').trim() === quizForm.course.trim() &&
                String(subject.degree_name || '').trim() === quizForm.degree.trim()
            )
            .map((subject) => String(subject.subject_name || '').trim())
            .filter(Boolean)
            .sort((a, b) => a.localeCompare(b))
    ), [subjects, quizForm.course, quizForm.degree]);

    useEffect(() => {
        let alive = true;
        async function restoreSession() {
            try {
                const [savedBase, savedToken, savedRole, savedUser] = await Promise.all([
                    SecureStore.getItemAsync(KEYS.base),
                    SecureStore.getItemAsync(KEYS.token),
                    SecureStore.getItemAsync(KEYS.role),
                    SecureStore.getItemAsync(KEYS.user),
                ]);
                if (!alive) return;
                // Keep the API address configured internally; regular users do not need to edit it.
                const configuredBase = savedBase || DEFAULT_API_BASE;
                setApiBase(configuredBase);
                setBaseInput(configuredBase);
                if (savedToken && savedRole && savedUser) {
                    setToken(savedToken);
                    setRole(savedRole);
                    setLoginRole(savedRole);
                    setProfile(JSON.parse(savedUser));
                }
            } catch {
                // A corrupted secure-store entry should not prevent the app from opening.
            } finally {
                if (alive) setBooting(false);
            }
        }
        void restoreSession();
        return () => { alive = false; };
    }, []);

    const refreshData = useCallback(async (showSpinner = true) => {
        if (!api || !role || !token) return;
        if (showSpinner) setLoadingData(true);
        setError('');
        try {
            if (role === 'student') {
                const [dashboardResponse, quizzesResponse, resultsResponse] = await Promise.all([
                    api.get('/student/dashboard'),
                    api.get('/student/quizzes'),
                    api.get('/student/results'),
                ]);
                const dashboard = dashboardResponse.data || {};
                setProfile(dashboard.student || profile);
                setAcademic(dashboard.academic || null);
                setQuizzes(Array.isArray(quizzesResponse.data?.quizzes) ? quizzesResponse.data.quizzes : []);
                setResults(Array.isArray(resultsResponse.data?.results) ? resultsResponse.data.results : []);

                // Load the unread badge count without blocking the main dashboard.
                try {
                    const notificationCountResponse = await api.get('/student/notifications/unread-count');
                    setUnreadCount(Number(notificationCountResponse.data?.unread_count || 0));
                } catch {
                    // Notification count is optional; keep the rest of the student app usable.
                }

                if (dashboard.student) {
                    await SecureStore.setItemAsync(KEYS.user, JSON.stringify(dashboard.student));
                }
            } else if (role === 'teacher') {
                const [dashboardResponse, quizzesResponse, resultsResponse] = await Promise.all([
                    api.get('/teacher/dashboard'),
                    api.get('/teacher/quizzes'),
                    api.get('/teacher/results'),
                ]);
                const dashboard = dashboardResponse.data || {};
                setProfile(dashboard.teacher || profile);
                setQuizzes(Array.isArray(quizzesResponse.data?.quizzes) ? quizzesResponse.data.quizzes : []);
                setResults(Array.isArray(resultsResponse.data?.submissions) ? resultsResponse.data.submissions : []);
                if (dashboard.teacher) {
                    await SecureStore.setItemAsync(KEYS.user, JSON.stringify(dashboard.teacher));
                }
            } else if (role === 'admin') {
                const dashboardResponse = await api.get('/admin/dashboard');
                const dashboard = dashboardResponse.data || {};
                setProfile(dashboard.admin || profile);
                setAdminStats(dashboard.statistics || {});
                if (dashboard.admin) {
                    await SecureStore.setItemAsync(KEYS.user, JSON.stringify(dashboard.admin));
                }
            } else {
                throw new Error('Unsupported account role.');
            }
        } catch (fetchError) {
            setError(messageFrom(fetchError));
        } finally {
            if (showSpinner) setLoadingData(false);
        }
    }, [api, role, token]);

    const loadAdminModule = useCallback(async (resource, searchValue = '') => {
        if (!api || role !== 'admin' || !resource) return;
        setAdminModuleLoading(true);
        setError('');
        try {
            const [schemaResponse, recordsResponse] = await Promise.all([
                api.get(`/admin/${resource}/schema`),
                api.get(`/admin/${resource}`, { params: { search: searchValue, per_page: 50 } }),
            ]);
            setAdminMeta(schemaResponse.data || null);
            setAdminRecords(Array.isArray(recordsResponse.data?.records?.data)
                ? recordsResponse.data.records.data
                : []);
        } catch (loadError) {
            setError(messageFrom(loadError));
            setAdminMeta(null);
            setAdminRecords([]);
        } finally {
            setAdminModuleLoading(false);
        }
    }, [api, role]);

    useEffect(() => {
        if (booting || !token || !role || !apiBase) return;
        void refreshData(true);
    }, [booting, token, role, apiBase, refreshData]);

    // Load the same academic and subject options used by the web Teacher portal.
    useEffect(() => {
        if (!api || role !== 'teacher' || !token) {
            setAcademicOptions([]);
            setSubjects([]);
            setAcademicOptionsError('');
            setSubjectsError('');
            setAcademicOptionsLoading(false);
            setSubjectsLoading(false);
            return undefined;
        }

        let active = true;
        setAcademicOptionsLoading(true);
        setSubjectsLoading(true);
        setAcademicOptionsError('');
        setSubjectsError('');

        async function loadQuizOptions() {
            const [academicResult, subjectResult] = await Promise.allSettled([
                api.get('/teacher/academic-options'),
                api.get('/teacher/subjects'),
            ]);

            if (!active) return;

            if (academicResult.status === 'fulfilled') {
                const options = academicResult.value.data?.academic_options;
                setAcademicOptions(Array.isArray(options) ? options : []);
                setAcademicOptionsError('');
            } else {
                setAcademicOptions([]);
                setAcademicOptionsError(messageFrom(academicResult.reason));
            }

            if (subjectResult.status === 'fulfilled') {
                const loadedSubjects = subjectResult.value.data;
                setSubjects(Array.isArray(loadedSubjects) ? loadedSubjects : []);
                setSubjectsError('');
            } else {
                setSubjects([]);
                setSubjectsError(messageFrom(subjectResult.reason));
            }

            setAcademicOptionsLoading(false);
            setSubjectsLoading(false);
        }

        void loadQuizOptions();

        return () => {
            active = false;
        };
    }, [api, role, token]);

    // In-app notifications are loaded from Laravel when the student opens Alerts.

    const handleLogin = async () => {
        setError('');
        setNotice('');
        let normalizedBase;
        try {
            normalizedBase = normalizeApiBaseUrl(baseInput);
        } catch (baseError) {
            setError(baseError.message);
            return;
        }
        if (!email.trim() || !password) {
            setError('Enter your email address and password.');
            return;
        }
        setLoginBusy(true);
        try {
            const response = await createApi(normalizedBase).post(`/${loginRole}/login`, {
                email: email.trim(),
                password,
            });
            const body = response.data || {};
            const newToken = body.token;
            const newProfile = body[loginRole] || body.admin || body.student || body.teacher;
            if (!newToken || !newProfile) throw new Error('The server did not return a valid login session.');
            await Promise.all([
                SecureStore.setItemAsync(KEYS.base, normalizedBase),
                SecureStore.setItemAsync(KEYS.token, newToken),
                SecureStore.setItemAsync(KEYS.role, loginRole),
                SecureStore.setItemAsync(KEYS.user, JSON.stringify(newProfile)),
            ]);
            setApiBase(normalizedBase);
            setBaseInput(normalizedBase);
            setRole(loginRole);
            setToken(newToken);
            setProfile(newProfile);
            setPassword('');
            setScreen('home');
            setNotice('Signed in successfully.');
        } catch (loginError) {
            setError(messageFrom(loginError));
        } finally {
            setLoginBusy(false);
        }
    };

    const handleLogout = async () => {
        try {
            if (api && role) await api.post(`/${role}/logout`);
        } catch {
            // Remove local credentials even if the server is temporarily unavailable.
        }
        await Promise.all([KEYS.token, KEYS.role, KEYS.user].map((key) => SecureStore.deleteItemAsync(key).catch(() => {})));
        setToken(null);
        setRole(null);
        setProfile(null);
        setAcademic(null);
        setQuizzes([]);
        setResults([]);
        setAdminStats({});
        setAdminResource(null);
        setAdminMeta(null);
        setAdminRecords([]);
        setAdminForm({});
        setAdminEditingId(null);
        setAdminEditorVisible(false);
        setUnreadCount(0);
        setMaterials([]);
        setSelectedMaterialFile(null);
        setMaterialTitle('');
        setMaterialDescription('');
        setMaterialLink('');
        setActiveQuiz(null);
        setAttemptSession(null);
        setDeadlineMs(null);
        setAttemptResult(null);
        setScreen('home');
        setError('');
        setNotice('You have signed out.');
    };

    const openQuiz = async (quiz) => {
        if (!api || startingQuizId !== null) return;
        setError('');
        setNotice('');
        setAttemptError('');
        setSaveWarning('');
        setAttemptSubmitted(false);
        setAttemptResult(null);
        setStartingQuizId(quiz.id);
        try {
            // Starting first returns the server-owned deadline and any saved answers.
            const startResponse = await api.post(`/student/quizzes/${quiz.id}/start`);
            const session = startResponse.data?.attempt_session;
            if (!session?.id || !session?.deadline_at) throw new Error('The server did not return a timed quiz session.');
            const detailResponse = await api.get(`/student/quizzes/${quiz.id}`);
            const detail = detailResponse.data || {};
            const deadline = new Date(session.deadline_at).getTime();
            const serverNow = startResponse.data?.server_time ? new Date(startResponse.data.server_time).getTime() : Date.now();
            if (Number.isNaN(deadline)) throw new Error('The server returned an invalid quiz deadline.');
            setActiveQuiz({ ...(detail.quiz || quiz), questions: Array.isArray(detail.questions) ? detail.questions : [] });
            setAttemptSession(session);
            setSelectedAnswers(session.answers && typeof session.answers === 'object' ? session.answers : {});
            setDeadlineMs(deadline);
            setServerOffsetMs(serverNow - Date.now());
            setRemainingSeconds(Math.max(0, Math.ceil((deadline - serverNow) / 1000)));
            setAttemptError('');
            setSaveWarning('');
            answerSaveChain.current = Promise.resolve();
            timeoutTriggeredRef.current = false;
            submittingRef.current = false;
            setSubmittingAttempt(false);
            setScreen('takeQuiz');
        } catch (openError) {
            setError(messageFrom(openError));
        } finally {
            setStartingQuizId(null);
        }
    };

    const selectAnswer = (questionId, option) => {
        if (!api || !activeQuiz || !attemptSession || submittingAttempt || remainingSeconds === 0) return;
        setSelectedAnswers((current) => ({ ...current, [String(questionId)]: option }));
        setSaveWarning('');
        // Serialize saves so rapid answer changes cannot arrive out of order.
        answerSaveChain.current = answerSaveChain.current
            .then(() => api.put(`/student/quizzes/${activeQuiz.id}/attempt/answer`, {
                attempt_session_id: attemptSession.id,
                question_id: questionId,
                selected_option: option,
            }))
            .then(() => undefined)
            .catch((saveError) => {
                setSaveWarning(messageFrom(saveError));
            });
    };

    const submitQuiz = useCallback(async (automatic = false) => {
        if (!api || !activeQuiz || !attemptSession?.id || attemptSubmitted || submittingRef.current) return;
        const questions = Array.isArray(activeQuiz.questions) ? activeQuiz.questions : [];
        const unanswered = questions.filter((question) => !selectedAnswers[String(question.id)]);
        if (!automatic && unanswered.length > 0) {
            setAttemptError(`Please answer all questions before submitting. ${unanswered.length} question(s) remain unanswered.`);
            return;
        }
        submittingRef.current = true;
        setSubmittingAttempt(true);
        setAttemptError('');
        try {
            // Finish the queued server-answer writes before a manual submission.
            await answerSaveChain.current;
            const response = await api.post(`/student/quizzes/${activeQuiz.id}/submit`, automatic
                ? { attempt_session_id: attemptSession.id, timed_out: true }
                : { attempt_session_id: attemptSession.id, answers: selectedAnswers });
            const body = response.data || {};
            setAttemptResult({
                ...(body.result || {}),
                attempt_id: body.attempt?.id,
                submitted_at: body.attempt?.submitted_at,
                message: body.message || (automatic ? 'Time expired. Saved answers were submitted.' : 'Quiz submitted successfully.'),
                automatic,
            });
            setAttemptSubmitted(true);
            setScreen('quizResult');
            setNotice('Quiz result saved successfully.');
            try {
                const resultsResponse = await api.get('/student/results');
                setResults(Array.isArray(resultsResponse.data?.results) ? resultsResponse.data.results : []);
            } catch {
                // The submitted result screen can still be shown from the submit response.
            }
        } catch (submitError) {
            setAttemptError(automatic
                ? `Automatic submission could not reach the server. Check your connection and retry. ${messageFrom(submitError)}`
                : messageFrom(submitError));
        } finally {
            submittingRef.current = false;
            setSubmittingAttempt(false);
        }
    }, [api, activeQuiz, attemptSession, attemptSubmitted, selectedAnswers]);

    submitQuizRef.current = submitQuiz;

    useEffect(() => {
        if (screen !== 'takeQuiz' || deadlineMs === null) return undefined;
        timeoutTriggeredRef.current = false;
        const tick = () => {
            const seconds = Math.max(0, Math.ceil((deadlineMs - (Date.now() + serverOffsetMs)) / 1000));
            setRemainingSeconds(seconds);
            if (seconds <= 0 && !timeoutTriggeredRef.current) {
                timeoutTriggeredRef.current = true;
                void submitQuizRef.current?.(true);
            }
        };
        tick();
        const timerId = setInterval(tick, 500);
        return () => clearInterval(timerId);
    }, [screen, deadlineMs, serverOffsetMs]);

    const exitQuiz = () => {
        Alert.alert(
            'Leave quiz?',
            'Your server-saved answers and deadline remain active. You can resume this quiz from My Quizzes.',
            [
                { text: 'Stay here', style: 'cancel' },
                { text: 'Leave', style: 'destructive', onPress: () => {
                        setActiveQuiz(null);
                        setAttemptSession(null);
                        setDeadlineMs(null);
                        setRemainingSeconds(null);
                        setScreen('quizzes');
                    } },
            ],
        );
    };

    const updateQuizForm = (key, value) => setQuizForm((form) => {
        const next = { ...form, [key]: value };
        if (key === 'course') {
            next.degree = '';
            next.batch = '';
            next.subject = '';
        } else if (key === 'degree') {
            next.batch = '';
            next.subject = '';
        }
        return next;
    });

    const updateQuestion = (index, key, value) => setQuizForm((form) => ({
        ...form,
        questions: form.questions.map((question, currentIndex) => currentIndex === index ? { ...question, [key]: value } : question),
    }));

    const createQuiz = async () => {
        if (!api) return;
        setError('');
        setNotice('');

        const normalizedQuestions = quizForm.questions.map((question) => ({
            question_text: question.question_text.trim(),
            option_a: question.option_a.trim(),
            option_b: question.option_b.trim(),
            option_c: question.option_c.trim(),
            option_d: question.option_d.trim(),
            correct_option: question.correct_option,
            marks: Number(question.marks || 1),
        }));

        if (!quizForm.title.trim() || !quizForm.course || !quizForm.degree || !quizForm.batch || !quizForm.subject) {
            setError('Complete the quiz title, course, degree, batch, and subject.');
            return;
        }

        const validAcademicGroup = academicOptions.some((option) =>
            option.course === quizForm.course &&
            option.degree === quizForm.degree &&
            option.batch === quizForm.batch
        );
        if (!validAcademicGroup) {
            setError('Please select a Course, Degree and Batch registered in Academic Setup.');
            return;
        }

        const validSubject = subjects.some((subject) =>
            String(subject.subject_name || '').trim() === quizForm.subject.trim() &&
            String(subject.course_name || '').trim() === quizForm.course.trim() &&
            String(subject.degree_name || '').trim() === quizForm.degree.trim()
        );
        if (!validSubject) {
            setError('Please select a subject assigned to the selected Course and Degree in the Admin Dashboard.');
            return;
        }

        const duration = Number(quizForm.duration_minutes);
        if (!Number.isInteger(duration) || duration < 1 || duration > 300) {
            setError('Duration must be a whole number between 1 and 300 minutes.');
            return;
        }

        const availableAt = new Date(quizForm.available_at);
        if (!quizForm.available_at || Number.isNaN(availableAt.getTime())) {
            setError('Please enter a valid Available Date & Time in YYYY-MM-DDTHH:mm format.');
            return;
        }
        if (availableAt.getTime() <= Date.now()) {
            setError('Available Date & Time must be later than the current time.');
            return;
        }

        if (normalizedQuestions.some((question) =>
            !question.question_text ||
            !question.option_a ||
            !question.option_b ||
            !question.option_c ||
            !question.option_d ||
            !Number.isInteger(question.marks) ||
            question.marks < 1 ||
            question.marks > 100
        )) {
            setError('Each question needs text, all four options, and marks between 1 and 100.');
            return;
        }

        setCreatingQuiz(true);
        try {
            const response = await api.post('/teacher/quizzes', {
                title: quizForm.title.trim(),
                // The web Teacher portal still sends this required legacy backend field.
                grade: 1,
                course: quizForm.course,
                degree: quizForm.degree,
                batch: quizForm.batch,
                subject: quizForm.subject,
                description: quizForm.description.trim() || null,
                duration_minutes: duration,
                // Match the web portal: convert the selected local datetime to UTC.
                available_at: availableAt.toISOString(),
                questions: normalizedQuestions,
            });

            if (response.data?.quiz) setQuizzes((current) => [response.data.quiz, ...current]);
            setQuizForm({
                title: '', course: '', degree: '', batch: '', subject: '',
                description: '', duration_minutes: '15', available_at: makeFutureLocalDateTime(),
                questions: [EMPTY_QUESTION()],
            });
            setNotice(response.data?.message || 'Quiz scheduled successfully.');
            setScreen('quizzes');
        } catch (createError) {
            setError(messageFrom(createError));
        } finally {
            setCreatingQuiz(false);
        }
    };

    const average = results.length
        ? results.reduce((sum, result) => sum + Number(result.percentage ?? 0), 0) / results.length
        : 0;
    const best = results.reduce((max, result) => Math.max(max, Number(result.percentage ?? 0)), 0);
    const displayName = profile?.name || (role === 'admin' ? 'Admin' : role === 'teacher' ? 'Teacher' : 'Student');

    if (booting) {
        return <View style={styles.loadingScreen}><StatusBar barStyle="dark-content" /><ActivityIndicator size="large" color={COLORS.blue} /><Text style={styles.loadingText}>Loading StudentManage…</Text></View>;
    }

    if (!token || !role) {
        return (
            <KeyboardAvoidingView style={styles.loginScreen} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
                <StatusBar barStyle="dark-content" />
                <ScrollView contentContainerStyle={styles.loginScroll} keyboardShouldPersistTaps="handled">
                    <View style={styles.brandRow}>
                        <View style={styles.brandMark}><Text style={styles.brandMarkText}>S</Text></View>
                        <View><Text style={styles.brandName}>StudentManage</Text><Text style={styles.brandTagline}>Your academic management platform</Text></View>
                    </View>
                    <View style={styles.loginHero}>
                        <Text style={styles.eyebrow}>CAMPUS MANAGEMENT</Text>
                        <Text style={styles.loginTitle}>Manage. Organize. Grow.</Text>
                        <Text style={styles.loginDescription}>Manage student records, teacher details, admissions, and academic activities from one place.</Text>
                    </View>
                    <View style={styles.loginCard}>
                        <Text style={styles.loginCardTitle}>Sign in</Text>
                        <Text style={styles.loginCardSubtitle}>Choose your account type to continue.</Text>
                        <View style={styles.roleToggle}>
                            {['student', 'teacher', 'admin'].map((item) => (
                                <Pressable key={item} onPress={() => setLoginRole(item)} style={[styles.roleOption, loginRole === item && styles.roleOptionActive]}>
                                    <Text style={[styles.roleOptionText, loginRole === item && styles.roleOptionTextActive]}>
                                        {item === 'student' ? '🎓 Student' : item === 'teacher' ? '🧑‍🏫 Teacher' : '🛡️ Admin'}
                                    </Text>
                                </Pressable>
                            ))}
                        </View>
                        <Field label="Email address" value={email} onChangeText={setEmail} placeholder="you@example.com" keyboardType="email-address" autoCapitalize="none" />
                        <Field label="Password" value={password} onChangeText={setPassword} placeholder="Enter your password" secureTextEntry autoCapitalize="none" />
                        <Notice text={error} />
                        <Notice text={notice} type="success" />
                        <PrimaryButton title="Sign in to StudentManage" onPress={handleLogin} loading={loginBusy} disabled={loginBusy} />
                    </View>
                </ScrollView>
            </KeyboardAvoidingView>
        );
    }

    const goTo = (next) => {
        setError('');
        setNotice('');
        setScreen(next);
    };

    const openAdminModule = async (resource) => {
        setAdminResource(resource);
        setAdminSearch('');
        setAdminMeta(null);
        setAdminRecords([]);
        setScreen('adminModule');
        await loadAdminModule(resource, '');
    };

    const openAdminEditor = (record = null) => {
        if (!adminMeta) return;
        const initialForm = {};
        for (const field of adminMeta.fields || []) {
            if (field.sensitive) {
                initialForm[field.name] = '';
                continue;
            }
            const rawValue = record ? record[field.name] : field.default;
            initialForm[field.name] = rawValue === null || rawValue === undefined ? '' : String(rawValue);
        }
        setAdminForm(initialForm);
        setAdminEditingId(record ? record[adminMeta.primary_key] : null);
        setAdminEditorVisible(true);
        setError('');
    };

    const updateAdminForm = (name, value) => {
        setAdminForm((current) => ({ ...current, [name]: value }));
    };

    const saveAdminRecord = async () => {
        if (!api || !adminResource || !adminMeta || adminSaving) return;
        setAdminSaving(true);
        setError('');
        try {
            const payload = {};
            for (const field of adminMeta.fields || []) {
                payload[field.name] = adminForm[field.name] ?? '';
            }
            if (adminEditingId !== null && adminEditingId !== undefined) {
                await api.put(`/admin/${adminResource}/${adminEditingId}`, payload);
                setNotice(`${adminMeta.title} record updated successfully.`);
            } else {
                await api.post(`/admin/${adminResource}`, payload);
                setNotice(`${adminMeta.title} record created successfully.`);
            }
            setAdminEditorVisible(false);
            await loadAdminModule(adminResource, adminSearch);
            await refreshData(false);
        } catch (saveError) {
            setError(messageFrom(saveError));
        } finally {
            setAdminSaving(false);
        }
    };

    const deleteAdminRecord = (record) => {
        const recordId = record?.[adminMeta?.primary_key || 'id'];
        const recordName = record?.name || record?.student_name || record?.teacher_name || record?.course_name || record?.degree_name || record?.batch_name || record?.subject_name || record?.exam_name || 'this record';
        Alert.alert(
            'Delete record?',
            `Are you sure you want to delete ${recordName}? This action cannot be undone.`,
            [
                { text: 'Cancel', style: 'cancel' },
                {
                    text: 'Delete',
                    style: 'destructive',
                    onPress: async () => {
                        try {
                            await api.delete(`/admin/${adminResource}/${recordId}`);
                            setNotice('Record deleted successfully.');
                            await loadAdminModule(adminResource, adminSearch);
                            await refreshData(false);
                        } catch (deleteError) {
                            setError(messageFrom(deleteError));
                        }
                    },
                },
            ],
        );
    };

    const adminRecordTitle = (record) => {
        const combinedName = [record?.first_name, record?.last_name].filter(Boolean).join(' ').trim();
        return record?.name || combinedName || record?.student_name || record?.teacher_name || record?.course_name || record?.degree_name || record?.batch_name || record?.subject_name || record?.exam_name || record?.title || record?.reg_no || record?.email || 'Untitled record';
    };

    const adminRecordSubtitle = (record) => {
        const fields = ['reg_no', 'email', 'phone', 'course_name', 'degree_name', 'batch_name', 'subject_code', 'course_code', 'degree_code', 'batch_code', 'admission_no', 'exam_date', 'created_at'];
        const value = fields.map((field) => record?.[field]).find((item) => item !== null && item !== undefined && String(item).trim() !== '');
        return value === undefined ? '' : String(value);
    };

    const renderAdminManage = () => (
        <>
            <SectionTitle title="Management" subtitle="Choose a section to view and manage its records." />
            <View style={styles.adminManageGrid}>
                {ADMIN_STAT_ITEMS.map((item) => (
                    <Pressable key={item.key} onPress={() => openAdminModule(item.key)} style={({ pressed }) => [styles.adminModuleCard, pressed && styles.pressed]}>
                        <View style={[styles.adminStatSymbol, { backgroundColor: `${item.accent}18` }]}>
                            <Text style={styles.adminStatSymbolText}>{item.symbol}</Text>
                        </View>
                        <Text style={styles.adminModuleTitle}>{item.label}</Text>
                        <Text style={styles.adminModuleCount}>{adminStats[item.key] === undefined ? '—' : adminStats[item.key]} records</Text>
                        <Text style={styles.adminModuleAction}>Manage records  →</Text>
                    </Pressable>
                ))}
            </View>
        </>
    );

    const renderAdminModule = () => {
        const currentModule = ADMIN_STAT_ITEMS.find((item) => item.key === adminResource);
        return (
            <>
                <Pressable onPress={() => goTo('adminManage')} style={styles.adminBackAction}>
                    <Text style={styles.adminBackText}>‹  Back to management</Text>
                </Pressable>
                <SectionTitle
                    title={adminMeta?.title || currentModule?.label || 'Manage records'}
                    subtitle="Create, view, update and delete records."
                    action={<Pressable onPress={() => loadAdminModule(adminResource, adminSearch)} style={styles.refreshButton}><Text style={styles.refreshText}>↻ Refresh</Text></Pressable>}
                />
                <Field label="Search records" value={adminSearch} onChangeText={setAdminSearch} placeholder="Search by name, email or code" autoCapitalize="none" />
                <View style={styles.adminToolbarRow}>
                    <Pressable onPress={() => loadAdminModule(adminResource, adminSearch)} style={[styles.adminToolbarButton, styles.adminSearchButton]}>
                        <Text style={styles.adminToolbarButtonText}>Search</Text>
                    </Pressable>
                    <Pressable onPress={() => openAdminEditor(null)} disabled={!adminMeta} style={[styles.adminToolbarButton, styles.adminAddButton, !adminMeta && styles.buttonDisabled]}>
                        <Text style={styles.adminToolbarButtonText}>＋ Add new</Text>
                    </Pressable>
                </View>
                {adminModuleLoading ? (
                    <View style={styles.adminLoadingCard}><ActivityIndicator color={COLORS.blue} /><Text style={styles.loadingBarText}>Loading records…</Text></View>
                ) : adminRecords.length === 0 ? (
                    <EmptyState title="No records found" body="Try another search or add a new record to this section." />
                ) : adminRecords.map((record) => (
                    <View key={String(record[adminMeta?.primary_key || 'id'])} style={styles.card}>
                        <View style={styles.cardTopRow}>
                            <View style={styles.flexOne}>
                                <Text style={styles.cardTitle}>{adminRecordTitle(record)}</Text>
                                {!!adminRecordSubtitle(record) && (
                                    <Text style={styles.cardSubtitle}>{adminRecordSubtitle(record)}</Text>
                                )}
                            </View>
                        </View>
                        <View style={styles.adminRecordActions}>
                            <Pressable onPress={() => openAdminEditor(record)} style={[styles.adminRecordButton, styles.adminEditButton]}>
                                <Text style={styles.adminEditButtonText}>Edit</Text>
                            </Pressable>
                            <Pressable onPress={() => deleteAdminRecord(record)} style={[styles.adminRecordButton, styles.adminDeleteButton]}>
                                <Text style={styles.adminDeleteButtonText}>Delete</Text>
                            </Pressable>
                        </View>
                    </View>
                ))}
                <Text style={styles.adminListFootnote}>Showing up to 50 records. Use search to find older records.</Text>
            </>
        );
    };

    const renderHome = () => (
        <>
            {role !== 'student' && (
                <View style={styles.welcomeCard}>
                    <Text style={styles.welcomeOverline}>
                        {role === 'teacher' ? 'TEACHER WORKSPACE' : 'ADMIN WORKSPACE'}
                    </Text>
                    <Text style={styles.welcomeTitle}>Hello, {displayName.split(' ')[0] || displayName}!</Text>
                    <Text style={styles.welcomeBody}>
                        {role === 'teacher'
                            ? 'Manage learning activities, quizzes, and student progress.'
                            : 'Manage student records, academic setup, and campus activities.'}
                    </Text>
                </View>
            )}
            {role === 'student' ? (
                <>
                    <View style={styles.studentHeroCard}>
                        <View style={styles.studentHeroTopRow}>
                            <View style={styles.studentHeroCopy}>
                                <Text style={styles.studentHeroOverline}>STUDENT DASHBOARD</Text>
                                <Text style={styles.studentHeroGreeting}>Welcome back,</Text>
                                <Text style={styles.studentHeroName}>{displayName.split(' ')[0] || displayName}!</Text>
                            </View>
                            <View style={styles.studentHeroAvatar}>
                                <Text style={styles.studentHeroAvatarText}>{String(displayName || 'S').trim().charAt(0).toUpperCase()}</Text>
                            </View>
                        </View>
                        <Text style={styles.studentHeroBody}>Your academic journey, organized in one place. Keep track of your learning and stay up to date.</Text>
                        <Pressable onPress={() => refreshData(true)} style={({ pressed }) => [styles.studentHeroRefresh, pressed && styles.pressed]}>
                            <Text style={styles.studentHeroRefreshText}>↻  Refresh overview</Text>
                        </Pressable>
                    </View>

                    <SectionTitle title="Your progress" subtitle="A quick look at your learning activity" />
                    <View style={styles.statsRow}>
                        <StatCard label="Attempts" value={results.length} symbol="✓" />
                        <StatCard label="Average" value={`${average.toFixed(1)}%`} accent={COLORS.green} symbol="↗" />
                        <StatCard label="Best score" value={`${best.toFixed(0)}%`} accent={COLORS.purple} symbol="★" />
                    </View>

                    <SectionTitle title="Quick access" subtitle="Go directly to your student tools" />
                    <View style={styles.studentQuickActionsRow}>
                        <Pressable onPress={() => goTo('quizzes')} style={({ pressed }) => [styles.studentQuickAction, pressed && styles.pressed]}>
                            <View style={[styles.studentQuickActionIcon, { backgroundColor: '#EEF1FF' }]}><Text style={styles.studentQuickActionIconText}>📘</Text></View>
                            <Text style={styles.studentQuickActionLabel}>Quizzes</Text>
                            <Text style={styles.studentQuickActionHint}>Learning activities</Text>
                        </Pressable>
                        <Pressable onPress={() => goTo('results')} style={({ pressed }) => [styles.studentQuickAction, pressed && styles.pressed]}>
                            <View style={[styles.studentQuickActionIcon, { backgroundColor: '#EAF8F1' }]}><Text style={styles.studentQuickActionIconText}>📊</Text></View>
                            <Text style={styles.studentQuickActionLabel}>Results</Text>
                            <Text style={styles.studentQuickActionHint}>Your performance</Text>
                        </Pressable>
                        <Pressable onPress={() => goTo('notifications')} style={({ pressed }) => [styles.studentQuickAction, pressed && styles.pressed]}>
                            <View style={[styles.studentQuickActionIcon, { backgroundColor: '#FFF4E7' }]}>
                                <Text style={styles.studentQuickActionIconText}>🔔</Text>
                                {unreadCount > 0 && <View style={styles.studentQuickActionBadge}><Text style={styles.studentQuickActionBadgeText}>{unreadCount > 9 ? '9+' : unreadCount}</Text></View>}
                            </View>
                            <Text style={styles.studentQuickActionLabel}>Alerts</Text>
                            <Text style={styles.studentQuickActionHint}>{unreadCount > 0 ? `${unreadCount} unread` : 'Notifications'}</Text>
                        </Pressable>
                    </View>

                    <SectionTitle title="Academic overview" subtitle="Your registered study details" />
                    <View style={styles.studentAcademicCard}>
                        <View style={styles.studentAcademicHeader}>
                            <View style={styles.studentAcademicIcon}><Text style={styles.studentAcademicIconText}>🎓</Text></View>
                            <View style={styles.flexOne}>
                                <Text style={styles.studentAcademicTitle}>My academic details</Text>
                                <Text style={styles.studentAcademicSubtitle}>Your current study information</Text>
                            </View>
                        </View>
                        <ProfileRow label="Course" value={academic?.course || 'Not assigned'} />
                        <ProfileRow label="Degree" value={academic?.degree || 'Not assigned'} />
                        <ProfileRow label="Batch" value={academic?.batch || 'Not assigned'} />
                        <ProfileRow label="Admission" value={academic?.admission_status || 'Not linked'} last />
                    </View>

                    <SectionTitle
                        title="Latest learning activities"
                        subtitle="Your recently assigned quizzes"
                        action={<Pressable onPress={() => goTo('quizzes')} style={styles.refreshButton}><Text style={styles.refreshText}>View all →</Text></Pressable>}
                    />
                    {quizzes.length === 0 ? (
                        <EmptyState title="No learning activities yet" body="New quizzes assigned to your academic group will appear here." />
                    ) : quizzes.slice(0, 2).map((quiz) => (
                        <QuizCard key={`student-home-${quiz.id}`} quiz={quiz} onStart={openQuiz} starting={startingQuizId === quiz.id} />
                    ))}
                </>
            ) : role === 'teacher' ? (
                <>
                    <View style={styles.statsRow}>
                        <StatCard label="My quizzes" value={quizzes.length} symbol="▤" />
                        <StatCard label="Submissions" value={results.length} accent={COLORS.green} symbol="✓" />
                    </View>

                    <SectionTitle title="Quick actions" subtitle="Common teacher tasks" />
                    <PrimaryButton title="Create a quiz  +" onPress={() => goTo('create')} />
                    <PrimaryButton title="Review student results  →" onPress={() => goTo('results')} variant="secondary" />

                    <SectionTitle
                        title="Upcoming quizzes"
                        subtitle="Published quizzes that are not available yet"
                        action={<Pressable onPress={() => refreshData(false)} style={styles.refreshButton}><Text style={styles.refreshText}>↻ Refresh</Text></Pressable>}
                    />
                    {quizzes.filter(isUpcomingQuiz).length === 0 ? (
                        <EmptyState title="No upcoming quizzes" body="Scheduled quizzes will appear here before their availability time." />
                    ) : quizzes.filter(isUpcomingQuiz).slice(0, 3).map((quiz) => (
                        <QuizCard key={quiz.id} quiz={{ ...quiz, is_available: false }} />
                    ))}

                    <SectionTitle
                        title="Recent submissions"
                        subtitle="Latest student quiz attempts"
                        action={<Pressable onPress={() => goTo('results')} style={styles.refreshButton}><Text style={styles.refreshText}>View all →</Text></Pressable>}
                    />
                    {results.length === 0 ? (
                        <EmptyState title="No submissions yet" body="Student submissions will appear here after they complete your quizzes." />
                    ) : results.slice(0, 3).map((item) => (
                        <TeacherSubmissionCard key={item.id} item={item} />
                    ))}
                </>
            ) : (
                <>
                    <SectionTitle
                        title="System overview"
                        subtitle="Live totals from your student management database"
                        action={<Pressable onPress={() => refreshData(true)} style={styles.refreshButton}><Text style={styles.refreshText}>↻ Refresh</Text></Pressable>}
                    />
                    <View style={styles.adminStatsGrid}>
                        {ADMIN_STAT_ITEMS.map((item) => (
                            <Pressable key={item.key} onPress={() => openAdminModule(item.key)} style={({ pressed }) => [styles.adminStatCard, pressed && styles.pressed]}>
                                <View style={[styles.adminStatSymbol, { backgroundColor: `${item.accent}18` }]}>
                                    <Text style={styles.adminStatSymbolText}>{item.symbol}</Text>
                                </View>
                                <Text style={styles.adminStatValue}>
                                    {adminStats[item.key] === undefined ? '—' : adminStats[item.key]}
                                </Text>
                                <Text style={styles.adminStatLabel}>{item.label}</Text>
                                <Text style={styles.adminStatManage}>Manage  →</Text>
                            </Pressable>
                        ))}
                    </View>
                    <View style={styles.adminInfoCard}>
                        <Text style={styles.adminInfoTitle}>Management workspace</Text>
                        <Text style={styles.adminInfoBody}>
                            Tap any statistics card to manage its records, or open the Management tab to see all eight sections.
                            All changes are sent to the protected Laravel Admin API.
                        </Text>
                    </View>
                </>
            )}
        </>
    );

    const renderMaterials = () => (
        <>
            <SectionTitle
                title={role === 'teacher' ? 'Lecture Materials' : 'Learning Materials'}
                subtitle={role === 'teacher' ? 'Upload a document and optionally add a related online link for students.' : 'Study files and learning links shared by your teachers.'}
                action={<Pressable onPress={() => loadMaterials(true)} style={styles.refreshButton}><Text style={styles.refreshText}>↻ Refresh</Text></Pressable>}
            />

            {role === 'teacher' && (
                <View style={styles.card}>
                    <Text style={styles.formSectionTitle}>Share new material</Text>
                    <Text style={styles.cardSubtitle}>Students can access materials shared by teachers from their Materials tab.</Text>

                    <Field label="Material title *" value={materialTitle} onChangeText={setMaterialTitle} placeholder="e.g. Week 01 - Introduction to Computing" autoCapitalize="sentences" />
                    <Field label="Description" value={materialDescription} onChangeText={setMaterialDescription} placeholder="Add a short note for students (optional)" multiline autoCapitalize="sentences" />
                    <Pressable onPress={chooseMaterialFile} style={styles.materialChooseFile}>
                        <Text style={styles.materialChooseFileTitle}>{selectedMaterialFile ? 'Change selected file' : '＋ Choose a document (optional)'}</Text>
                        <Text style={styles.materialChooseFileHint}>PDF, Word (.doc/.docx), PowerPoint (.ppt/.pptx) · up to 25 MB</Text>
                    </Pressable>
                    {!!selectedMaterialFile && <Text style={styles.materialSelectedFile}>Selected: {selectedMaterialFile.name}</Text>}
                    <Field label="Google Drive / online link (optional)" value={materialLink} onChangeText={setMaterialLink} placeholder="https://drive.google.com/..." keyboardType="url" autoCapitalize="none" helperText="Add a link to the same lesson or a related resource. Make sure students have permission to view it." />
                    {!!materialsError && <Notice text={materialsError} />}
                    <PrimaryButton title={savingMaterial ? 'Sharing…' : 'Share with students'} onPress={shareMaterial} loading={savingMaterial} disabled={savingMaterial} />
                </View>
            )}

            <SectionTitle title={role === 'teacher' ? 'Shared by me' : 'Available materials'} subtitle={role === 'teacher' ? 'Documents and optional links you have shared.' : 'Open a file or follow a shared resource link.'} />
            {!!materialsError && role === 'student' && <Notice text={materialsError} />}
            {materialsLoading ? (
                <View style={styles.adminLoadingCard}><ActivityIndicator color={COLORS.blue} /><Text style={styles.loadingBarText}>Loading lecture materials…</Text></View>
            ) : materials.length === 0 ? (
                <EmptyState title="No lecture materials yet" body={role === 'teacher' ? 'Upload a document, add an optional link, or share just a link with students.' : 'Your teachers have not shared any learning materials yet.'} />
            ) : materials.map((item) => (
                <View key={String(item.id)} style={styles.card}>
                    <View style={styles.cardTopRow}>
                        <View style={styles.flexOne}>
                            <Text style={styles.cardTitle}>{item.title}</Text>
                            <Text style={styles.cardSubtitle}>{item.teacher_name || (role === 'teacher' ? 'You shared this material' : 'Teacher shared material')}</Text>
                        </View>
                        <View style={styles.materialTypePill}>
                            <Text style={styles.materialTypePillText}>
                                {item.download_url && item.external_url
                                    ? `${String(item.original_filename || 'FILE').split('.').pop().toUpperCase()} + LINK`
                                    : item.download_url
                                        ? String(item.original_filename || 'FILE').split('.').pop().toUpperCase()
                                        : 'LINK'}
                            </Text>
                        </View>
                    </View>
                    {!!item.description && <Text style={styles.cardDescription}>{item.description}</Text>}
                    {!!item.original_filename && <Text style={styles.materialMetaText}>📄 {item.original_filename}</Text>}
                    <View style={styles.materialActions}>
                        {!!item.download_url && (
                            <Pressable onPress={() => openMaterial(item, 'file')} style={styles.materialOpenButton}>
                                <Text style={styles.materialOpenButtonText}>Download file ↓</Text>
                            </Pressable>
                        )}
                        {!!item.external_url && (
                            <Pressable onPress={() => openMaterial(item, 'link')} style={styles.materialOpenButton}>
                                <Text style={styles.materialOpenButtonText}>Open link ↗</Text>
                            </Pressable>
                        )}
                        {role === 'teacher' && (
                            <Pressable onPress={() => deleteMaterial(item)} style={styles.materialDeleteButton}>
                                <Text style={styles.materialDeleteButtonText}>Delete</Text>
                            </Pressable>
                        )}
                    </View>
                </View>
            ))}
            <Text style={styles.adminListFootnote}>Files are stored in the StudentManage server. External links open using the sharing permissions set by their owner.</Text>
        </>
    );

    const renderQuizzes = () => (
        <>
            <SectionTitle
                title={role === 'student' ? 'My quizzes' : 'My quizzes'}
                subtitle={role === 'student' ? 'Assigned to your course, degree, and batch.' : 'Quizzes created by your teacher account.'}
                action={<Pressable onPress={() => refreshData(false)} style={styles.refreshButton}><Text style={styles.refreshText}>↻ Refresh</Text></Pressable>}
            />
            {role === 'teacher' && <PrimaryButton title="Create new quiz  +" onPress={() => goTo('create')} />}
            {quizzes.length === 0 ? <EmptyState title="No quizzes found" body={role === 'student' ? 'Published quizzes assigned to your academic group will appear here.' : 'Create a quiz to start collecting student submissions.'} /> : quizzes.map((quiz) => (
                <QuizCard key={quiz.id} quiz={quiz} onStart={role === 'student' ? openQuiz : null} starting={startingQuizId === quiz.id} />
            ))}
        </>
    );

    const renderResults = () => {
        if (role === 'student') {
            return (
                <>
                    <SectionTitle title="My results" subtitle="Your submitted quizzes and performance." action={<Pressable onPress={() => refreshData(false)} style={styles.refreshButton}><Text style={styles.refreshText}>↻ Refresh</Text></Pressable>} />
                    <View style={styles.statsRow}>
                        <StatCard label="Total attempts" value={results.length} symbol="✓" />
                        <StatCard label="Average score" value={`${average.toFixed(2)}%`} accent={COLORS.green} symbol="↗" />
                        <StatCard label="Best score" value={`${best.toFixed(0)}%`} accent={COLORS.purple} symbol="★" />
                    </View>
                    {results.length === 0 ? <EmptyState title="No results yet" body="Your marks and percentages will appear here after you submit a quiz." /> : results.map((item) => <StudentResultCard key={item.attempt_id || item.id} item={item} />)}
                </>
            );
        }
        return (
            <>
                <SectionTitle title="Student submissions" subtitle="Results from quizzes owned by your teacher account." action={<Pressable onPress={() => refreshData(false)} style={styles.refreshButton}><Text style={styles.refreshText}>↻ Refresh</Text></Pressable>} />
                {results.length === 0 ? <EmptyState title="No submissions yet" body="When students submit your quizzes, their submissions will appear here." /> : results.map((item) => <TeacherSubmissionCard key={item.id} item={item} />)}
            </>
        );
    };

    const renderCreateQuiz = () => (
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
            <SectionTitle title="Create a quiz" subtitle="Complete the quiz details and add your questions." />

            <View style={styles.card}>
                <Text style={styles.formSectionTitle}>Quiz information</Text>
                <Field label="Quiz title *" value={quizForm.title} onChangeText={(value) => updateQuizForm('title', value)} placeholder="e.g. Software Engineering Fundamentals Quiz" autoCapitalize="sentences" />

                <DropdownField
                    label="Course *"
                    value={quizForm.course}
                    placeholder={academicOptionsLoading ? 'Loading registered courses...' : courseOptions.length ? 'Select a course' : 'No academic setup found'}
                    options={courseOptions}
                    onSelect={(value) => updateQuizForm('course', value)}
                    disabled={academicOptionsLoading || courseOptions.length === 0}
                    emptyMessage="No Course / Degree / Batch combinations are registered in Academic Setup."
                />
                <DropdownField
                    label="Degree *"
                    value={quizForm.degree}
                    placeholder={!quizForm.course ? 'Select a course first' : degreeOptions.length ? 'Select a degree' : 'No degrees for this course'}
                    options={degreeOptions}
                    onSelect={(value) => updateQuizForm('degree', value)}
                    disabled={!quizForm.course || degreeOptions.length === 0}
                    emptyMessage="No degrees are configured for this course."
                />
                <DropdownField
                    label="Batch *"
                    value={quizForm.batch}
                    placeholder={!quizForm.degree ? 'Select a course and degree first' : batchOptions.length ? 'Select a batch' : 'No batches for this degree'}
                    options={batchOptions}
                    onSelect={(value) => updateQuizForm('batch', value)}
                    disabled={!quizForm.course || !quizForm.degree || batchOptions.length === 0}
                    emptyMessage="No batches are configured for this Course and Degree."
                />

                {!!academicOptionsError && <Notice text={academicOptionsError} />}

                <DropdownField
                    label="Subject *"
                    value={quizForm.subject}
                    placeholder={
                        subjectsLoading ? 'Loading registered subjects...' :
                            !quizForm.course ? 'Select a course first' :
                                !quizForm.degree ? 'Select a degree first' :
                                    subjectsError ? 'Unable to load subjects' :
                                        subjectOptions.length ? 'Select a subject' : 'No subjects assigned to this course and degree'
                    }
                    options={subjectOptions}
                    onSelect={(value) => updateQuizForm('subject', value)}
                    disabled={subjectsLoading || !quizForm.course || !quizForm.degree || subjectOptions.length === 0}
                    helperText="Subjects are filtered by the selected Course and Degree. Register or assign subjects in the Admin Dashboard if the list is empty."
                    emptyMessage="No subjects are assigned to this Course and Degree."
                />
                {!!subjectsError && <Notice text={subjectsError} />}

                <Field
                    label="Duration (minutes) *"
                    value={quizForm.duration_minutes}
                    onChangeText={(value) => updateQuizForm('duration_minutes', value)}
                    keyboardType="number-pad"
                    helperText="Enter a duration between 1 and 300 minutes."
                />
                <Field
                    label="Available Date & Time *"
                    value={quizForm.available_at}
                    onChangeText={(value) => updateQuizForm('available_at', value)}
                    placeholder="YYYY-MM-DDTHH:mm"
                    helperText="Use your local date and time. Students can access this quiz from the selected date and time. The default is 15 minutes from now."
                    autoCapitalize="none"
                />
                <Field
                    label="Description"
                    value={quizForm.description}
                    onChangeText={(value) => updateQuizForm('description', value)}
                    placeholder="What will students learn from this quiz?"
                    multiline
                    autoCapitalize="sentences"
                />
            </View>

            <SectionTitle
                title="Quiz questions"
                subtitle="Add multiple-choice questions and select the correct answer."
                action={<Text style={styles.questionCountBadge}>{quizForm.questions.length} {quizForm.questions.length === 1 ? 'question' : 'questions'}</Text>}
            />

            {quizForm.questions.map((question, index) => (
                <View key={`question-${index}`} style={styles.card}>
                    <View style={styles.cardTopRow}>
                        <Text style={styles.cardTitle}>Question {index + 1}</Text>
                        {quizForm.questions.length > 1 && (
                            <Pressable onPress={() => setQuizForm((form) => ({ ...form, questions: form.questions.filter((_, i) => i !== index) }))}>
                                <Text style={styles.removeText}>Remove</Text>
                            </Pressable>
                        )}
                    </View>
                    <Field label="Question text *" value={question.question_text} onChangeText={(value) => updateQuestion(index, 'question_text', value)} placeholder="Enter your question" multiline autoCapitalize="sentences" />
                    {['a', 'b', 'c', 'd'].map((letter) => (
                        <Field
                            key={letter}
                            label={`Option ${letter.toUpperCase()} *`}
                            value={question[`option_${letter}`]}
                            onChangeText={(value) => updateQuestion(index, `option_${letter}`, value)}
                            placeholder={`Enter option ${letter.toUpperCase()}`}
                            autoCapitalize="sentences"
                        />
                    ))}
                    <Text style={styles.fieldLabel}>Correct answer *</Text>
                    <View style={styles.optionRow}>
                        {['A', 'B', 'C', 'D'].map((option) => (
                            <Pressable
                                key={option}
                                onPress={() => updateQuestion(index, 'correct_option', option)}
                                style={[styles.correctOption, question.correct_option === option && styles.correctOptionSelected]}
                            >
                                <Text style={[styles.correctOptionText, question.correct_option === option && styles.correctOptionTextSelected]}>
                                    Option {option}
                                </Text>
                            </Pressable>
                        ))}
                    </View>
                    <Field label="Marks *" value={question.marks} onChangeText={(value) => updateQuestion(index, 'marks', value)} keyboardType="number-pad" />
                </View>
            ))}

            <PrimaryButton
                title="Add another question  +"
                onPress={() => setQuizForm((form) => ({ ...form, questions: [...form.questions, EMPTY_QUESTION()] }))}
                variant="secondary"
            />
            <PrimaryButton title="Schedule Quiz" onPress={createQuiz} loading={creatingQuiz} disabled={creatingQuiz} />
            <Text style={styles.formNote}>
                Course, Degree and Batch options come from Academic Setup. Subjects are filtered by the selected Course and Degree. The quiz is saved through the existing Laravel API.
            </Text>
        </KeyboardAvoidingView>
    );

    const renderTakeQuiz = () => {
        const expired = remainingSeconds !== null && remainingSeconds <= 0;
        return (
            <>
                <Pressable onPress={exitQuiz} style={styles.backButton}><Text style={styles.backButtonText}>← Exit quiz</Text></Pressable>
                <View style={styles.quizHeaderCard}>
                    <Text style={styles.eyebrow}>TIMED QUIZ</Text>
                    <Text style={styles.quizHeaderTitle}>{activeQuiz?.title || 'Quiz'}</Text>
                    <Text style={styles.cardSubtitle}>{activeQuiz?.subject || 'General subject'} · {activeQuiz?.duration_minutes || '—'} minutes</Text>
                    <View style={[styles.timerPill, expired ? styles.timerExpired : remainingSeconds !== null && remainingSeconds <= 60 ? styles.timerWarning : styles.timerNormal]}>
                        <Text style={[styles.timerLabel, expired ? styles.timerExpiredText : remainingSeconds !== null && remainingSeconds <= 60 ? styles.timerWarningText : styles.timerNormalText]}>TIME REMAINING</Text>
                        <Text style={[styles.timerValue, expired ? styles.timerExpiredText : remainingSeconds !== null && remainingSeconds <= 60 ? styles.timerWarningText : styles.timerNormalText]}>{remainingSeconds === null ? '--:--' : formatDuration(remainingSeconds)}</Text>
                    </View>
                    <Text style={styles.timerHint}>The deadline is controlled by the server. Leaving and reopening the quiz will not reset the timer.</Text>
                </View>
                <Notice text={saveWarning} type="warning" />
                <Notice text={attemptError} />
                {Array.isArray(activeQuiz?.questions) && activeQuiz.questions.map((question, index) => (
                    <View key={question.id} style={styles.card}>
                        <View style={styles.questionTopRow}><Text style={styles.questionNumber}>QUESTION {index + 1}</Text><Text style={styles.questionMarks}>{question.marks ?? 1} mark(s)</Text></View>
                        <Text style={styles.questionText}>{question.question_text}</Text>
                        {Object.entries(question.options || {}).filter(([, value]) => value !== null && value !== undefined && String(value).trim() !== '').map(([option, value]) => {
                            const chosen = selectedAnswers[String(question.id)] === option;
                            return (
                                <Pressable key={option} onPress={() => selectAnswer(question.id, option)} disabled={submittingAttempt || expired} style={[styles.answerOption, chosen && styles.answerOptionChosen, (submittingAttempt || expired) && styles.answerOptionDisabled]}>
                                    <View style={[styles.answerLetter, chosen && styles.answerLetterChosen]}><Text style={[styles.answerLetterText, chosen && styles.answerLetterTextChosen]}>{option}</Text></View>
                                    <Text style={[styles.answerOptionText, chosen && styles.answerOptionTextChosen]}>{String(value)}</Text>
                                    {chosen && <Text style={styles.answerCheck}>✓</Text>}
                                </Pressable>
                            );
                        })}
                    </View>
                ))}
                <Text style={styles.answersCount}>{Object.keys(selectedAnswers).filter((key) => selectedAnswers[key]).length} of {activeQuiz?.questions?.length || 0} answered</Text>
                {expired ? (
                    <PrimaryButton title="Retry automatic submission" onPress={() => submitQuizRef.current?.(true)} loading={submittingAttempt} disabled={submittingAttempt} />
                ) : (
                    <PrimaryButton title="Submit answers" onPress={() => submitQuizRef.current?.(false)} loading={submittingAttempt} disabled={submittingAttempt || !activeQuiz?.questions?.length} />
                )}
            </>
        );
    };

    const renderQuizResult = () => (
        <View style={styles.card}>
            <View style={styles.resultSuccessIcon}><Text style={styles.resultSuccessIconText}>✓</Text></View>
            <Text style={styles.resultTitle}>Quiz submitted successfully</Text>
            <Text style={styles.resultMessage}>{attemptResult?.message || 'Your answers have been saved and graded.'}</Text>
            {attemptResult?.attempt_id && <Text style={styles.dateCaption}>Submission reference: #{attemptResult.attempt_id}</Text>}
            <View style={styles.resultBigScore}><Text style={styles.resultBigScoreLabel}>PERCENTAGE</Text><Text style={styles.resultBigScoreValue}>{Number(attemptResult?.percentage || 0).toFixed(2)}%</Text></View>
            <View style={styles.statsRow}>
                <StatCard label="Marks obtained" value={attemptResult?.marks_obtained ?? 0} accent={COLORS.green} symbol="✓" />
                <StatCard label="Total marks" value={attemptResult?.total_marks ?? 0} accent={COLORS.purple} symbol="Σ" />
            </View>
            <PrimaryButton title="Go to My Results" onPress={() => { setActiveQuiz(null); setAttemptSession(null); setDeadlineMs(null); setRemainingSeconds(null); setScreen('results'); void refreshData(false); }} />
            <PrimaryButton title="Back to My Quizzes" onPress={() => { setActiveQuiz(null); setAttemptSession(null); setDeadlineMs(null); setRemainingSeconds(null); setScreen('quizzes'); void refreshData(false); }} variant="secondary" />
        </View>
    );

    return (
        <View style={styles.appRoot}>
            <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />
            <View style={styles.topBar}>
                <View style={styles.topBrandMark}><Text style={styles.topBrandMarkText}>S</Text></View>
                <View style={styles.topBarCopy}>
                    <Text style={styles.topBarTitle}>StudentManage</Text>
                    <Text style={styles.topBarSubtitle}>
                        {role === 'student'
                            ? 'Student Portal · Your academic workspace'
                            : role === 'teacher'
                                ? 'Teacher Workspace · Manage learning activities'
                                : 'Admin Workspace · Manage the platform'}
                    </Text>
                </View>
            </View>
            {(error || notice) && screen !== 'takeQuiz' && screen !== 'quizResult' && <View style={styles.globalNotice}><Notice text={error} /><Notice text={notice} type="success" /></View>}
            {loadingData && <View style={styles.loadingBar}><ActivityIndicator size="small" color={COLORS.blue} /><Text style={styles.loadingBarText}>Loading your workspace…</Text></View>}
            <ScrollView
                key={screen}
                style={styles.mainScroll}
                contentContainerStyle={styles.mainContent}
                keyboardShouldPersistTaps="handled"
                showsVerticalScrollIndicator={false}
            >
                {screen === 'home' && renderHome()}
                {screen === 'adminManage' && role === 'admin' && renderAdminManage()}
                {screen === 'adminModule' && role === 'admin' && renderAdminModule()}
                {screen === 'quizzes' && renderQuizzes()}
                {screen === 'materials' && (role === 'teacher' || role === 'student') && renderMaterials()}
                {screen === 'results' && renderResults()}
                {screen === 'notifications' && role === 'student' && (
                    <StudentNotificationsScreen
                        api={api}
                        onUnreadCountChange={setUnreadCount}
                    />
                )}
                {screen === 'create' && role === 'teacher' && renderCreateQuiz()}
                {screen === 'takeQuiz' && renderTakeQuiz()}
                {screen === 'quizResult' && renderQuizResult()}
                {screen === 'profile' && (
                    <>
                        <SectionTitle title="My profile" subtitle="Account details" />
                        <View style={styles.card}>
                            <ProfileRow label="Name" value={profile?.name || '—'} />
                            <ProfileRow label="Email" value={profile?.email || '—'} />
                            <ProfileRow
                                label={role === 'student' ? 'Registration number' : role === 'teacher' ? 'Teaching subject' : 'Account role'}
                                value={role === 'student' ? (profile?.reg_no || '—') : role === 'teacher' ? (profile?.subject || '—') : (profile?.role || 'admin')}
                            />
                            <ProfileRow label="API endpoint" value={apiBase || '—'} last />
                        </View>
                        <PrimaryButton title="Refresh data" onPress={() => refreshData(true)} variant="secondary" />
                        <PrimaryButton title="Sign out" onPress={handleLogout} variant="danger" />
                    </>
                )}
            </ScrollView>
            <Modal
                visible={adminEditorVisible}
                animationType="slide"
                presentationStyle="fullScreen"
                onRequestClose={() => setAdminEditorVisible(false)}
            >
                <KeyboardAvoidingView style={styles.adminEditorRoot} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
                    <View style={styles.adminEditorHeader}>
                        <View style={styles.flexOne}>
                            <Text style={styles.adminEditorTitle}>{adminEditingId !== null ? 'Edit record' : 'Add new record'}</Text>
                            <Text style={styles.cardSubtitle}>{adminMeta?.title || 'Admin management'}</Text>
                        </View>
                        <Pressable onPress={() => setAdminEditorVisible(false)} style={styles.adminCloseButton}>
                            <Text style={styles.adminCloseButtonText}>×</Text>
                        </Pressable>
                    </View>
                    <ScrollView contentContainerStyle={styles.adminEditorContent} keyboardShouldPersistTaps="handled">
                        <Notice text={error} />
                        {(adminMeta?.fields || []).map((field) => {
                            const currentValue = adminForm[field.name] ?? '';
                            const selectedOption = (field.options || []).find((option) => String(option.value) === String(currentValue));
                            const label = `${field.label}${field.required && adminEditingId === null ? ' *' : ''}`;
                            if (field.type === 'select') {
                                return (
                                    <DropdownField
                                        key={field.name}
                                        label={label}
                                        value={selectedOption?.label || ''}
                                        placeholder={`Select ${field.label.toLowerCase()}`}
                                        options={(field.options || []).map((option) => option.label)}
                                        onSelect={(optionLabel) => {
                                            const option = (field.options || []).find((item) => item.label === optionLabel);
                                            updateAdminForm(field.name, option ? String(option.value) : '');
                                        }}
                                        helperText={field.helper}
                                        disabled={!field.options || field.options.length === 0}
                                        emptyMessage="No matching records are available. Add the related record first."
                                    />
                                );
                            }
                            return (
                                <Field
                                    key={field.name}
                                    label={label}
                                    value={String(currentValue)}
                                    onChangeText={(value) => updateAdminForm(field.name, value)}
                                    placeholder={field.type === 'date' ? 'YYYY-MM-DD' : field.type === 'datetime' ? 'YYYY-MM-DD HH:mm:ss' : field.sensitive && adminEditingId !== null ? 'Leave blank to keep current password' : `Enter ${field.label.toLowerCase()}`}
                                    secureTextEntry={field.sensitive}
                                    keyboardType={field.type === 'number' ? 'numeric' : field.name === 'email' ? 'email-address' : 'default'}
                                    multiline={field.type === 'multiline'}
                                    autoCapitalize={field.name === 'email' ? 'none' : 'sentences'}
                                    helperText={field.sensitive && adminEditingId !== null ? 'Leave this empty to keep the existing password.' : field.helper}
                                />
                            );
                        })}
                        <PrimaryButton title={adminSaving ? 'Saving…' : adminEditingId !== null ? 'Save changes' : 'Create record'} onPress={saveAdminRecord} loading={adminSaving} disabled={adminSaving} />
                        <PrimaryButton title="Cancel" onPress={() => setAdminEditorVisible(false)} variant="secondary" disabled={adminSaving} />
                    </ScrollView>
                </KeyboardAvoidingView>
            </Modal>
            {screen !== 'takeQuiz' && screen !== 'quizResult' && (
                <View style={styles.bottomNav}>
                    {role === 'admin' ? (
                        <>
                            <NavItem label="Home" icon="⌂" active={screen === 'home'} onPress={() => goTo('home')} />
                            <NavItem label="Manage" icon="▦" active={screen === 'adminManage' || screen === 'adminModule'} onPress={() => goTo('adminManage')} />
                            <NavItem label="Profile" icon="◉" active={screen === 'profile'} onPress={() => goTo('profile')} />
                        </>
                    ) : (
                        <>
                            <NavItem label="Home" icon="⌂" active={screen === 'home'} onPress={() => goTo('home')} />
                            <NavItem label="Quizzes" icon="▤" active={screen === 'quizzes'} onPress={() => goTo('quizzes')} />
                            <NavItem label="Results" icon="▥" active={screen === 'results'} onPress={() => goTo('results')} />
                            <NavItem label="Materials" icon="▣" active={screen === 'materials'} onPress={() => goTo('materials')} />
                            {role === 'teacher' ? (
                                <>
                                    <NavItem label="Create" icon="+" active={screen === 'create'} onPress={() => goTo('create')} />
                                    <NavItem label="Profile" icon="◉" active={screen === 'profile'} onPress={() => goTo('profile')} />
                                </>
                            ) : (
                                <>
                                    <NavItem label="Alerts" icon="♢" badge={unreadCount} active={screen === 'notifications'} onPress={() => goTo('notifications')} />
                                    <NavItem label="Profile" icon="◉" active={screen === 'profile'} onPress={() => goTo('profile')} />
                                </>
                            )}
                        </>
                    )}
                </View>
            )}
        </View>
    );
}

function ProfileRow({ label, value, last }) {
    return <View style={[styles.profileRow, last && styles.profileRowLast]}><Text style={styles.profileLabel}>{label}</Text><Text style={styles.profileValue}>{String(value ?? '—')}</Text></View>;
}

function EmptyState({ title, body }) {
    return <View style={styles.emptyState}><View style={styles.emptyIcon}><Text style={styles.emptyIconText}>✓</Text></View><Text style={styles.emptyTitle}>{title}</Text><Text style={styles.emptyBody}>{body}</Text></View>;
}

function NavItem({ label, icon, active, onPress, badge = 0 }) {
    return (
        <Pressable onPress={onPress} style={styles.navItem}>
            <View style={[styles.navIconWrap, active && styles.navIconWrapActive]}>
                <Text style={[styles.navIcon, active && styles.navIconActive]}>{icon}</Text>
                {badge > 0 && (
                    <View style={styles.navBadge}>
                        <Text style={styles.navBadgeText}>{badge > 99 ? '99+' : badge}</Text>
                    </View>
                )}
            </View>
            <Text style={[styles.navLabel, active && styles.navLabelActive]}>{label}</Text>
        </Pressable>
    );
}

const styles = StyleSheet.create({
    appRoot: { flex: 1, backgroundColor: COLORS.background, paddingTop: Platform.OS === 'android' ? (StatusBar.currentHeight || 0) : 0 },
    loadingScreen: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: COLORS.background, gap: 14 },
    loadingText: { color: COLORS.muted, fontSize: 14 },
    loginScreen: { flex: 1, backgroundColor: COLORS.background },
    loginScroll: { padding: 22, paddingTop: 34, paddingBottom: 42 },
    brandRow: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: 34 },
    brandMark: { width: 48, height: 48, borderRadius: 16, alignItems: 'center', justifyContent: 'center', backgroundColor: COLORS.blue },
    brandMarkText: { color: '#fff', fontSize: 25, fontWeight: '900' },
    brandName: { color: COLORS.ink, fontSize: 20, fontWeight: '900', letterSpacing: -0.5 },
    brandTagline: { color: COLORS.muted, fontSize: 12, marginTop: 3 },
    loginHero: { marginBottom: 22 },
    eyebrow: { color: COLORS.blue, fontSize: 10, fontWeight: '900', letterSpacing: 1.6, marginBottom: 9 },
    loginTitle: { color: COLORS.ink, fontSize: 35, fontWeight: '900', lineHeight: 40, letterSpacing: -1.3, marginBottom: 12 },
    loginDescription: { color: COLORS.muted, fontSize: 14, lineHeight: 22 },
    loginCard: { backgroundColor: COLORS.white, borderRadius: 23, borderWidth: 1, borderColor: COLORS.line, padding: 20, shadowColor: '#17213C', shadowOpacity: 0.05, shadowRadius: 15, shadowOffset: { width: 0, height: 7 }, elevation: 2 },
    loginCardTitle: { color: COLORS.ink, fontSize: 22, fontWeight: '900' },
    loginCardSubtitle: { color: COLORS.muted, fontSize: 12, marginTop: 5, marginBottom: 17 },
    roleToggle: { flexDirection: 'row', gap: 9, marginBottom: 14 },
    roleOption: { flex: 1, paddingVertical: 12, borderRadius: 12, borderWidth: 1, borderColor: COLORS.line, alignItems: 'center', backgroundColor: '#FAFBFE' },
    roleOptionActive: { backgroundColor: '#F0F1FF', borderColor: '#B9BFFF' },
    roleOptionText: { color: COLORS.muted, fontSize: 12, fontWeight: '800' },
    roleOptionTextActive: { color: COLORS.blue },
    fieldWrap: { marginBottom: 13 },
    fieldLabel: { color: '#34415E', fontSize: 12, fontWeight: '800', marginBottom: 7 },
    input: { width: '100%', minHeight: 47, borderRadius: 11, borderWidth: 1, borderColor: '#E0E6F0', backgroundColor: '#FBFCFF', paddingHorizontal: 12, paddingVertical: 11, color: COLORS.ink, fontSize: 13 },
    multilineInput: { minHeight: 85, textAlignVertical: 'top' },
    inputDisabled: { opacity: 0.65 },
    helperText: { color: COLORS.muted, fontSize: 10, lineHeight: 15, marginTop: 6 },
    buttonPrimary: { minHeight: 47, paddingHorizontal: 16, paddingVertical: 12, borderRadius: 12, backgroundColor: COLORS.blue, alignItems: 'center', justifyContent: 'center', marginTop: 8, marginBottom: 8 },
    buttonPrimaryText: { color: '#fff', fontSize: 13, fontWeight: '900' },
    buttonSecondary: { minHeight: 45, paddingHorizontal: 15, paddingVertical: 11, borderRadius: 12, backgroundColor: '#F2F3FF', borderWidth: 1, borderColor: '#DDE0FF', alignItems: 'center', justifyContent: 'center', marginTop: 8, marginBottom: 8 },
    buttonSecondaryText: { color: '#515FD8', fontSize: 13, fontWeight: '900' },
    buttonDanger: { minHeight: 45, paddingHorizontal: 15, paddingVertical: 11, borderRadius: 12, backgroundColor: COLORS.redBg, borderWidth: 1, borderColor: '#F1C8CE', alignItems: 'center', justifyContent: 'center', marginTop: 8, marginBottom: 8 },
    buttonDangerText: { color: COLORS.red, fontSize: 13, fontWeight: '900' },
    buttonSubtle: { minHeight: 38, paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10, backgroundColor: '#F5F7FC', borderWidth: 1, borderColor: COLORS.line, alignItems: 'center', justifyContent: 'center' },
    buttonSubtleText: { color: COLORS.ink, fontSize: 11, fontWeight: '800' },
    buttonSmall: { minHeight: 34, paddingVertical: 7, marginTop: 0, marginBottom: 0 },
    buttonDisabled: { opacity: 0.5 },
    pressed: { opacity: 0.8 },
    notice: { paddingVertical: 10, paddingHorizontal: 12, borderWidth: 1, borderRadius: 10, marginBottom: 10 },
    noticeError: { backgroundColor: COLORS.redBg, borderColor: '#F1C8CE' },
    noticeSuccess: { backgroundColor: COLORS.greenBg, borderColor: '#C7EEDC' },
    noticeWarning: { backgroundColor: COLORS.amberBg, borderColor: '#F0DFB8' },
    noticeText: { color: COLORS.ink, fontSize: 11, lineHeight: 16 },
    connectionTip: { padding: 16, marginTop: 16, borderRadius: 15, backgroundColor: '#EEF1FB' },
    connectionTipTitle: { color: COLORS.ink, fontWeight: '900', fontSize: 12, marginBottom: 5 },
    connectionTipText: { color: COLORS.muted, fontSize: 11, lineHeight: 17 },
    topBar: { backgroundColor: COLORS.white, paddingHorizontal: 16, paddingTop: Platform.OS === 'android' ? 8 : 10, paddingBottom: 12, flexDirection: 'row', alignItems: 'center', borderBottomWidth: 1, borderBottomColor: COLORS.line },
    topBrandMark: { width: 38, height: 38, borderRadius: 12, backgroundColor: COLORS.blue, alignItems: 'center', justifyContent: 'center', marginRight: 10 },
    topBrandMarkText: { color: '#FFFFFF', fontWeight: '900', fontSize: 21 },
    topBarCopy: { flex: 1 },
    topBarTitle: { color: COLORS.ink, fontSize: 16, fontWeight: '900' },
    topBarSubtitle: { color: COLORS.muted, fontSize: 10, marginTop: 2 },
    signOutButton: { borderRadius: 10, borderWidth: 1, borderColor: COLORS.line, paddingHorizontal: 11, paddingVertical: 8 },
    signOutText: { color: COLORS.ink, fontSize: 10, fontWeight: '800' },
    globalNotice: { paddingHorizontal: 15, paddingTop: 8 },
    loadingBar: { paddingHorizontal: 16, paddingVertical: 8, flexDirection: 'row', gap: 8, alignItems: 'center' },
    loadingBarText: { color: COLORS.muted, fontSize: 11 },
    mainScroll: { flex: 1 },
    mainContent: { paddingHorizontal: 16, paddingTop: 18, paddingBottom: 28 },
    studentHeroCard: { borderRadius: 24, padding: 20, backgroundColor: COLORS.navy, marginBottom: 20, overflow: 'hidden' },
    studentHeroTopRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 12 },
    studentHeroCopy: { flex: 1 },
    studentHeroOverline: { color: '#BFC8FF', fontSize: 9, letterSpacing: 1.5, fontWeight: '900', marginBottom: 10 },
    studentHeroGreeting: { color: '#D6DCF4', fontSize: 13, fontWeight: '700' },
    studentHeroName: { color: '#FFFFFF', fontSize: 28, lineHeight: 34, letterSpacing: -0.8, fontWeight: '900', marginTop: 2 },
    studentHeroAvatar: { width: 58, height: 58, borderRadius: 20, backgroundColor: '#6875F5', borderWidth: 2, borderColor: '#8D97FF', alignItems: 'center', justifyContent: 'center' },
    studentHeroAvatarText: { color: '#FFFFFF', fontSize: 25, fontWeight: '900' },
    studentHeroBody: { color: '#D6DCF4', fontSize: 11, lineHeight: 18, marginTop: 14, maxWidth: 300 },
    studentHeroRefresh: { alignSelf: 'flex-start', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10, backgroundColor: 'rgba(255,255,255,0.12)', marginTop: 15 },
    studentHeroRefreshText: { color: '#FFFFFF', fontSize: 10, fontWeight: '900' },
    studentQuickActionsRow: { flexDirection: 'row', gap: 9, marginBottom: 16 },
    studentQuickAction: { flex: 1, minWidth: 0, minHeight: 112, borderRadius: 16, borderWidth: 1, borderColor: COLORS.line, backgroundColor: COLORS.white, padding: 11 },
    studentQuickActionIcon: { width: 34, height: 34, borderRadius: 11, alignItems: 'center', justifyContent: 'center', marginBottom: 9, position: 'relative' },
    studentQuickActionIconText: { fontSize: 17 },
    studentQuickActionLabel: { color: COLORS.ink, fontSize: 11, fontWeight: '900' },
    studentQuickActionHint: { color: COLORS.muted, fontSize: 8, lineHeight: 12, marginTop: 4 },
    studentQuickActionBadge: { position: 'absolute', top: -5, right: -6, minWidth: 16, height: 16, paddingHorizontal: 3, borderRadius: 8, backgroundColor: COLORS.red, alignItems: 'center', justifyContent: 'center' },
    studentQuickActionBadgeText: { color: COLORS.white, fontSize: 8, lineHeight: 10, fontWeight: '900' },
    studentAcademicCard: { borderRadius: 18, borderWidth: 1, borderColor: COLORS.line, backgroundColor: COLORS.white, padding: 16, marginBottom: 12, shadowColor: '#1D284D', shadowOpacity: 0.025, shadowRadius: 10, shadowOffset: { width: 0, height: 4 }, elevation: 1 },
    studentAcademicHeader: { flexDirection: 'row', alignItems: 'center', gap: 11, paddingBottom: 11, marginBottom: 2, borderBottomWidth: 1, borderBottomColor: '#EEF1F7' },
    studentAcademicIcon: { width: 38, height: 38, borderRadius: 12, backgroundColor: '#EEF1FF', alignItems: 'center', justifyContent: 'center' },
    studentAcademicIconText: { fontSize: 18 },
    studentAcademicTitle: { color: COLORS.ink, fontSize: 13, fontWeight: '900' },
    studentAcademicSubtitle: { color: COLORS.muted, fontSize: 10, marginTop: 3 },
    welcomeCard: { borderRadius: 22, padding: 22, backgroundColor: COLORS.navy, marginBottom: 21 },
    welcomeOverline: { color: '#BFC8FF', fontSize: 10, letterSpacing: 1.4, fontWeight: '900', marginBottom: 10 },
    welcomeTitle: { color: '#FFFFFF', fontSize: 27, letterSpacing: -0.7, fontWeight: '900', marginBottom: 8 },
    welcomeBody: { color: '#D6DCF4', fontSize: 12, lineHeight: 19 },
    sectionTitleRow: { flexDirection: 'row', alignItems: 'center', marginTop: 10, marginBottom: 13, gap: 12 },
    sectionTitleCopy: { flex: 1 },
    sectionTitle: { color: COLORS.ink, fontSize: 19, fontWeight: '900', letterSpacing: -0.3 },
    sectionSubtitle: { color: COLORS.muted, fontSize: 11, lineHeight: 16, marginTop: 4 },
    refreshButton: { paddingHorizontal: 10, paddingVertical: 8, borderRadius: 9, backgroundColor: '#F0F1FF' },
    refreshText: { color: COLORS.blue, fontSize: 10, fontWeight: '900' },
    card: { borderRadius: 18, borderWidth: 1, borderColor: COLORS.line, backgroundColor: COLORS.white, padding: 16, marginBottom: 13, shadowColor: '#1D284D', shadowOpacity: 0.025, shadowRadius: 10, shadowOffset: { width: 0, height: 4 }, elevation: 1 },
    cardTopRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 10, marginBottom: 11 },
    flexOne: { flex: 1, minWidth: 0 },
    cardTitle: { color: COLORS.ink, fontSize: 15, lineHeight: 21, fontWeight: '900' },
    cardSubtitle: { color: COLORS.muted, fontSize: 11, marginTop: 4 },
    cardDescription: { color: COLORS.muted, fontSize: 11, lineHeight: 17, marginBottom: 12 },
    statusPill: { borderRadius: 999, paddingHorizontal: 9, paddingVertical: 6 },
    statusAvailable: { backgroundColor: COLORS.greenBg },
    statusUpcoming: { backgroundColor: COLORS.amberBg },
    statusText: { fontSize: 9, fontWeight: '900' },
    statusAvailableText: { color: COLORS.green },
    statusUpcomingText: { color: COLORS.amber },
    metaGrid: { flexDirection: 'row', flexWrap: 'wrap', borderTopWidth: 1, borderTopColor: '#EEF1F7', paddingTop: 12, marginTop: 4 },
    metaCell: { width: '50%', paddingVertical: 5, paddingRight: 8 },
    metaLabel: { color: '#8C98AD', fontSize: 10, marginBottom: 4 },
    metaValue: { color: '#33405E', fontSize: 11, fontWeight: '800' },
    dateCaption: { color: '#8B97AC', fontSize: 10, marginTop: 12, lineHeight: 15 },
    statsRow: { flexDirection: 'row', gap: 9, marginBottom: 17 },
    statCard: { flex: 1, minWidth: 0, borderWidth: 1, borderColor: COLORS.line, backgroundColor: COLORS.white, borderRadius: 15, padding: 12, marginBottom: 2 },
    statSymbol: { width: 29, height: 29, borderRadius: 9, alignItems: 'center', justifyContent: 'center', marginBottom: 10 },
    statSymbolText: { fontSize: 16, fontWeight: '900' },
    statValue: { color: COLORS.ink, fontSize: 18, fontWeight: '900' },
    statLabel: { color: COLORS.muted, fontSize: 9, lineHeight: 13, marginTop: 5 },
    profileRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', gap: 12, paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#EEF1F7' },
    profileRowLast: { borderBottomWidth: 0 },
    profileLabel: { color: COLORS.muted, fontSize: 11, flex: 0.8 },
    profileValue: { color: COLORS.ink, fontSize: 11, fontWeight: '800', flex: 1.2, textAlign: 'right' },
    adminStatsGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', marginBottom: 14 },
    adminStatCard: { width: '48.5%', minHeight: 126, borderRadius: 16, borderWidth: 1, borderColor: COLORS.line, backgroundColor: COLORS.white, padding: 13, marginBottom: 10 },
    adminStatManage: { color: COLORS.blue, fontSize: 9, fontWeight: '900', marginTop: 7 },
    adminManageGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' },
    adminModuleCard: { width: '48.5%', minHeight: 135, borderRadius: 16, borderWidth: 1, borderColor: COLORS.line, backgroundColor: COLORS.white, padding: 13, marginBottom: 11 },
    adminModuleTitle: { color: COLORS.ink, fontSize: 13, fontWeight: '900', marginTop: 2 },
    adminModuleCount: { color: COLORS.muted, fontSize: 10, marginTop: 4 },
    adminModuleAction: { color: COLORS.blue, fontSize: 10, fontWeight: '900', marginTop: 9 },
    adminBackAction: { alignSelf: 'flex-start', paddingVertical: 7, paddingHorizontal: 2, marginBottom: 5 },
    adminBackText: { color: COLORS.blue, fontSize: 12, fontWeight: '900' },
    adminToolbarRow: { flexDirection: 'row', gap: 9, marginBottom: 12 },
    adminToolbarButton: { minHeight: 42, paddingHorizontal: 14, paddingVertical: 11, borderRadius: 11, alignItems: 'center', justifyContent: 'center' },
    adminSearchButton: { backgroundColor: '#EEF1FB', borderWidth: 1, borderColor: '#DCE3F2' },
    adminAddButton: { flex: 1, backgroundColor: COLORS.blue },
    adminToolbarButtonText: { color: COLORS.ink, fontSize: 11, fontWeight: '900' },
    adminRecordActions: { flexDirection: 'row', gap: 9, borderTopWidth: 1, borderTopColor: '#EEF1F7', paddingTop: 11, marginTop: 3 },
    adminRecordButton: { flex: 1, minHeight: 36, alignItems: 'center', justifyContent: 'center', borderRadius: 9, borderWidth: 1 },
    adminEditButton: { backgroundColor: '#F0F1FF', borderColor: '#DDE0FF' },
    adminDeleteButton: { backgroundColor: COLORS.redBg, borderColor: '#F1C8CE' },
    adminEditButtonText: { color: COLORS.blue, fontSize: 11, fontWeight: '900' },
    adminDeleteButtonText: { color: COLORS.red, fontSize: 11, fontWeight: '900' },
    adminListFootnote: { color: COLORS.muted, fontSize: 10, textAlign: 'center', marginVertical: 8 },
    materialModeRow: { flexDirection: 'row', gap: 8, marginTop: 14, marginBottom: 15 },
    materialModeOption: { flex: 1, minHeight: 40, alignItems: 'center', justifyContent: 'center', borderRadius: 10, borderWidth: 1, borderColor: COLORS.line, backgroundColor: '#FAFBFE' },
    materialModeOptionActive: { borderColor: '#B9BFFF', backgroundColor: '#F0F1FF' },
    materialModeText: { color: COLORS.muted, fontSize: 11, fontWeight: '800' },
    materialModeTextActive: { color: COLORS.blue },
    materialChooseFile: { borderWidth: 1, borderStyle: 'dashed', borderColor: '#B9BFFF', backgroundColor: '#F7F8FF', borderRadius: 13, padding: 15, marginBottom: 8 },
    materialChooseFileTitle: { color: COLORS.blue, fontSize: 12, fontWeight: '900' },
    materialChooseFileHint: { color: COLORS.muted, fontSize: 10, lineHeight: 15, marginTop: 5 },
    materialSelectedFile: { color: COLORS.green, fontSize: 10, fontWeight: '800', lineHeight: 15, marginBottom: 9 },
    materialTypePill: { backgroundColor: '#EEF1FF', borderRadius: 9, paddingHorizontal: 9, paddingVertical: 6 },
    materialTypePillText: { color: COLORS.blue, fontSize: 9, fontWeight: '900' },
    materialMetaText: { color: COLORS.muted, fontSize: 10, marginBottom: 10 },
    materialActions: { flexDirection: 'row', gap: 9, marginTop: 7 },
    materialOpenButton: { flex: 1, minHeight: 40, alignItems: 'center', justifyContent: 'center', borderRadius: 10, backgroundColor: '#F0F1FF', borderWidth: 1, borderColor: '#DDE0FF' },
    materialOpenButtonText: { color: COLORS.blue, fontSize: 11, fontWeight: '900' },
    materialDeleteButton: { minWidth: 82, minHeight: 40, alignItems: 'center', justifyContent: 'center', borderRadius: 10, backgroundColor: COLORS.redBg, borderWidth: 1, borderColor: '#F1C8CE' },
    materialDeleteButtonText: { color: COLORS.red, fontSize: 11, fontWeight: '900' },
    adminLoadingCard: { minHeight: 100, justifyContent: 'center', alignItems: 'center', gap: 10, backgroundColor: COLORS.white, borderRadius: 16, borderWidth: 1, borderColor: COLORS.line, marginBottom: 12 },
    adminEditorRoot: { flex: 1, backgroundColor: COLORS.background, paddingTop: Platform.OS === 'android' ? (StatusBar.currentHeight || 0) : 0 },
    adminEditorHeader: { minHeight: 70, paddingHorizontal: 18, paddingVertical: 12, backgroundColor: COLORS.white, borderBottomWidth: 1, borderBottomColor: COLORS.line, flexDirection: 'row', alignItems: 'center', gap: 12 },
    adminEditorTitle: { color: COLORS.ink, fontSize: 18, fontWeight: '900' },
    adminCloseButton: { width: 38, height: 38, borderRadius: 12, backgroundColor: '#F0F1F7', alignItems: 'center', justifyContent: 'center' },
    adminCloseButtonText: { color: COLORS.ink, fontSize: 24, lineHeight: 27, fontWeight: '700' },
    adminEditorContent: { padding: 18, paddingBottom: 40 },
    adminStatSymbol: { width: 31, height: 31, borderRadius: 10, alignItems: 'center', justifyContent: 'center', marginBottom: 9 },
    adminStatSymbolText: { fontSize: 15, fontWeight: '900' },
    adminStatValue: { color: COLORS.ink, fontSize: 21, fontWeight: '900' },
    adminStatLabel: { color: COLORS.muted, fontSize: 10, fontWeight: '700', marginTop: 3 },
    adminInfoCard: { backgroundColor: '#EEF1FB', borderRadius: 16, padding: 16, marginTop: 4, marginBottom: 12 },
    adminInfoTitle: { color: COLORS.ink, fontSize: 13, fontWeight: '900', marginBottom: 6 },
    adminInfoBody: { color: COLORS.muted, fontSize: 11, lineHeight: 17 },
    scorePill: { paddingVertical: 7, paddingHorizontal: 9, borderRadius: 10 },
    scoreGood: { backgroundColor: COLORS.greenBg },
    scoreLow: { backgroundColor: COLORS.redBg },
    scorePillText: { fontSize: 11, fontWeight: '900' },
    scoreGoodText: { color: COLORS.green },
    scoreLowText: { color: COLORS.red },
    resultMarksRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 9, borderTopWidth: 1, borderTopColor: '#EEF1F7', paddingTop: 12 },
    resultMarks: { color: COLORS.ink, fontSize: 18, fontWeight: '900', marginTop: 3 },
    emptyState: { borderRadius: 18, borderWidth: 1, borderColor: COLORS.line, padding: 24, alignItems: 'flex-start', backgroundColor: COLORS.white, marginBottom: 14 },
    emptyIcon: { width: 40, height: 40, borderRadius: 13, backgroundColor: '#F0F1FF', alignItems: 'center', justifyContent: 'center', marginBottom: 12 },
    emptyIconText: { color: COLORS.blue, fontSize: 19, fontWeight: '900' },
    emptyTitle: { color: COLORS.ink, fontSize: 15, fontWeight: '900', marginBottom: 6 },
    emptyBody: { color: COLORS.muted, fontSize: 11, lineHeight: 17 },
    bottomNav: {
        minHeight: 74,
        borderTopWidth: 1,
        borderTopColor: '#E8ECF5',
        backgroundColor: COLORS.white,
        flexDirection: 'row',
        justifyContent: 'space-around',
        alignItems: 'center',
        paddingHorizontal: 5,
        paddingTop: 7,
        paddingBottom: Platform.OS === 'ios' ? 16 : 7,
        elevation: 12,
        shadowColor: '#172554',
        shadowOffset: { width: 0, height: -4 },
        shadowOpacity: 0.06,
        shadowRadius: 10,
    },
    navItem: {
        flex: 1,
        minWidth: 0,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 4,
        paddingVertical: 2,
    },
    navIconWrap: {
        width: 35,
        height: 32,
        borderRadius: 11,
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: 'transparent',
    },
    navIconWrapActive: {
        backgroundColor: COLORS.blue,
        shadowColor: COLORS.blue,
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.22,
        shadowRadius: 5,
        elevation: 3,
    },
    navIcon: {
        fontSize: 18,
        color: '#8A95A9',
        fontWeight: '800',
        textAlign: 'center',
    },
    navIconActive: { color: COLORS.white },
    navBadge: {
        position: 'absolute',
        top: -4,
        right: -3,
        minWidth: 16,
        height: 16,
        paddingHorizontal: 3,
        borderRadius: 8,
        backgroundColor: COLORS.red,
        borderWidth: 1.5,
        borderColor: COLORS.white,
        alignItems: 'center',
        justifyContent: 'center',
    },
    navBadgeText: { color: COLORS.white, fontSize: 8, lineHeight: 10, fontWeight: '900' },
    navLabel: { color: '#8A95A9', fontSize: 9, fontWeight: '700' },
    navLabelActive: { color: '#4657E8', fontWeight: '900' },
    twoFields: { flexDirection: 'row', gap: 10 },
    halfField: { flex: 1 },
    removeText: { color: COLORS.red, fontWeight: '800', fontSize: 11 },
    optionRow: { flexDirection: 'row', gap: 8, marginBottom: 13 },
    correctOption: { flex: 1, minHeight: 38, alignItems: 'center', justifyContent: 'center', borderRadius: 10, borderWidth: 1, borderColor: COLORS.line, backgroundColor: '#FAFBFE' },
    correctOptionSelected: { backgroundColor: COLORS.greenBg, borderColor: '#A9E4C9' },
    correctOptionText: { color: COLORS.muted, fontWeight: '900', fontSize: 12 },
    correctOptionTextSelected: { color: COLORS.green },
    formNote: { color: COLORS.muted, fontSize: 10, lineHeight: 16, marginTop: 10, marginBottom: 8 },
    backButton: { alignSelf: 'flex-start', paddingVertical: 8, paddingHorizontal: 11, borderWidth: 1, borderColor: COLORS.line, borderRadius: 10, backgroundColor: COLORS.white, marginBottom: 13 },
    backButtonText: { color: COLORS.ink, fontWeight: '800', fontSize: 11 },
    quizHeaderCard: { borderRadius: 20, borderWidth: 1, borderColor: COLORS.line, padding: 18, backgroundColor: COLORS.white, marginBottom: 13 },
    quizHeaderTitle: { color: COLORS.ink, fontWeight: '900', fontSize: 23, lineHeight: 29, marginBottom: 7 },
    timerPill: { marginTop: 15, borderRadius: 15, padding: 13, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
    timerNormal: { backgroundColor: '#EFF1FF', borderWidth: 1, borderColor: '#DDE0FF' },
    timerWarning: { backgroundColor: COLORS.amberBg, borderWidth: 1, borderColor: '#F0DFB8' },
    timerExpired: { backgroundColor: COLORS.redBg, borderWidth: 1, borderColor: '#F1C8CE' },
    timerLabel: { fontSize: 9, fontWeight: '900', letterSpacing: 0.8 },
    timerValue: { fontSize: 24, fontWeight: '900', fontVariant: ['tabular-nums'] },
    timerNormalText: { color: COLORS.blue },
    timerWarningText: { color: COLORS.amber },
    timerExpiredText: { color: COLORS.red },
    timerHint: { color: COLORS.muted, fontSize: 10, lineHeight: 15, marginTop: 10 },
    questionTopRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    questionNumber: { color: COLORS.blue, fontSize: 10, fontWeight: '900', letterSpacing: 0.7 },
    questionMarks: { color: '#929DB2', fontSize: 10 },
    questionText: { color: COLORS.ink, fontSize: 14, fontWeight: '800', lineHeight: 21, marginBottom: 14 },
    answerOption: { minHeight: 48, padding: 10, borderWidth: 1, borderColor: '#E3E8F2', borderRadius: 12, backgroundColor: COLORS.white, marginBottom: 8, flexDirection: 'row', alignItems: 'center', gap: 10 },
    answerOptionChosen: { borderColor: '#777FF0', backgroundColor: '#F2F3FF' },
    answerOptionDisabled: { opacity: 0.75 },
    answerLetter: { width: 27, height: 27, borderRadius: 8, backgroundColor: '#EFF0FF', alignItems: 'center', justifyContent: 'center' },
    answerLetterChosen: { backgroundColor: COLORS.blue },
    answerLetterText: { color: '#5D66DF', fontSize: 11, fontWeight: '900' },
    answerLetterTextChosen: { color: '#FFFFFF' },
    answerOptionText: { flex: 1, color: '#4D5A73', fontSize: 12, lineHeight: 17 },
    answerOptionTextChosen: { color: COLORS.ink, fontWeight: '700' },
    answerCheck: { color: COLORS.blue, fontWeight: '900', fontSize: 15 },
    answersCount: { color: COLORS.muted, fontSize: 11, textAlign: 'center', marginTop: 4, marginBottom: 8 },
    resultSuccessIcon: { width: 52, height: 52, borderRadius: 17, backgroundColor: COLORS.greenBg, alignItems: 'center', justifyContent: 'center', marginBottom: 15 },
    resultSuccessIconText: { color: COLORS.green, fontSize: 27, fontWeight: '900' },
    resultTitle: { color: COLORS.ink, fontSize: 21, fontWeight: '900', marginBottom: 8 },
    resultMessage: { color: COLORS.muted, fontSize: 12, lineHeight: 18 },
    resultBigScore: { backgroundColor: '#F4F6FF', borderRadius: 16, padding: 17, alignItems: 'center', marginTop: 20, marginBottom: 14 },
    resultBigScoreLabel: { color: COLORS.muted, fontSize: 9, fontWeight: '900', letterSpacing: 1.1 },
    resultBigScoreValue: { color: COLORS.blue, fontWeight: '900', fontSize: 31, marginTop: 5 },
    dropdownInput: { width: '100%', minHeight: 47, borderRadius: 11, borderWidth: 1, borderColor: '#E0E6F0', backgroundColor: '#FBFCFF', paddingHorizontal: 12, paddingVertical: 11, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
    dropdownValue: { color: COLORS.ink, fontSize: 13, flex: 1, paddingRight: 10 },
    dropdownPlaceholder: { color: '#9AA5B8' },
    dropdownChevron: { color: COLORS.muted, fontSize: 19, fontWeight: '800' },
    dropdownModalOverlay: { flex: 1, backgroundColor: 'rgba(18, 28, 54, 0.45)', justifyContent: 'center', alignItems: 'center', padding: 22 },
    dropdownModalCard: { width: '100%', maxHeight: '78%', borderRadius: 20, backgroundColor: COLORS.white, padding: 17, borderWidth: 1, borderColor: COLORS.line },
    dropdownModalHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 },
    dropdownModalTitle: { color: COLORS.ink, fontSize: 16, fontWeight: '900', flex: 1 },
    dropdownModalCloseIcon: { color: COLORS.muted, fontSize: 28, lineHeight: 30, paddingHorizontal: 5 },
    dropdownOptionsScroll: { flexGrow: 0, maxHeight: 360 },
    dropdownOption: { minHeight: 46, borderBottomWidth: 1, borderBottomColor: '#EEF1F7', paddingVertical: 11, paddingHorizontal: 10, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
    dropdownOptionSelected: { backgroundColor: '#F0F1FF', borderRadius: 9 },
    dropdownOptionText: { color: COLORS.ink, fontSize: 13, flex: 1 },
    dropdownOptionTextSelected: { color: COLORS.blue, fontWeight: '900' },
    dropdownOptionCheck: { color: COLORS.blue, fontWeight: '900', fontSize: 16, marginLeft: 8 },
    dropdownEmptyText: { color: COLORS.muted, fontSize: 12, lineHeight: 18, paddingVertical: 18 },
    formSectionTitle: { color: COLORS.ink, fontSize: 16, fontWeight: '900', marginBottom: 16 },
    questionCountBadge: { color: COLORS.blue, backgroundColor: '#F0F1FF', overflow: 'hidden', borderRadius: 999, paddingHorizontal: 10, paddingVertical: 6, fontSize: 10, fontWeight: '900' },
});
