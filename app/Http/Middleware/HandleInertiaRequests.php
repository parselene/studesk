<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user,
            ],

            'notifications' => fn () => $user
                ? [
                    'unread_count' => $user->unreadNotifications()->count(),

                    'recent' => $user->notifications()
                        ->latest()
                        ->take(10)
                        ->get()
                        ->map(fn ($notification) => [
                            'id' => $notification->id,
                            'data' => $notification->data,
                            'read_at' => $notification->read_at,
                            'created_at' => $notification->created_at,
                        ]),
                ]
                : [
                    'unread_count' => 0,
                    'recent' => [],
                ],
        ];
    }
}
