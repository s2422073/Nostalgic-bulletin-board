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

$user_id = $_SESSION['user_id'];
$error = ''; // エラーメッセージを保持する変数

// POSTリクエストであるかを確認
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTデータの取得 (null合体演算子 ?? を使用し、未定義の場合も空文字列に)
    $memo = trim($_POST['memo'] ?? ''); // メモは必須ではなくなった
    $lat_str = trim($_POST['lat'] ?? '');
    $lng_str = trim($_POST['lng'] ?? '');
    // is_publicチェックボックスは、チェックされていれば"1"がPOSTされる。なければPOSTされない。
    // 't'/'f'はPostgreSQLのBOOLEAN型に対応
    $is_public = isset($_POST['is_public']) ? 't' : 'f';

    $uploaded_filename_for_db = null; // データベースに保存するファイル名を初期化

    // === 1. メモのバリデーション (必須ではなくなったため、ここのチェックは不要) ===
    // if (empty($memo)) {
    //     $error = "メモは必須です。";
    // }

    // === 2. 位置情報のバリデーションと型変換 ===
    // 前段でエラーがなければ処理を続行
    if (empty($error)) { // ここはメモのバリデーションがなくなったため、実質的に常に実行される
        // filter_varを使用し、より安全に数値をチェック
        // filter_varは数値でない場合はfalseを返す
        $lat = filter_var($lat_str, FILTER_VALIDATE_FLOAT);
        $lng = filter_var($lng_str, FILTER_VALIDATE_FLOAT);

        // 数値として無効 (false)、または範囲外の場合
        if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            $error = "無効な位置情報が送信されました。緯度・経度は数値で-90から90、-180から180の範囲で入力してください。";
            // 非表示フィールドなので、GPS取得に失敗した場合にアラートで知らせる
            error_log("Invalid geolocation data received from client: lat=$lat_str, lng=$lng_str");
        }
    }

    // === 3. ファイルアップロード処理 ===
    // 前段のバリデーションにエラーがなく、かつファイルが送信された場合のみ処理
    // $_FILES['file_data']['error'] == UPLOAD_ERR_NO_FILE はファイルが選択されなかった場合
    if (empty($error) && isset($_FILES['file_data']) && $_FILES['file_data']['error'] != UPLOAD_ERR_NO_FILE) {
        // UPLOAD_ERR_OK (0) はファイルが正常にアップロードされたことを示す
        if ($_FILES['file_data']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

            // MIMEタイプチェックをfinfo_openで厳密に行う (推奨される方法)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) { // finfo_openが成功したかチェック
                $file_type = finfo_file($finfo, $_FILES['file_data']['tmp_name']);
                finfo_close($finfo);
            } else {
                // finfo_openが失敗した場合のフォールバック (ただし、セキュリティは低下)
                $file_type = 'unknown'; // 不明なタイプとして扱う
                error_log("finfo_open failed. Cannot verify MIME type accurately.");
            }

            if (!in_array($file_type, $allowed_types)) {
                $error = "許可されていないファイル形式です。JPEG, PNG, GIFのみアップロード可能です。";
            } else {
                // 最大ファイルサイズのチェック (例: 5MB)
                $max_file_size = 5 * 1024 * 1024; // 5 MB
                if ($_FILES['file_data']['size'] > $max_file_size) {
                    $error = "ファイルサイズが大きすぎます（最大5MB）。";
                } else {
                    // 安全なファイル名を生成 (タイムスタンプ + プロセスID + 元の拡張子)
                    $filename_base = time() . '_' . getmypid();
                    $file_extension = pathinfo($_FILES['file_data']['name'], PATHINFO_EXTENSION);
                    $new_filename = $filename_base . '.' . $file_extension;
                    $upload_path = "uploads/" . $new_filename; // uploadsディレクトリは事前に作成しておく

                    if (!move_uploaded_file($_FILES['file_data']['tmp_name'], $upload_path)) {
                        $error = "ファイルのアップロードに失敗しました。";
                        // 詳細なエラーログ（サーバーのログファイルに出力される）
                        error_log("Failed to move uploaded file: " . $_FILES['file_data']['tmp_name'] . " to " . $upload_path);
                        error_log("Upload error code: " . $_FILES['file_data']['error']);
                    } else {
                        $uploaded_filename_for_db = $new_filename; // DBに保存するファイル名をセット
                    }
                }
            }
        } else {
            // UPLOAD_ERR_OK 以外の場合のファイルアップロードエラー
            // ユーザーフレンドリーなエラーメッセージに変換
            $upload_error_codes = [
                UPLOAD_ERR_INI_SIZE   => 'アップロードされたファイルは、サーバーの最大ファイルサイズを超えています。',
                UPLOAD_ERR_FORM_SIZE  => 'アップロードされたファイルは、フォームで指定された最大サイズを超えています。',
                UPLOAD_ERR_PARTIAL    => 'ファイルの一部しかアップロードされませんでした。',
                // UPLOAD_ERR_NO_FILE はここで処理しない (ファイルが任意のため)
                UPLOAD_ERR_NO_TMP_DIR => '一時保存フォルダが見つかりません。サーバー管理者に連絡してください。',
                UPLOAD_ERR_CANT_WRITE => 'ファイルの保存に失敗しました。サーバーに書き込み権限がありません。',
                UPLOAD_ERR_EXTENSION  => 'ファイルのアップロードが拡張モジュールによって停止されました。'
            ];
            $error = "ファイルのアップロード中にエラーが発生しました: " . ($upload_error_codes[$_FILES['file_data']['error']] ?? '不明なエラーコード ' . $_FILES['file_data']['error']);
        }
    }

    // === 4. データベースへの挿入処理 ===
    // ここまでのバリデーションとファイルアップロード処理でエラーがなければDBに挿入
    if (empty($error)) {
        $sql = "INSERT INTO location_diary (user_id, memo, filename, lat, lng, is_public) VALUES ($1, $2, $3, $4, $5, $6)";
        $params = [$user_id, $memo, $uploaded_filename_for_db, $lat, $lng, $is_public];

        $res = pg_query_params($dbconn, $sql, $params);

        if ($res) {
            $_SESSION['message'] = "投稿が完了しました！";
            header("Location: view.php"); // 投稿完了後は一覧ページへリダイレクト
            exit;
        } else {
            $error = "投稿に失敗しました: " . pg_last_error($dbconn);
            // データベース挿入失敗時にアップロードされたファイルを削除 (任意だが推奨)
            if ($uploaded_filename_for_db && file_exists("uploads/" . $uploaded_filename_for_db)) {
                unlink("uploads/" . $uploaded_filename_for_db);
            }
        }
    }

    // === 5. エラーがある場合はindex.phpに戻す ===
    if (!empty($error)) {
        $_SESSION['error'] = $error; // エラーメッセージをセッションに保存
        header("Location: index.php"); // index.phpへリダイレクト
        exit;
    }

} else {
    // POST以外のリクエストの場合は不正アクセスとしてindex.phpにリダイレクト
    header("Location: index.php");
    exit;
}
?>