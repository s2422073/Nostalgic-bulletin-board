<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>新規登録</title>
<style>
body {
  font-family: 'Inter', 'Noto Sans JP', sans-serif; /* モダンなフォント */
  background-color: #f0f2f5; /* 落ち着いた背景色 */
  display: flex; /* 中央寄せのため */
  justify-content: center; /* 水平方向の中央寄せ */
  align-items: center; /* 垂直方向の中央寄せ */
  min-height: 100vh; /* ビューポートいっぱいの高さ */
  margin: 0;
  padding: 20px;
  box-sizing: border-box; /* パディングを含めてサイズ計算 */
}
h2 {
  color: #334a52; /* 濃い目の落ち着いた色 */
  margin-bottom: 25px; /* 下にスペース */
  font-size: 1.8em; /* 見出しサイズ調整 */
  font-weight: 600; /* 少し太めに */
  text-align: center;
}
form {
  background: #ffffff; /* 白い背景 */
  padding: 40px 50px; /* パディングを増やす */
  border-radius: 12px; /* 角を丸く */
  box-shadow: 0 8px 25px rgba(0,0,0,0.08); /* 影を強調 */
  width: 100%;
  max-width: 400px; /* 最大幅を設定 */
  box-sizing: border-box;
}
input[type=text], input[type=password] {
  width: calc(100% - 20px); /* パディングを考慮 */
  padding: 12px 10px; /* パディングを増やす */
  margin: 10px 0; /* マージン調整 */
  border: 1px solid #dcdfe6; /* 落ち着いたボーダー色 */
  border-radius: 6px; /* 角を丸く */
  font-size: 1em; /* フォントサイズ */
  color: #333;
  transition: border-color 0.3s, box-shadow 0.3s; /* ホバー時のアニメーション */
}
input[type=text]:focus, input[type=password]:focus {
  border-color: #5b7e8d; /* フォーカス時の色 */
  box-shadow: 0 0 0 3px rgba(91, 126, 141, 0.2); /* フォーカス時のシャドウ */
  outline: none; /* アウトラインを消す */
}
input[type=submit] {
  background-color: #334a52; /* メインカラー */
  color: white;
  padding: 12px 25px; /* パディング調整 */
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1.1em;
  font-weight: 500;
  margin-top: 20px; /* 上にスペース */
  width: 100%; /* 幅を100%に */
  transition: background-color 0.3s ease; /* ホバー時のアニメーション */
}
input[type=submit]:hover {
  background-color: #273a42; /* ホバー時の色 */
}
a {
  display: block; /* ブロック要素にして中央寄せしやすく */
  margin-top: 20px;
  color: #5b7e8d; /* リンクの色 */
  text-decoration: none;
  font-size: 0.95em;
  transition: color 0.3s ease; /* ホバー時のアニメーション */
  text-align: center;
}
a:hover {
  color: #334a52; /* ホバー時の色 */
  text-decoration: underline; /* ホバー時に下線 */
}
p {
  color: #555;
  text-align: center;
  margin-top: 15px;
  font-size: 0.95em;
}
p.error-message { /* エラーメッセージ用のスタイル */
  color: #e74c3c; /* 赤系のエラー色 */
  font-weight: bold;
  margin-bottom: 15px;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    form {
        padding: 25px 30px;
    }
    input[type=text], input[type=password], input[type=submit] {
        font-size: 0.9em;
        padding: 10px;
    }
    h2 {
        font-size: 1.5em;
    }
}
</style>
</head>
<body>
<h2>ユーザー新規登録</h2>
<?php
require_once("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // ユーザー名の重複チェック
  $res = pg_query_params($dbconn, "SELECT id FROM users WHERE username = $1", [$username]);
  if (pg_num_rows($res) > 0) {
    echo "<p class='error-message'>そのユーザー名は既に使われています。</p>";
  } else {
    // パラメータ化クエリで安全にユーザーを登録
    pg_query_params($dbconn, "INSERT INTO users (username, password) VALUES ($1, $2)", [$username, $password])
      or die('登録失敗: ' . pg_last_error());
    echo "<p>登録が完了しました！<a href='login.php'>ログイン</a></p>";
    exit;
  }
}
?>
<form method="post">
  ユーザー名: <input type="text" name="username" required><br>
  パスワード: <input type="password" name="password" required><br>
  <input type="submit" value="登録">
</form>
<a href="login.php">→ ログインへ</a>
</body>
</html>