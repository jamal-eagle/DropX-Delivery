<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{

    public function getCustomerNotifications()
    {
        $user = auth()->user();


        if ($user->user_type !== 'customer') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id'         => $notification->id,
                    'title'      => $notification->title ?? '',
                    'body'       => $notification->body ?? '',
                    'created_at' => $notification->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
        ], 200);
    }

    public function getdriverNotifications()
    {
        $user = auth()->user();


        if ($user->user_type !== 'driver') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id'         => $notification->id,
                    'title'      => $notification->title ?? '',
                    'body'       => $notification->body ?? '',
                    'created_at' => $notification->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
        ], 200);
    }

    public function getresturantNotifications()
    {
        $user = auth()->user();


        if ($user->user_type !== 'restaurant') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id'         => $notification->id,
                    'title'      => $notification->title ?? '',
                    'body'       => $notification->body ?? '',
                    'created_at' => $notification->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
        ], 200);
    }
}
