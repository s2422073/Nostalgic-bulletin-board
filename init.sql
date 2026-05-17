-- DBテーブル定義（最初に一度だけ実行）
-- もし既存のテーブルがある場合は、DROP TABLE IF EXISTS で一度削除してから再作成するか、
-- ALTER TABLE を使用してカラムを追加してください。

-- usersテーブル（変更なし）
CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  username TEXT UNIQUE NOT NULL,
  password TEXT NOT NULL
);

-- location_diaryテーブル（is_publicカラムを追加）
CREATE TABLE IF NOT EXISTS location_diary (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(id) ON DELETE CASCADE, -- ユーザー削除時に投稿も削除
  lat FLOAT,
  lng FLOAT,
  memo TEXT,
  filename TEXT,
  is_public BOOLEAN DEFAULT FALSE, -- 新規追加：公開設定
  created_at TIMESTAMP DEFAULT current_timestamp
);

-- commentsテーブル（新規追加）
CREATE TABLE IF NOT EXISTS comments (
  id SERIAL PRIMARY KEY,
  post_id INT REFERENCES location_diary(id) ON DELETE CASCADE, -- 投稿削除時にコメントも削除
  user_id INT REFERENCES users(id) ON DELETE CASCADE, -- ユーザー削除時にコメントも削除
  comment_text TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT current_timestamp
);

-- ヒント: 既存のDBにis_publicカラムを追加する場合のSQL
-- ALTER TABLE location_diary ADD COLUMN is_public BOOLEAN DEFAULT FALSE;