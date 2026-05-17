<?php
require_once("db.php");

// POST送信時のログイン処理（HTMLより前に書く）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  // パラメータ化クエリを使用し、SQLインジェクションを防止
  $res = pg_query_params($dbconn, "SELECT id FROM users WHERE username = $1 AND password = $2", [$username, $password]);
  if ($row = pg_fetch_assoc($res)) {
    $_SESSION['user_id'] = $row['id'];
    header("Location: index.php");
    exit;
  } else {
    $error = "ログインに失敗しました。";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>ログイン</title>
  <style>
    body {
      font-family: 'Inter', 'Noto Sans JP', sans-serif; /* モダンなフォント */
      background-color: #eef2f6; /* 落ち着いた背景色 */
      display: flex; /* 中央寄せのため */
      flex-direction: column; /* 垂直方向に並べる */
      justify-content: center; /* 垂直方向の中央寄せ */
      align-items: center; /* 水平方向の中央寄せ */
      min-height: 100vh; /* ビューポートいっぱいの高さ */
      margin: 0;
      padding: 20px;
      box-sizing: border-box;
    }
    h2 {
      color: #334a52; /* 濃い目の落ち着いた色 */
      margin-bottom: 25px;
      font-size: 1.8em;
      font-weight: 600;
      text-align: center; /* ここを追加・変更 */
      width: 100%; /* 親要素の幅いっぱいに広げる */
    }
    form {
      display: block; /* inline-blockからblockに変更 */
      background: #ffffff;
      padding: 40px 50px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      width: 100%;
      max-width: 400px;
      box-sizing: border-box;
      margin: 0 auto; /* 中央寄せのため */
    }
    input[type=text], input[type=password] {
      width: calc(100% - 20px);
      padding: 12px 10px;
      margin: 10px 0;
      border: 1px solid #dcdfe6;
      border-radius: 6px;
      font-size: 1em;
      color: #333;
      transition: border-color 0.3s, box-shadow 0.3s;
    }
    input[type=text]:focus, input[type=password]:focus {
      border-color: #5b7e8d;
      box-shadow: 0 0 0 3px rgba(91, 126, 141, 0.2);
      outline: none;
    }
    input[type=submit] {
      background-color: #334a52;
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1.1em;
      font-weight: 500;
      margin-top: 20px;
      width: 100%;
      transition: background-color 0.3s ease;
    }
    input[type=submit]:hover {
      background-color: #273a42;
    }
    a {
      color: #5b7e8d;
      text-decoration: none;
      display: block;
      margin-top: 20px;
      font-size: 0.95em;
      transition: color 0.3s ease;
      text-align: center;
    }
    a:hover {
      color: #334a52;
      text-decoration: underline;
    }
    p {
      color: #555;
      text-align: center;
      margin-top: 15px;
      font-size: 0.95em;
    }
    p.error-message { /* エラーメッセージ用のスタイル */
      color: #e74c3c;
      font-weight: bold;
      margin-bottom: 15px;
      text-align: center; /* エラーメッセージも中央寄せ */
      width: 100%; /* 親要素の幅いっぱいに広げる */
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
  <h2>ログインフォーム</h2>
  <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
  <form method="post">
    ユーザー名: <input type="text" name="username" required><br>
    パスワード: <input type="password" name="password" required><br>
    <input type="submit" value="ログイン">
  </form>
  <a href="register.php">→ 新規登録はこちら</a>
</body>
</html>