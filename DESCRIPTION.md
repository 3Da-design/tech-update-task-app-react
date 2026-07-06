../EXPERIMENT-STACK.md と CLAUDE.md を読んで、S2（React）のベースライン構築を完了してください。
ポート変更は済みです（Web 8004 / DB 5436 / Vite dev 5175）。

【参考】
- S1（htmljs）が完了していれば ../tech-update-task-app-htmljs/docs/STACK-PROFILE.md の Sanctum 設定を参考にする（認証方針を S1 と揃える）
- 未完了なら improved（../tech-update-task-app）の API 契約を維持しつつ、React 向け Sanctum SPA 設定を新規で行う

【実装方針】
- バックエンド: improved（TaskService / TaskRepository）を維持。Fat Controller 化禁止
- フロント: React + Vite + TypeScript を frontend/ に配置（SPA）
- ルーティング: React Router（理由を docs/STACK-PROFILE.md に記載）
- タスク UI: Blade（resources/views/tasks/*）をやめ、React コンポーネントに置き換え
- Web\TaskController のタスク CRUD ルートは削除または無効化し、タスク操作は API 経由に一本化
- 認証: Laravel Sanctum（SPA / Cookie 方式）
  - SANCTUM_STATEFUL_DOMAINS に localhost:5175, localhost:8004 等を設定
  - axios または fetch で credentials: 'include'、CSRF クッキー取得フローを実装
- ログイン: React のログイン画面を用意（Breeze Blade /login に依存しない SPA 完結型を推奨）
- API 契約: 既存 REST API（/api/tasks）と TaskResource の JSON 形式を S0/improved と同一に保つ
- タスク属性は4項目のみ（title / description / due_date / status）。priority 等は未実装

【ビルド・Docker】
- ホストで npm install しない（node コンテナ / composer npm:docker-build を使用）
- check-quality.sh が通るよう ESLint + Vite build + PHPUnit + Newman を整合
- 本番相当: composer npm:docker-build でフロント資産をビルドし、nginx から配信できる構成にする
  （開発時は docker compose --profile node run --rm --service-ports node npm run dev で 5175）

【品質ゲート（すべて必須）】
1. ./scripts/check-quality.sh が成功
2. ブラウザで login → タスク CRUD・一覧フィルタ・ソートが動作
3. docs/STACK-PROFILE.md を作成（ディレクトリ構成・認証フロー・ポート・parity 表）
4. experiment-baseline-v1 タグを打つ

【進め方】
- 実装 → check-quality.sh 実行 → 失敗箇所を修正、を繰り返す
- 各ラウンドで変更ファイル一覧・残課題・手動確認結果を報告
- 完了時に STACK-PROFILE と手動確認手順（URL・ログイン情報）を書く

【禁止】
- Service/Repository の削除・リファクタによる設計変更
- シナリオ変更（priority / status-int 等）の混入
- main / experiment-baseline-v1 へのシナリオ混在
- tech-update-task-app-best の参照
- ホストでの npm install / php artisan serve

日本語で作業・説明・コミットメッセージを書いてください。
