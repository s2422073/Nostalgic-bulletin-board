<?php
// このファイルはview.phpからのみ読み込まれることを想定
if (!defined('VIEW_FILE_INCLUDED')) {
    die('直接アクセスは許可されていません。');
}

// コメント関連処理のロジック (POSTリクエストかつ 'action' パラメータが存在する場合に実行)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_comment':
            $post_id_for_comment = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
            $comment_text = trim($_POST['comment_text']);

            if ($post_id_for_comment && $comment_text !== '') {
                $sql_insert_comment = "INSERT INTO comments (post_id, user_id, comment_text) VALUES ($1, $2, $3);";
                $res_insert_comment = pg_query_params($dbconn, $sql_insert_comment, [$post_id_for_comment, $user_id, $comment_text]);
                if ($res_insert_comment) {
                    $_SESSION['message'] = "<p class='success-message'>コメントを投稿しました。</p>";
                } else {
                    $_SESSION['error'] = "<p class='error-message'>コメントの投稿に失敗しました: " . htmlspecialchars(pg_last_error($dbconn)) . "</p>";
                }
            } else {
                $_SESSION['error'] = "<p class='error-message'>コメント内容が空、または投稿IDが無効です。</p>";
            }
            header("Location: view.php"); // 処理後リダイレクト
            exit;

        case 'edit_comment':
            $comment_id_to_edit = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
            $new_comment_text = trim($_POST['new_comment_text']);

            if ($comment_id_to_edit && $new_comment_text !== '') {
                // コメントの所有者チェック
                $sql_check_owner = "SELECT id FROM comments WHERE id = $1 AND user_id = $2;";
                $res_check_owner = pg_query_params($dbconn, $sql_check_owner, [$comment_id_to_edit, $user_id]);

                if (pg_fetch_assoc($res_check_owner)) {
                    $sql_update_comment = "UPDATE comments SET comment_text = $1 WHERE id = $2 AND user_id = $3;";
                    $res_update_comment = pg_query_params($dbconn, $sql_update_comment, [$new_comment_text, $comment_id_to_edit, $user_id]);
                    if ($res_update_comment) {
                        $_SESSION['message'] = "<p class='success-message'>コメントを編集しました。</p>";
                    } else {
                        $_SESSION['error'] = "<p class='error-message'>コメントの編集に失敗しました: " . htmlspecialchars(pg_last_error($dbconn)) . "</p>";
                    }
                } else {
                    $_SESSION['error'] = "<p class='error-message'>このコメントを編集する権限がありません。</p>";
                }
            } else {
                $_SESSION['error'] = "<p class='error-message'>コメント内容が空、またはコメントIDが無効です。</p>";
            }
            header("Location: view.php"); // 処理後リダイレクト
            exit;

        case 'delete_comment':
            $comment_id_to_delete = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);

            if ($comment_id_to_delete) {
                // コメントの所有者チェック
                $sql_check_owner = "SELECT id FROM comments WHERE id = $1 AND user_id = $2;";
                $res_check_owner = pg_query_params($dbconn, $sql_check_owner, [$comment_id_to_delete, $user_id]);

                if (pg_fetch_assoc($res_check_owner)) {
                    $sql_delete_comment = "DELETE FROM comments WHERE id = $1 AND user_id = $2;";
                    $res_delete_comment = pg_query_params($dbconn, $sql_delete_comment, [$comment_id_to_delete, $user_id]);
                    if ($res_delete_comment) {
                        $_SESSION['message'] = "<p class='success-message'>コメントを削除しました。</p>";
                    } else {
                        $_SESSION['error'] = "<p class='error-message'>コメントの削除に失敗しました: " . htmlspecialchars(pg_last_error($dbconn)) . "</p>";
                    }
                } else {
                    $_SESSION['error'] = "<p class='error-message'>このコメントを削除する権限がありません。</p>";
                }
            } else {
                $_SESSION['error'] = "<p class='error-message'>コメントIDが無効です。</p>";
            }
            header("Location: view.php"); // 処理後リダイレクト
            exit;
    }
}
?>