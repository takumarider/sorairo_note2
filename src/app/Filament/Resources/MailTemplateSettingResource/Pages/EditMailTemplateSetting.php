<?php

namespace App\Filament\Resources\MailTemplateSettingResource\Pages;

use App\Filament\Resources\MailTemplateSettingResource;
use App\Models\SystemSetting;
use Filament\Resources\Pages\EditRecord;

class EditMailTemplateSetting extends EditRecord
{
    protected static string $resource = MailTemplateSettingResource::class;

    public function mount(int|string|null $record = null): void
    {
        $singleton = SystemSetting::getSingleton();

        parent::mount((string) $singleton->getKey());
        $this->previousUrl = url()->previous();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('apply-default-templates')
                ->label('お任せテンプレート')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->action(function () {
                    SystemSetting::getSingleton()->update([
                        // 一般メニュー用テンプレート
                        'mail_user_confirmed_subject' => '【Sorairo Note】予約確定のお知らせ',
                        'mail_user_confirmed_body' => "ご予約ありがとうございます。\n\nお客様のご予約内容は以下の通りです。\n\n**予約日時**: {{reservation_date}}\n**メニュー**: {{menu_name}}\n**お客様名**: {{user_name}} 様\n\nご不明な点やご予約の変更が必要な場合は、お気軽にお問い合わせください。\n\n---\nSorairo Note",
                        'mail_user_canceled_subject' => '【Sorairo Note】予約キャンセルのお知らせ',
                        'mail_user_canceled_body' => "いつもご利用ありがとうございます。\n\nご予約のキャンセルが完了いたしました。\n\n**キャンセル対象**: {{reservation_date}} の {{menu_name}}\n**お客様名**: {{user_name}} 様\n\nまたのご利用をお待ちしております。\n\n---\nSorairo Note",
                        'mail_admin_confirmed_subject' => '新規予約が入りました',
                        'mail_admin_confirmed_body' => "新しい予約が入りました。\n\n**予約日時**: {{reservation_date}}\n**お客様名**: {{user_name}}\n**メニュー**: {{menu_name}}\n**連絡先**: {{user_phone}}\n\n管理画面から確認してください。\n\n---\nSorairo Note",
                        'mail_admin_canceled_subject' => '予約キャンセルの通知',
                        'mail_admin_canceled_body' => "予約がキャンセルされました。\n\n**キャンセル対象**: {{reservation_date}} の {{menu_name}}\n**お客様名**: {{user_name}}\n\n管理画面から詳細を確認してください。\n\n---\nSorairo Note",
                        // イベント用テンプレート
                        'mail_event_user_confirmed_subject' => '【Sorairo Note】イベント参加予約確定のお知らせ',
                        'mail_event_user_confirmed_body' => "イベント参加のご予約ありがとうございます。\n\nお客様のご予約内容は以下の通りです。\n\n**イベント名**: {{menu_name}}\n**開催日時**: {{reservation_date}} {{reservation_start_time}} - {{reservation_end_time}}\n**お客様名**: {{user_name}} 様\n\nマイページよりご予約状況の確認ができます。\nご不明な点がございましたら、お気軽にお問い合わせください。\n\n---\nSorairo Note",
                        'mail_event_user_canceled_subject' => '【Sorairo Note】イベント参加予約のキャンセル',
                        'mail_event_user_canceled_body' => "いつもご利用ありがとうございます。\n\nイベント参加のキャンセルが完了いたしました。\n\n**キャンセル対象**: {{reservation_date}} の {{menu_name}}\n**お客様名**: {{user_name}} 様\n\n次回のイベントのご参加をお待ちしております。\n\n---\nSorairo Note",
                        'mail_event_admin_confirmed_subject' => 'イベント参加予約が入りました',
                        'mail_event_admin_confirmed_body' => "イベント参加予約が入りました。\n\n**イベント名**: {{menu_name}}\n**開催日時**: {{reservation_date}} {{reservation_start_time}} - {{reservation_end_time}}\n**参加者名**: {{user_name}}\n**連絡先**: {{user_email}}\n\n管理画面から確認してください。\n\n---\nSorairo Note",
                        'mail_event_admin_canceled_subject' => 'イベント参加予約がキャンセルされました',
                        'mail_event_admin_canceled_body' => "イベント参加予約がキャンセルされました。\n\n**キャンセル対象**: {{reservation_date}} の {{menu_name}}\n**参加者名**: {{user_name}}\n\n管理画面から詳細を確認してください。\n\n---\nSorairo Note",
                    ]);
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('テンプレートを適用しました')
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
