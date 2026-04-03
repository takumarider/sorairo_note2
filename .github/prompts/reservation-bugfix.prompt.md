---
description: "Investigate and fix reservation flow bugs with reproducible steps, root-cause analysis, patch, and verification commands."
name: "Reservation Bugfix"
argument-hint: "症状・再現手順・期待動作・対象画面を入力"
agent: "agent"
---

予約フローの不具合を修正してください。以下の順番で進めてください。

入力情報:

- 症状: ${input:symptom:例: 特定日の時刻一覧が空になる}
- 再現手順: ${input:steps:例: メニューA選択→2026-04-10を選ぶ→時刻一覧へ}
- 期待動作: ${input:expected:例: 10:00, 10:30 が表示される}
- 対象画面/機能: ${input:scope:例: reservations.times}

必須アウトプット:

1. 原因の要約（どの条件・どのコードパスで発生するか）
2. 修正内容（変更ファイルごとに要点）
3. 後方互換の確認

- 新方式 reservation 時間帯チェック
- 旧方式 slot ベース予約チェック

4. 実行した検証コマンド

- php artisan test（関連テスト）
- 必要なら php artisan pint

5. 残るリスクと追加テスト案

制約:

- 予約可否判定は AvailabilityService を中心に保つこと。
- タイムゾーンは Asia/Tokyo 前提を維持すること。
- 不要なリファクタはしないこと。

参考:

- [Reservation controller](../../src/app/Http/Controllers/ReservationController.php)
- [Availability service](../../src/app/Services/AvailabilityService.php)
- [Reservation model](../../src/app/Models/Reservation.php)
- [Project guide](../../AGENT.md)
