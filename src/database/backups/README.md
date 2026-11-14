# 動作確認用バックアップ

`synthetic-test-data.sql` は、PostgreSQL 16 の空のデータベースから作成した、スキーマと架空のテストデータを含むバックアップです。以前の開発データを復元するものではありません。本番環境では使用しないでください。

内容は管理者・利用者各1名、通常メニュー2件、イベントメニュー1件、オプション、営業時間、予約、メッセージなどです。予約公開月と予約日はバックアップ作成時点の固定値です。利用時に期限が過ぎている場合は管理画面で予約公開月や日付を設定してください。

テスト専用のログイン情報は以下のとおりです。

| 種別 | メールアドレス | パスワード |
| --- | --- | --- |
| 管理者 | `admin@example.test` | `TestOnly!2026` |
| 利用者 | `customer@example.test` | `TestOnly!2026` |

これらは公開されたテスト用アカウントです。実際のアカウントとして使用しないでください。

## 別の空データベースに復元する

以下はリポジトリ直下で実行します。**現在の開発 DB や本番 DB には復元しないでください。** 最初のコマンドは新しい `sorairo_sample_backup` データベースを作ります。同名の DB が既に存在する場合は失敗するので、その場合は実行を止め、空の新しい DB 名を選んで両方のコマンドを変更してください。

```bash
docker compose exec -T db createdb -U sorairo sorairo_sample_backup
docker compose exec -T db psql -v ON_ERROR_STOP=1 -1 -U sorairo -d sorairo_sample_backup < src/database/backups/synthetic-test-data.sql
```

この操作だけでは、通常のアプリの接続先や既存の開発 DB は変更されません。アプリからこのデータを閲覧するには、接続先を別途 `sorairo_sample_backup` に設定する必要があります。DB 接続先がキャッシュされている場合があるため、切り替え前に接続設定を確認してください。
