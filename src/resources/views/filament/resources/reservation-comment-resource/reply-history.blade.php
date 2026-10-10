<div class="max-h-64 space-y-3 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3">
    @forelse($comments as $comment)
        @php($isAdminMessage = $comment->sender_type === \App\Models\ReservationComment::SENDER_ADMIN)
        <div class="rounded-lg p-3 {{ $isAdminMessage ? 'bg-amber-50' : 'bg-sky-50' }}">
            <div class="flex justify-between gap-3 text-xs text-gray-500">
                <span class="font-semibold text-gray-700">{{ $isAdminMessage ? '管理者' : 'お客様' }}</span>
                <time>{{ $comment->created_at->timezone('Asia/Tokyo')->format('Y/m/d H:i') }}</time>
            </div>
            @if($comment->hidden_by_user_at)
                <span class="mt-1 inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-700">お客様が削除済み（{{ $comment->hidden_by_user_at->timezone('Asia/Tokyo')->format('Y/m/d H:i') }}）</span>
            @endif
            @if($comment->deleted_by_admin_at)
                <p class="mt-1 text-sm italic text-gray-400">削除済みのメッセージ（お客様には表示されません）</p>
            @elseif($isAdminMessage)
                <button type="button" wire:click="deleteAdminComment({{ $comment->id }})" wire:confirm="このメッセージを削除しますか？お客様側には表示されなくなります。" class="mt-1 block w-full text-left" title="タップして削除">
                    <span class="block whitespace-pre-wrap break-words text-sm text-gray-800">{{ $comment->body }}</span>
                    <span class="mt-1 block text-xs text-rose-500">タップして削除</span>
                </button>
            @else
                <p class="mt-1 whitespace-pre-wrap break-words text-sm text-gray-800">{{ $comment->body }}</p>
            @endif
        </div>
    @empty
        <p class="text-sm text-gray-500">メッセージはありません。</p>
    @endforelse
</div>
