<?php
// PHPエラー表示を有効にする (デバッグ用。本番環境では無効にしてください)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 各ファイルが直接アクセスされないように定義
define('VIEW_FILE_INCLUDED', true);

require_once("db.php"); // DB接続とセッション開始

// 未ログインの場合はログインページへリダイレクト
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$message = ''; // ユーザーへのメッセージ
$error = '';

// URLからのメッセージとエラーの取得 (リダイレクト時など)
if (isset($_GET['message'])) {
    $message .= htmlspecialchars(urldecode($_GET['message']));
}
if (isset($_GET['error'])) {
    $error .= htmlspecialchars(urldecode($_GET['error']));
}

// セッションに保存されたメッセージがあれば表示
if (isset($_SESSION['message'])) {
    $message .= $_SESSION['message']; // 既存メッセージに追加
    unset($_SESSION['message']); // 一度表示したら削除
}
if (isset($_SESSION['error'])) {
    $error .= $_SESSION['error']; // 既存エラーに追加
    unset($_SESSION['error']); // 一度表示したら削除
}

// コメント処理ロジックを読み込む（POSTアクションがある場合のみ実行される）
require_once("includes/comment_logic.php");

// 投稿表示・編集ロジックを読み込む
require_once("includes/post_logic.php"); // このファイル内で投稿編集のPOST処理も行われる

// ヘッダー部分を読み込む
require_once("includes/header.php");
?>

<?php if (!empty($message)): ?>
  <div class="message-box success-message"><?php echo $message; ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="message-box error-message"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($post_id_to_edit > 0 && $post_to_edit): // 編集モードの場合 ?>
  <?php require_once("templates/post_edit_form.php"); ?>
<?php else: // 一覧モードの場合 ?>
  <h2>投稿一覧</h2>

  <div class="controls">
    <a href="?mode=my_posts<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="<?php echo ($display_mode === 'my_posts' ? 'active' : ''); ?>">自分の投稿</a>
    <a href="?mode=public<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="<?php echo ($display_mode === 'public' ? 'active' : ''); ?>">公開された投稿</a>
    <form method="get" style="display:inline-block; margin-left: 20px;">
        <input type="hidden" name="mode" value="<?php echo htmlspecialchars($display_mode); ?>">
        <input type="text" name="search" placeholder="キーワード検索..." value="<?php echo htmlspecialchars($search_query); ?>" style="width: 180px; padding: 8px; border: 1px solid #dcdfe6; border-radius: 5px;">
        <button type="submit" style="padding: 8px 12px; background-color: #5b7e8d; color: white; border: none; border-radius: 5px; cursor: pointer;">検索</button>
        <?php if (!empty($search_query)): ?>
            <a href="?mode=<?php echo htmlspecialchars($display_mode); ?>" style="background-color: #f0f2f5; color: #555; border: 1px solid #dcdfe6; margin-left: 5px;">クリア</a>
        <?php endif; ?>
    </form>
  </div>

  <?php require_once("templates/post_list_table.php"); ?>
<?php endif; ?>

<?php
// フッター部分を読み込む
require_once("includes/footer.php");
?>