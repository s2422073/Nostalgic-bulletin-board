<?php
// PHPエラー表示を有効にする (デバッグ用。本番環境では無効にしてください)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db.php"); // DB接続とセッション開始 (db.phpでsession_start()が呼び出されている前提)

// 未ログインの場合はログインページへリダイレクト
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

// セッションからのメッセージとエラーの取得
$message = '';
$error = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // 一度表示したら削除
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); // 一度表示したら削除
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>位置メモ投稿</title>
  <style>
    body {
      font-family: 'Inter', 'Noto Sans JP', sans-serif;
      background-color: #f0f2f5;
      margin: 0;
      padding: 20px;
      color: #333;
      line-height: 1.6;
    }
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      margin-bottom: 30px;
      border-bottom: 1px solid #e0e0e0;
      flex-wrap: wrap; /* レスポンシブ対応 */
    }
    .header-links {
        display: flex;
        flex-wrap: wrap; /* レスポンシブ対応 */
        gap: 15px; /* リンク間のスペース */
    }
    .header-links a {
      margin-left: 0; /* flexboxでgapを使うため0に */
      color: #5b7e8d;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }
    .header-links a:hover {
      color: #334a52;
    }
    h2 {
      text-align: center;
      color: #334a52;
      font-size: 2em;
      margin-bottom: 30px;
      font-weight: 700;
    }
    form {
      background-color: #ffffff;
      padding: 35px 45px;
      border-radius: 12px;
      max-width: 550px;
      margin: auto;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      box-sizing: border-box;
    }
    label {
      display: block;
      margin-bottom: 8px;
      color: #555;
      font-weight: 500;
      font-size: 0.95em;
    }
    input[type=text], textarea, input[type=file] { /* textareaも追加 */
      width: calc(100% - 20px);
      padding: 12px 10px;
      margin-bottom: 20px;
      border: 1px solid #dcdfe6;
      border-radius: 6px;
      font-size: 1em;
      color: #333;
      transition: border-color 0.3s, box-shadow 0.3s;
      box-sizing: border-box;
    }
    textarea { /* textareaのスタイル調整 */
      min-height: 100px;
      resize: vertical;
    }
    input[type=file] {
      padding: 10px 10px; /* ファイル入力フィールドのパディング調整 */
    }
    input[type=text]:focus, textarea:focus, input[type=file]:focus { /* textareaも追加 */
      border-color: #5b7e8d;
      box-shadow: 0 0 0 3px rgba(91, 126, 141, 0.2);
      outline: none;
    }
    input[type=submit] {
      background-color: #334a52;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1.1em;
      font-weight: 500;
      width: 100%;
      margin-top: 10px;
      transition: background-color 0.3s ease;
    }
    input[type=submit]:hover {
      background-color: #273a42;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .checkbox-group {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .checkbox-group input[type="checkbox"] {
        margin-right: 10px;
        width: auto; /* チェックボックスの幅を自動に */
        margin-bottom: 0; /* 不要なマージンを削除 */
    }
    /* メッセージボックスのスタイル */
    .message-box {
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 5px;
      text-align: center;
      font-weight: bold;
      max-width: 550px;
      margin-left: auto;
      margin-right: auto;
      box-sizing: border-box;
    }
    .success-message {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .error-message {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    /* レスポンシブ対応 */
    @media (max-width: 768px) {
        header {
            flex-direction: column;
            align-items: flex-start;
        }
        .header-links {
            margin-top: 10px;
            gap: 10px;
        }
        form {
            padding: 25px 30px;
        }
        input[type=text], textarea, input[type=file], input[type=submit] { /* textareaも追加 */
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
  <header>
    <h1>マイメモリー</h1>
    <div class="header-links">
      <a href="view.php">投稿一覧を見る</a>
      <a href="logout.php">ログアウト</a>
    </div>
  </header>

  <h2>位置・メモ・画像を投稿</h2>

  <?php if (!empty($message)): ?>
    <div class="message-box success-message"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="message-box error-message"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form action="upload.php" method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label for="memo">メモ (任意):</label>
      <textarea name="memo" id="memo" placeholder="今日の出来事をメモ"></textarea> </div>
    <div class="form-group">
      <label for="file_data">画像 (任意):</label>
      <input type="file" name="file_data" id="file_data" accept="image/*">
    </div>
    <div class="checkbox-group">
        <input type="checkbox" name="is_public" id="is_public" value="1" checked>
        <label for="is_public">この投稿を公開する (他のユーザーも閲覧可能になります)</label>
    </div>
    <input type="hidden" name="lat" id="lat" value="0.0">
    <input type="hidden" name="lng" id="lng" value="0.0">
    <input type="submit" value="送信">
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const latInput = document.getElementById("lat");
      const lngInput = document.getElementById("lng");

      // 初期値を0.0に設定 (位置情報取得失敗時の保険)
      // JSによる自動取得が遅れても、PHP側には最低限0.0が送信される
      latInput.value = "0.0";
      lngInput.value = "0.0";

      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function(pos) {
            latInput.value = pos.coords.latitude;
            lngInput.value = pos.coords.longitude;
            // 位置情報が取得できたら、ユーザーに知らせる（オプション）
            // alert("位置情報を取得しました！");
          },
          function(error) {
            let errorMessage = "位置情報の取得に失敗しました。";
            switch(error.code) {
              case error.PERMISSION_DENIED:
                errorMessage = "ブラウザで位置情報の利用が許可されませんでした。ブラウザの設定をご確認ください。";
                break;
              case error.POSITION_UNAVAILABLE:
                errorMessage = "位置情報が利用できませんでした。";
                break;
              case error.TIMEOUT:
                errorMessage = "位置情報の取得がタイムアウトしました。";
                break;
              case error.UNKNOWN_ERROR:
                errorMessage = "不明なエラーが発生しました。";
                break;
            }
            alert(errorMessage + "\n（緯度・経度は初期値0.0で投稿されます）");
          },
          { // オプションオブジェクトを追加し、高精度な位置情報を要求
            enableHighAccuracy: true,
            timeout: 5000, // タイムアウト時間（ミリ秒）
            maximumAge: 0  // キャッシュされた位置情報を使用しない
          }
        );
      } else {
        alert("お使いのブラウザは位置情報に対応していません。\n（緯度・経度は初期値0.0で投稿されます）");
      }
    });
  </script>
</body>
</html>