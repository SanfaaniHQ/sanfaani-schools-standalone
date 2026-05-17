<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function feed(Request $request)
    {
        if (! Schema::hasTable('notifications')) {
            return response()->json([
                'unread_count' => 0,
                'notifications' => [],
            ]);
        }

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data ?? [];

                return [
                    'id' => $notification->id,
                    'title' => data_get($data, 'title', class_basename($notification->type)),
                    'body' => data_get($data, 'body'),
                    'action_url' => data_get($data, 'action_url') ?: route('notifications.index'),
                    'read' => filled($notification->read_at),
                    'created_at' => $notification->created_at?->diffForHumans(),
                ];
            })
            ->values();

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function index(Request $request)
    {
        $notifications = Schema::hasTable('notifications')
            ? $request->user()
                ->notifications()
                ->latest()
                ->paginate(20)
                ->withQueryString()
            : new LengthAwarePaginator([], 0, 20);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => Schema::hasTable('notifications') ? $request->user()->unreadNotifications()->count() : 0,
        ]);
    }

    public function markRead(Request $request, string $notification)
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $notification->markAsRead();

        $redirect = $this->safeRedirect($request);

        return $redirect
            ? redirect()->to($redirect)
            : back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    private function safeRedirect(Request $request): ?string
    {
        $redirect = trim((string) $request->input('redirect'));

        if ($redirect === '') {
            return null;
        }

        if (str_starts_with($redirect, '/')) {
            return $redirect;
        }

        if (! filter_var($redirect, FILTER_VALIDATE_URL)) {
            return null;
        }

        return parse_url($redirect, PHP_URL_HOST) === $request->getHost()
            ? $redirect
            : null;
    }
}
