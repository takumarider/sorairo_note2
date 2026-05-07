<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DiscordService
{
    /**
     * Discord Webhook へ通知を送信する。
     * 本番環境のみ送信し、失敗しても呼び出し元処理は継続する。
     */
    public function send(string $message, array $context = []): void
    {
        if (! app()->environment('production')) {
            return;
        }

        $webhook = (string) config('services.discord.webhook');
        if (! filled($webhook)) {
            return;
        }

        try {
            Http::timeout(5)
                ->asJson()
                ->post($webhook, ['content' => $message])
                ->throw();
        } catch (Throwable $e) {
            Log::warning('Discord通知の送信に失敗しました', array_merge($context, [
                'error' => $e->getMessage(),
            ]));
        }
    }
}
