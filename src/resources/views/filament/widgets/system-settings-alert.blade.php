<div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm text-amber-900">
            通知設定が未完了です。管理者通知を有効にするには設定を完了してください。
        </div>
        <x-filament::button tag="a" :href="$settingsUrl" color="warning">
            通知設定を開く
        </x-filament::button>
    </div>
</div>
