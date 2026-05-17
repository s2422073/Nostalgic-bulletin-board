<?php
// PHPエラー表示を有効にする (デバッグ用。本番環境では無効にしてください)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db.php"); // DB接続とセッション開始

// 未ログインの場合はログインページへリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$message = '';

// POSTリクエストであるかを確認
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);

    if ($post_id === false || $post_id === null) {
        $error = "無効な投稿IDが指定されました。";
    } else {
        // 削除する投稿のファイル名を取得 (削除権限確認のため、user_idも一緒に検索)
        $sql_select = "SELECT filename FROM location_diary WHERE id = $1 AND user_id = $2";
        $res_select = pg_query_params($dbconn, $sql_select, [$post_id, $user_id]);

        if ($res_select) {
            $row = pg_fetch_assoc($res_select);
            if ($row) {
                $filename_to_delete = $row['filename'];

                // データベースから投稿を削除
                // user_idも条件に加えることで、他のユーザーの投稿を削除できないようにする
                $sql_delete = "DELETE FROM location_diary WHERE id = $1 AND user_id = $2";
                $res_delete = pg_query_params($dbconn, $sql_delete, [$post_id, $user_id]);

                if ($res_delete) {
                    // データベースから削除成功した場合、関連するファイルも削除
                    if ($filename_to_delete && file_exists("uploads/" . $filename_to_delete)) {
                        unlink("uploads/" . $filename_to_delete);
                        error_log("Deleted file: uploads/" . $filename_to_delete); // ログ出力
                    }
                    $message = "投稿を削除しました。";
                } else {
                    $error = "投稿の削除に失敗しました: " . pg_last_error($dbconn);
                    error_log("Failed to delete post from DB: ID=$post_id, UserID=$user_id, Error=" . pg_last_error($dbconn)); // ログ出力
                }
            } else {
                $error = "指定された投稿が見つからないか、削除権限がありません。";
            }
        } else {
            $error = "データベースからの投稿情報取得に失敗しました: " . pg_last_error($dbconn);
            error_log("Failed to select post for deletion: ID=$post_id, UserID=$user_id, Error=" . pg_last_error($dbconn)); // ログ出力
        }
    }
} else {
    // POST以外のリクエストは不正アクセス
    $error = "不正なアクセスです。";
}

// 処理結果をセッションに保存してview.phpへリダイレクト
if (!empty($message)) {
    $_SESSION['message'] = $message;
}
if (!empty($error)) {
    $_SESSION['error'] = $error;
}
header("Location: view.php");
exit;
?>