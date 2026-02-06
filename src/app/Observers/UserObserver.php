<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * ユーザーモデルの変更を監視するオブザーバー
 * 特に管理者権限（is_admin）の変更を監査ログに記録する
 */
class UserObserver
{
    /**
     * ユーザー更新時に管理者権限の変更を検知してログに記録
     */
    public function updating(User $user): void
    {
        if ($user->isDirty('is_admin')) {
            $oldValue = $user->getOriginal('is_admin');
            $newValue = $user->is_admin;
            $changedBy = Auth::user();

            Log::channel('security')->warning('管理者権限の変更を検知', [
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
                'old_is_admin' => $oldValue,
                'new_is_admin' => $newValue,
                'changed_by_user_id' => $changedBy?->id,
                'changed_by_email' => $changedBy?->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }

    /**
     * ユーザー作成時に管理者として作成された場合のログ記録
     */
    public function created(User $user): void
    {
        if ($user->is_admin) {
            $createdBy = Auth::user();

            Log::channel('security')->warning('管理者ユーザーが新規作成されました', [
                'new_user_id' => $user->id,
                'new_user_email' => $user->email,
                'created_by_user_id' => $createdBy?->id,
                'created_by_email' => $createdBy?->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }

    /**
     * ユーザー削除時のログ記録
     */
    public function deleted(User $user): void
    {
        $deletedBy = Auth::user();

        Log::channel('security')->warning('ユーザーが削除されました', [
            'deleted_user_id' => $user->id,
            'deleted_user_email' => $user->email,
            'was_admin' => $user->is_admin,
            'deleted_by_user_id' => $deletedBy?->id,
            'deleted_by_email' => $deletedBy?->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
