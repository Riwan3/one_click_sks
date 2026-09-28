<?php

namespace App\Services;

use App\Models\BestEmployeeActivityLog;
use Illuminate\Http\Request;

class BestEmployeeActivityLogger
{
    public function log(int $campaignId, ?int $userId, string $actionType, string $description = '', ?array $metadata = null, ?int $attemptId = null): void
    {
        $request = request();

        BestEmployeeActivityLog::create([
            'campaign_id' => $campaignId,
            'attempt_id' => $attemptId,
            'user_id' => $userId,
            'action_type' => mb_substr($actionType, 0, 50),
            'description' => mb_substr($description, 0, 500),
            'metadata' => $metadata,
            'ip_address' => $request instanceof Request ? $request->ip() : null,
            'user_agent' => $request instanceof Request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
        ]);
    }

    public function timeline(int $campaignId, ?int $attemptId = null, int $limit = 200)
    {
        return BestEmployeeActivityLog::with('user:id,name,username')
            ->where('campaign_id', $campaignId)
            ->when($attemptId !== null, fn ($q) => $q->where('attempt_id', $attemptId))
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
