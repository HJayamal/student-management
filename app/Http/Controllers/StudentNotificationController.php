<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentNotificationController extends Controller
{
    /**
     * Get notifications belonging to the authenticated student.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        if (!$student instanceof Student) {
            return response()->json([
                'message' => 'Only students can access notifications.',
            ], 403);
        }

        $notifications = StudentNotification::query()
            ->where('student_id', $student->id)
            ->with('quiz:id,title,subject')
            ->latest()
            ->paginate(20);

        $unreadCount = StudentNotification::query()
            ->where('student_id', $student->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'message' => 'Notifications retrieved successfully.',
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Get the unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $student = $request->user();

        if (!$student instanceof Student) {
            return response()->json([
                'message' => 'Only students can access notifications.',
            ], 403);
        }

        $count = StudentNotification::query()
            ->where('student_id', $student->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    /**
     * Mark one notification as read.
     */
    public function markRead(
        Request $request,
        int $id
    ): JsonResponse {
        $student = $request->user();

        if (!$student instanceof Student) {
            return response()->json([
                'message' => 'Only students can update notifications.',
            ], 403);
        }

        $notification = StudentNotification::query()
            ->where('student_id', $student->id)
            ->findOrFail($id);

        if ($notification->read_at === null) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $notification,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $student = $request->user();

        if (!$student instanceof Student) {
            return response()->json([
                'message' => 'Only students can update notifications.',
            ], 403);
        }

        $updatedCount = StudentNotification::query()
            ->where('student_id', $student->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'updated_count' => $updatedCount,
        ]);
    }
}
