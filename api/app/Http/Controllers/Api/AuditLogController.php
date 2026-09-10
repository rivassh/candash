<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with('user:id,name')
            ->when($request->model_type, fn($q, $t) => $q->where('model_type', $t))
            ->when($request->user_id, fn($q, $u) => $q->where('user_id', $u))
            ->orderByDesc('created_at');

        return response()->json($query->paginate(30));
    }
}