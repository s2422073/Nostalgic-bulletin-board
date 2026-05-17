<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<title>phpb12 - 地図表示</title>
</head>
<body>

<form action="./phpb12.php" method="POST">
  <label for="pname">場所の名前:</label>
  <input type="text" id="pname" name="pname" size="30" placeholder="例: 東京タワー">
  <input type="submit" value="検索">
</form>

<?php
// POSTデータがあるか確認
if (isset($_POST['pname']) && !empty($_POST['pname'])) {
    $pname = $_POST['pname'];

    // データベース接続情報 (ご自身の環境に合わせて変更してください)
    $db_host = "localhost";
    $db_name = "s2422073"; // 例: "spatial_db"
    $db_user = "s2422073";       // 例: "postgres"
    $db_pass = "TVDa8jmA";       // 例: "password123"

    $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass")
        or die('データベースに接続できませんでした: ' . pg_last_error());

    // データベースから場所を検索するSQLクエリ
    // 'name' カラムの部分一致検索
    $query = "SELECT id, name, location, cat FROM seightsee1 WHERE name LIKE '%" . pg_escape_string($pname) . "%';";
    $result = pg_query($dbconn, $query) or die('クエリの実行に失敗しました: ' . pg_last_error());

    // 検索結果を表示
    if (pg_num_rows($result) > 0) {
        echo "<h2>検索結果</h2>\n";
        echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
        echo "  <thead>\n";
        echo "    <tr><th>名前 (カテゴリ)</th><th>地図</th></tr>\n";
        echo "  </thead>\n";
        echo "  <tbody>\n";

        while ($line = pg_fetch_array($result)) {
            $loc = $line['location']; // 'location' カラムから座標を取得

            // 座標文字列の整形: 不要な括弧やスペースを除去
            // 例: "(139.78628609769672,35.63141292764219)" -> "139.78628609769672,35.63141292764219"
            $loc = str_replace(['(', ')', ' '], '', $loc);

            // カンマで分割して経度と緯度を取得
            // データベースの 'location' が (経度,緯度) 形式の場合、[0]が経度、[1]が緯度
            $point = explode(",", $loc);
            $lon = $point[0]; // 経度
            $lat = $point[1]; // 緯度

            echo "    <tr>\n";
            echo "      <td>" . htmlspecialchars($line['name']) . " (" . htmlspecialchars($line['cat']) . ")</td>\n";
            echo "      <td>\n";
            // Google Maps Embed API の iframe を生成
            // qパラメータは '緯度,経度' の順序で指定する必要がある点に注意
            echo "        <iframe\n";
            echo "          width=\"600\"\n";
            echo "          height=\"450\"\n";
            echo "          style=\"border:0\"\n";
            echo "          loading=\"lazy\"\n";
            echo "          allowfullscreen\n";
            echo "          src=\"マスク" . urlencode($lat . "," . $lon) . "\">\n";
            echo "        </iframe>\n";
            echo "      </td>\n";
            echo "    </tr>\n";
        }
        echo "  </tbody>\n";
        echo "</table>\n";
    } else {
        echo "<p>「<strong>" . htmlspecialchars($pname) . "</strong>」に一致するデータは見つかりませんでした。</p>\n";
    }

    // データベース接続を閉じる
    pg_free_result($result);
    pg_close($dbconn);
} else {
    echo "<p>検索したい場所の名前を入力してください。</p>\n";
}
?>

</body>
</html>
