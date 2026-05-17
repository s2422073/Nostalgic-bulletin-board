<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>gupload_modern</title>
<style>
    table, th, td {
        border: 1px solid #ccc;
        border-collapse: collapse;
        padding: 8px;
    }
    th {
        background-color: #f2f2f2;
    }
    img {
        max-width: 100%;
        height: auto;
        display: block; /* 画像の下に余白ができるのを防ぐ */
    }
</style>
</head>
<body>
<h1>画像アップロード</h1>
<form enctype="multipart/form-data" action="./gupload_modern.php" method="post">
    タイトル:<input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"><br />
    <input type="file" name="file_data" accept="image/*"> <input type="submit" value="ファイル送信">
</form>

<?php
require_once 'db_config.php'; // データベース接続設定を読み込み

// アップロードファイルの保存先ディレクトリ
// ★重要★ このディレクトリにはPHPが書き込み権限を持つように設定してください (例: chmod 755 uploads)
// ★重要★ Webサーバーから直接アクセス可能にしておく必要があります
$upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

// uploadsディレクトリが存在しない場合は作成
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true); // 0755 は推奨される権限
}

$upload_message = ''; // アップロード結果メッセージ

if (isset($_FILES['file_data'])) {
    $file = $_FILES['file_data'];
    $title = $_POST['title'] ?? '';

    // アップロードエラーのチェック
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $upload_message = "ファイルサイズが上限を超えています。";
                break;
            case UPLOAD_ERR_PARTIAL:
                $upload_message = "ファイルの一部しかアップロードされませんでした。";
                break;
            case UPLOAD_ERR_NO_FILE:
                $upload_message = "ファイルが選択されていません。";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $upload_message = "一時フォルダが見つかりません。";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $upload_message = "ファイルの書き込みに失敗しました。";
                break;
            case UPLOAD_ERR_EXTENSION:
                $upload_message = "PHP拡張機能によりファイルのアップロードが停止されました。";
                break;
            default:
                $upload_message = "不明なエラーが発生しました。";
        }
    } elseif ($file['size'] === 0) {
        $upload_message = "ファイルが選択されていません！アップロードファイルを指定してください。";
    } else {
        // 許可するMIMEタイプと拡張子を厳密にチェックする
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']); // ファイルの内容からMIMEタイプを判別
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($mime_type, $allowed_mimes) || !in_array($extension, $allowed_extensions)) {
            $upload_message = "許可されていないファイル形式です。画像ファイル（JPEG, PNG, GIF）のみアップロードできます。";
        } else {
            // ファイル名を安全に生成 (重複回避)
            // time() と uniqid() を組み合わせることで、よりユニークなファイル名を生成
            $unique_filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $upload_dir . $unique_filename;

            // アップロードされた一時ファイルを指定のパスに移動
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                try {
                    // ★★★ SQLインジェクション対策済み！ ★★★
                    $sql = "INSERT INTO gupload (title, filename) VALUES (?, ?);";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$title, $unique_filename]);
                    $upload_message = "アップロード成功 (" . htmlspecialchars($title) . ")!!";
                } catch (PDOException $e) {
                    $upload_message = 'データベースへの保存に失敗しました: ' . $e->getMessage();
                    // データベースへの保存が失敗した場合は、アップロードしたファイルも削除するのが望ましい
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                }
            } else {
                $upload_message = "ファイルの移動に失敗しました。uploadsディレクトリの権限を確認してください。";
            }
        }
    }
    echo "<p>" . $upload_message . "</p>";
}

// データベースから画像一覧を取得して表示
try {
    $sql = "SELECT title, filename FROM gupload ORDER BY gid DESC;";
    $stmt = $pdo->query($sql);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($images)) {
        echo "<h2>アップロードされた画像</h2>\n";
        echo "<table>\n";
        foreach ($images as $image) {
            echo "\t<tr>\n";
            // uploadsディレクトリ内の画像を表示
            // htmlspecialchars() でエスケープしてXSS対策
            echo "\t\t<td>" . htmlspecialchars($image['title']) . "</td>\n";
            echo "\t\t<td><img width=\"100\" src=\"./uploads/" . htmlspecialchars($image['filename']) . "\" alt=\"" . htmlspecialchars($image['title']) . "\"></td>\n";
            echo "\t</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>まだ画像がアップロードされていません。</p>";
    }

} catch (PDOException $e) {
    echo '<p>画像一覧の取得エラー: ' . $e->getMessage() . '</p>';
}
?>
</body>
</html>