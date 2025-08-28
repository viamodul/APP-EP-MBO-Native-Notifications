<?php

namespace App\Http\Controllers;

use App\Models\WebhookLog;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WebhookLog::with('shop');
        
        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('order_id')) {
            $query->where('order_id', 'like', '%' . $request->order_id . '%');
        }
        
        $webhookLogs = $query->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return response()->json($webhookLogs);
    }

    public function retry(WebhookLog $webhookLog): JsonResponse
    {
        if ($webhookLog->status === 'sent') {
            return response()->json([
                'message' => 'Webhook already sent successfully',
            ], 400);
        }
        
        $webhookService = new WebhookService();
        $webhookService->retryFailedWebhook($webhookLog);
        
        return response()->json([
            'message' => 'Webhook retry initiated',
            'webhook_log_id' => $webhookLog->id,
        ]);
    }
}
