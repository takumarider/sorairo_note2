---
description: "Use when implementing or fixing reservation flow, booking availability, time calculations, cancellation rules, or reservation notifications in Laravel controllers/services/models."
name: "Reservation Flow Guidelines"
applyTo:
  - "src/app/Http/Controllers/ReservationController.php"
  - "src/app/Http/Controllers/MypageController.php"
  - "src/app/Services/AvailabilityService.php"
  - "src/app/Services/NotificationService.php"
  - "src/app/Models/Reservation.php"
  - "src/routes/web.php"
---

# Reservation Flow Guidelines

## Scope

- 対象は予約導線（カレンダー、時刻選択、確認、確定、キャンセル）です。
- 予約可否ロジックは AvailabilityService を起点に保ちます。

## Core Rules

- 二重予約防止は現行のトランザクションとロック戦略を維持します。
- 時刻処理は Asia/Tokyo と Carbon 前提で統一します。
- 予約可否判定では新方式と旧方式の両方を壊さないようにします。
  - reservations の時間帯チェック
  - slot ベースの予約済みチェック
- 予約作成・キャンセル時の通知は NotificationService と Mail クラスを再利用します。

## Implementation Checklist

- ルーティング変更時は web.php の既存フロー順序を確認します。
- 入力バリデーションは既存 Request/バリデーション方針を優先します。
- 予約ステータスや時刻カラムの cast と整合性を確認します。
- 変更後は最低限、予約確定とキャンセルの Feature Test を実行します。

## Do Not

- Blade に予約ビジネスロジックを直接書かない。
- AvailabilityService を迂回して可否判定を重複実装しない。
- タイムゾーン未指定の日時生成を増やさない。

## References

- [Project setup and product context](../../src/README.md)
- [Detailed project guide](../../AGENT.md)
- [Reservation routes](../../src/routes/web.php)
- [Reservation controller](../../src/app/Http/Controllers/ReservationController.php)
- [Availability service](../../src/app/Services/AvailabilityService.php)
