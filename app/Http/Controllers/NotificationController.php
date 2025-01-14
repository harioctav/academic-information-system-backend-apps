<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
  public function index(Request $request)
  {
    $query = $request->user()->notifications();

    if ($request->has('status') && $request->status === 'unread') {
      $query = $request->user()->unreadNotifications();
    }

    $notifications = $query->latest()
      ->paginate($request->input('per_page', 5));

    return NotificationResource::collection($notifications)
      ->additional([
        'meta' => [
          'unread_count' => $request->user()->unreadNotifications()->count()
        ]
      ]);
  }

  public function markAsRead(Request $request, $id)
  {
    $notification = $request->user()->notifications()->findOrFail($id);
    $notification->markAsRead();

    return response()->json([
      'status' => true,
      'message' => 'Notifikasi telah ditandai sebagai dibaca'
    ]);
  }

  public function destroy(Request $request, $id)
  {
    $notification = $request->user()->notifications()->findOrFail($id);
    $notification->delete();

    return response()->json([
      'status' => true,
      'message' => 'Notifikasi berhasil dihapus'
    ]);
  }
}
