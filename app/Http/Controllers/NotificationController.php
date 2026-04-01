<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Marquer une notification spécifique comme lue.
     */
    public function marquerCommeLue(Request $request, $id): JsonResponse
    {
        $notification = $request->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function marquerToutCommeLu(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}
