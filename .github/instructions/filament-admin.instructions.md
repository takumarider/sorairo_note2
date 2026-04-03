---
description: "Use when creating or editing Filament resources, forms, tables, admin actions, and admin access control for the salon reservation system."
name: "Filament Admin Guidelines"
applyTo:
  - "src/app/Filament/**/*.php"
  - "src/app/Models/User.php"
---

# Filament Admin Guidelines

## Scope

- Filament 管理画面の Resource、Form、Table、Action、Widget を対象にします。
- 管理者権限は User の is_admin と canAccessPanel を基準に扱います。

## Core Rules

- フォーム/テーブルのラベルは既存に合わせて日本語優先にします。
- 一覧の検索・並び替え・フィルタは運用で使う列を優先します。
- 削除や状態変更アクションは予約整合性を崩さない条件付きにします。
- 既存のモデルリレーションと cast 前提を崩さないようにします。

## Implementation Checklist

- Resource 追加時は生成コマンドをベースに最小差分で実装します。
- テーブル列は必要な searchable/sortable/toggleable を明示します。
- フォーム保存時の副作用は Service または Model 側に寄せます。
- 管理画面での変更がユーザー予約フローに影響しないか確認します。

## Do Not

- 権限判定を画面ごとに重複実装しない。
- 重い集計処理を無制限で一覧描画時に実行しない。
- 既存命名規約から逸脱した列名・ステータス値を導入しない。

## References

- [Project setup and product context](../../src/README.md)
- [Detailed project guide](../../AGENT.md)
- [User model access gate](../../src/app/Models/User.php)
