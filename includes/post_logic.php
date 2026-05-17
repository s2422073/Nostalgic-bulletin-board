<?php
// このファイルはview.phpからのみ読み込まれることを想定
if (!defined('VIEW_FILE_INCLUDED')) {
    die('直接アクセスは許可されていません。');
}

// 投稿の表示モード制御
$display_mode = isset($_GET['mode']) && in_array($_GET['mode'], ['public', 'my_posts']) ? $_GET['mode'] : 'my_posts';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$post_id_to_edit = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0; // 編集対象の投稿ID

// ページネーション設定
$posts_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $posts_per_page;

// 投稿データの取得用SQLパラメータとWHERE句の構築
$sql_params = [];
$param_index = 1;
$sql_where_clauses = [];

if ($display_mode === 'my_posts') {
    $sql_where_clauses[] = "user_id = $" . ($param_index++);
    $sql_params[] = $user_id;
} else if ($display_mode === 'public') {
    $sql_where_clauses[] = "is_public = TRUE";
}

if (!empty($search_query)) {
    $sql_where_clauses[] = "(memo ILIKE $" . ($param_index++) . " OR lat::text ILIKE $" . ($param_index++) . " OR lng::text ILIKE $" . ($param_index++) . ")";
    $sql_params[] = '%' . $search_query . '%';
    $sql_params[] = '%' . $search_query . '%';
    $sql_params[] = '%' . $search_query . '%';
}

$where_sql = count($sql_where_clauses) > 0 ? "WHERE " . implode(" AND ", $sql_where_clauses) : "";

// 合計投稿数の取得
$count_sql = "SELECT COUNT(*) FROM location_diary " . $where_sql;
$count_res = pg_query_params($dbconn, $count_sql, $sql_params);
$total_posts = pg_fetch_result($count_res, 0, 0);
$total_pages = ceil($total_posts / $posts_per_page);

// 投稿データの取得（ユーザー名も結合）
$sql = "
    SELECT
        ld.id,
        ld.lat,
        ld.lng,
        ld.memo,
        ld.filename,
        ld.created_at,
        ld.is_public,
        u.username AS author_username,
        u.id AS author_id
    FROM
        location_diary ld
    JOIN
        users u ON ld.user_id = u.id
    {$where_sql}
    ORDER BY
        ld.created_at DESC
    LIMIT $" . ($param_index++) . " OFFSET $" . ($param_index++);

$sql_params[] = $posts_per_page;
$sql_params[] = $offset;

$posts_result = pg_query_params($dbconn, $sql, $sql_params) or die('Select failed: ' . pg_last_error());
$posts = pg_fetch_all($posts_result);

// 編集対象の投稿データ取得 (編集フォーム表示用)
$post_to_edit = null;
if ($post_id_to_edit > 0) {
    $edit_sql = "SELECT memo, filename, lat, lng, is_public FROM location_diary WHERE id = $1 AND user_id = $2;";
    $edit_result = pg_query_params($dbconn, $edit_sql, [$post_id_to_edit, $user_id]);
    $post_to_edit = pg_fetch_assoc($edit_result);

    if (!$post_to_edit) {
        $_SESSION['error'] = "指定された投稿が見つからないか、編集権限がありません。";
        $post_id_to_edit = 0; // 無効なIDとしてリセット
        header("Location: view.php?error=" . urlencode($_SESSION['error']));
        exit;
    }
}

// 投稿編集処理 (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_post' && $post_id_to_edit > 0) {
    $new_memo = trim($_POST['memo']);
    $new_lat = $_POST['lat'];
    $new_lng = $_POST['lng'];
    $new_is_public = isset($_POST['is_public']) ? 't' : 'f';
    $old_image_filename = $_POST['old_image_filename']; // 元のファイル名

    $update_sql = "UPDATE location_diary SET memo = $1, lat = $2, lng = $3, is_public = $4";
    $params = [$new_memo, $new_lat, $new_lng, $new_is_public];
    $param_count = 4;

    // 緯度と経度のバリデーションを強化
    // 空文字列の場合はnullに変換し、数値にキャスト
    $new_lat = trim($_POST['lat']);
    $new_lng = trim($_POST['lng']);

    // nullまたは空文字列の場合はfloatとして0に、そうでなければ数値に変換
    $lat_val = ($new_lat === '' || $new_lat === null) ? 0.0 : (float)$new_lat;
    $lng_val = ($new_lng === '' || $new_lng === null) ? 0.0 : (float)$new_lng;

    if (!is_numeric($lat_val) || !is_numeric($lng_val) || $lat_val < -90 || $lat_val > 90 || $lng_val < -180 || $lng_val > 180) {
        $_SESSION['error'] = "無効な位置情報が送信されました。緯度・経度は数値で-90から90、-180から180の範囲で入力してください。";
    } else if (isset($_FILES['new_file_data']) && $_FILES['new_file_data']['size'] > 0) {
        // 新しい画像がアップロードされた場合
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($file_info, $_FILES['new_file_data']['tmp_name']);
        finfo_close($file_info);

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "許可されていないファイル形式です。";
        } else {
            // 最大ファイルサイズのチェック (例: 5MB)
            $max_file_size = 5 * 1024 * 1024; // 5 MB
            if ($_FILES['new_file_data']['size'] > $max_file_size) {
                $_SESSION['error'] = "ファイルサイズが大きすぎます（最大5MB）。";
            } else {
                $new_filename = time() . '_' . getmypid() . '.' . pathinfo($_FILES['new_file_data']['name'], PATHINFO_EXTENSION);
                $upload_path = "uploads/" . $new_filename;

                if (move_uploaded_file($_FILES['new_file_data']['tmp_name'], $upload_path)) {
                    // 古い画像を削除 (安全のため、ファイルが存在するか確認)
                    if (!empty($old_image_filename) && file_exists("uploads/" . $old_image_filename)) {
                        unlink("uploads/" . $old_image_filename);
                    }
                    $update_sql .= ", filename = $" . (++$param_count);
                    $params[] = $new_filename;
                    $post_to_edit['filename'] = $new_filename; // 表示用に更新
                } else {
                    $_SESSION['error'] = "新しい画像のアップロードに失敗しました。";
                }
            }
        }
    }

    if (empty($_SESSION['error'])) {
        $update_sql .= " WHERE id = $" . (++$param_count) . " AND user_id = $" . (++$param_count);
        $params[] = $post_id_to_edit;
        $params[] = $user_id;

        if (pg_query_params($dbconn, $update_sql, $params)) {
            $_SESSION['message'] = "投稿が更新されました！";
            // 更新後の表示用に変数を再設定
            $post_to_edit['memo'] = $new_memo;
            $post_to_edit['lat'] = $new_lat;
            $post_to_edit['lng'] = $new_lng;
            $post_to_edit['is_public'] = $new_is_public === 't' ? true : false;
            // 編集モードを終了して一覧表示に戻る
            header("Location: view.php?message=" . urlencode($_SESSION['message']));
            exit;
        } else {
            $_SESSION['error'] = "更新に失敗しました: " . pg_last_error($dbconn);
        }
    }
    header("Location: view.php?error=" . urlencode($_SESSION['error']));
    exit;
}
?>