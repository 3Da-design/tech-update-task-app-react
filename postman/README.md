# Postman（実行後・API 通信）

## インポート

1. Postman で **Import** → `Task-API.postman_collection.json` と `local.postman_environment.json`
2. 右上の Environment で **Local (Docker)** を選択

## 実行順

1. `docker compose up -d` と `migrate --seed` 済みであること
2. 右上 Environment で **Local (Docker)** を選択
3. **Auth → GET /login (CSRF cookie)**（`csrfToken` 変数がセットされる）
4. **Auth → POST /login**（フォーム送信 + CSRF）
5. **Tasks API** フォルダを上から実行

### Collection Runner

1. コレクション **Task API** → **Run**
2. フォルダ順: **Health** → **Auth** → **Tasks API**（Auth を Tasks より前に）
3. Environment: **Local (Docker)**
4. **Save cookies** にチェックが入っていることを確認

### POST /login が 419 になる場合

Laravel の CSRF 不一致です。次を試してください。

1. Postman の **Cookies** → `localhost` をすべて削除
2. **Auth** フォルダだけを先に Run（GET /login → POST /login）
3. 成功したら **Tasks API** を Run
4. `baseUrl` が `http://localhost:8000` であること（`127.0.0.1` と混在しない）

修正済みコレクションでは、GET /login の HTML から **平文の csrf-token** だけを `csrfToken` に保存します。

**Tasks API が 401 になる場合:** Laravel の API ルートでセッションが有効か確認してください（`bootstrap/app.php` で `StartSession` 等を API に付与）。Postman では **Save cookies** を ON にし、POST /login の後に Tasks API を実行します。**XSRF-TOKEN Cookie の値を `_token` に使うと 419 になります**（Cookie は暗号化されているため）。POST /login には `X-XSRF-TOKEN` ヘッダーを付けず、フォームの `_token` のみ送ります。

## Newman（CLI）

```bash
npx newman run postman/Task-API.postman_collection.json \
  -e postman/local.postman_environment.json \
  --folder "Health" \
  --folder "Auth" \
  --folder "Tasks API"
```

※ セッション Cookie が必要なため、Collection Runner では **Auth を先に** 実行してください。
