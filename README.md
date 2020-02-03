## 機械学習で投稿内容を査定する掲示板

### サーバーサイド
- Laravel6
- Nginx
- Mysql5.7

フロントとはリポジトリを分け、WebApiとして利用

#### 機能一覧
- ユーザーの登録・ログイン・ログアウト
- SESメールによるパスワードリマインダー

- 掲示板(フォーラム）投稿
- 添付画像アップロード
- アップロードした画像をRekognitionで査定し、投稿禁止 or 警告

- レスポンス投稿
- 添付画像アップロード
- アップロードした画像を査定し、分類分け
- 投稿内容をComprehendで解析し、感情分析を行う

- 通知機能(レスポンスへの返信があったとき)

### フロント
(https://github.com/naoto-takaya/forum-frontend)

### その他の技術 (環境構築等）
- Docker
- CircleCI

### インフラ（AWS）

![構成図](https://cacoo.com/diagrams/SrTJ8gcDQoU84q3r-151C2.png)
