<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private Notification $notification
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $userCreatedAt = $request->user()->created_at;

        $notifications = $this->notification
            ->active()
            ->where('created_at', '>', $userCreatedAt)
            ->get();

        return response()->json($notifications, 200);
    }
}
