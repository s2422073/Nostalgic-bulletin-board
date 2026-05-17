<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>phpb10_modern</title>
<style>
    table, th, td {
        border: 1px solid #ccc;
        border-collapse: collapse;
        padding: 8px;
    }
    th {
        background-color: #f2f2f2;
    }
</style>
</head>
<body>
<h1>GPSデータの取得と距離計算</h1>
<script type="text/javascript">
    // HTML5 Geolocation API を使用
    // ★重要★ この機能はHTTPS接続でのみ動作します
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            // 成功時のコールバック関数
            function (pos) {
                var latitude = pos.coords.latitude;
                var longitude = pos.coords.longitude;
                var locationHtml = "<li>緯度:" + latitude + "</li>";
                locationHtml += "<li>経度:" + longitude + "</li>";
                document.getElementById("loc").innerHTML = locationHtml;
                document.getElementById("lat").value = latitude;
                document.getElementById("lng").value = longitude;
            },
            // 失敗時のコールバック関数
            function (error) {
                var errorMessage;
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = "位置情報の利用が許可されませんでした。";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = "位置情報が取得できませんでした。";
                        break;
                    case error.TIMEOUT:
                        errorMessage = "位置情報の取得がタイムアウトしました。";
                        break;
                    case error.UNKNOWN_ERROR:
                        errorMessage = "不明なエラーが発生しました。";
                        break;
                    default:
                        errorMessage = "位置情報が取得できませんでした。";
                }
                document.getElementById("loc").innerHTML = "<li>" + errorMessage + "</li>";
                console.error("Geolocation Error:", error);
            }
        );
    } else {
        document.getElementById("loc").innerHTML = "<li>本ブラウザではGeolocationが使えません。</li>";
        alert("本ブラウザではGeolocationが使えません");
    }
</script>
<ul id="loc"></ul>
<form action="./phpb10_modern.php" method="POST">
    <input type="hidden" name="lat" id="lat">
    <input type="hidden" name="lng" id="lng">
    <input type="submit" value="現在地から検索!">
</form>

<?php
require_once 'db_config.php'; // データベース接続設定を読み込み

if (isset($_POST['lat']) && $_POST['lat'] !== '' && isset($_POST['lng']) && $_POST['lng'] !== '') {
    $current_lat = $_POST['lat'];
    $current_lng = $_POST['lng'];

    // 緯度・経度が数値であることを確認
    if (!is_numeric($current_lat) || !is_numeric($current_lng)) {
        echo "<p>無効な位置情報が送信されました。</p>";
        exit;
    }

    try {
        // ★★★ SQLインジェクション対策済み！ ★★★
        // PostGISのST_Distance関数とST_SetSRID, ST_MakePointを使用
        // ST_MakePoint(経度, 緯度) の順序に注意
        // locationカラムがgeography(Point, 4326)型であると仮定
        $query = "
            SELECT
                id,
                name,
                ST_AsText(location) AS location_wkt, -- GEOGRAPHY型をWKTテキスト形式で取得
                cat,
                ST_Distance(location, ST_SetSRID(ST_MakePoint(?, ?)::geography, 4326)) AS distance_meters
            FROM
                seightsee1
            ORDER BY
                distance_meters;
        ";
        $stmt = $pdo->prepare($query);
        // パラメータはfloat型としてバインド
        $stmt->execute([ (float)$current_lng, (float)$current_lat ]);

        if ($stmt->rowCount() > 0) {
            echo "<h2>現在地 (緯度: " . htmlspecialchars($current_lat) . ", 経度: " . htmlspecialchars($current_lng) . ") からの距離</h2>\n";
            echo "<table>\n";
            echo "\t<tr>\n";
            echo "\t\t<th>ID</th>\n";
            echo "\t\t<th>名前</th>\n";
            echo "\t\t<th>カテゴリ</th>\n";
            echo "\t\t<th>距離 (メートル)</th>\n";
            echo "\t\t<th>ナビ</th>\n";
            echo "\t</tr>\n";

            while ($line = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $location_wkt = $line['location_wkt']; // 例: 'POINT(139.753 35.689)'

                $nav_url = '#';
                if (preg_match('/POINT\(([\d\.-]+)\s+([\d\.-]+)\)/', $location_wkt, $matches)) {
                    // WKTから経度と緯度を抽出
                    $longitude_point = $matches[1];
                    $latitude_point = $matches[2];
                    // Apple Mapsのq=緯度,経度 の形式に合わせる
                    $nav_url = 'http://maps.apple.com/maps?q=' . urlencode($latitude_point) . ',' . urlencode($longitude_point);
                }

                echo "\t<tr>\n";
                echo "\t\t<td>" . htmlspecialchars($line['id']) . "</td>\n";
                echo "\t\t<td>" . htmlspecialchars($line['name']) . "</td>\n";
                echo "\t\t<td>" . htmlspecialchars($line['cat']) . "</td>\n";
                // 距離を小数点以下2桁で表示
                echo "\t\t<td>" . htmlspecialchars(sprintf("%.2f", $line['distance_meters'])) . "</td>\n";
                echo "\t\t<td><button type=\"button\" onclick=\"location.href='" . htmlspecialchars($nav_url) . "'\">ナビ起動</button></td>\n";
                echo "\t</tr>\n";
            }
            echo "</table>\n";
        } else {
            echo "<p>近くの場所が見つかりませんでした。</p>";
        }

    } catch (PDOException $e) {
        echo '<p>距離計算エラー: ' . $e->getMessage() . '</p>';
    }
} else {
    echo "<p>位置情報を取得して「現在地から検索！」ボタンを押してください。<br>（ブラウザによっては、このページをHTTPSで開く必要があります）</p>";
}
?>
</body>
</html>