<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<form enctype="multipart/form-data" action="gupload.php" method="post">
  タイトル:<input type="text" name="title"><br />
  <input type="file" name="file_data">
  <input type="submit" name="FILE 送信" value="FILE 送信">
</form>

<?php
$dbconn = pg_connect("host=localhost dbname=s2422073 user=s2422073 password=TVDa8jmA")
  or die('Could not connect: ' . pg_last_error());

// アップロードファイル情報を表示する。
if (isset($_FILES['file_data'])) {
  echo "アップロードファイル名 : " , $_FILES["file_data"]["name"] , "<br>";
  echo "MIME タイプ: " , $_FILES["file_data"]["type"] , "<br>";
  echo "ファイルサイズ: " , $_FILES["file_data"]["size"] , "<br>";
  echo "テンポラリファイル名: " , $_FILES["file_data"]["tmp_name"] , "<br>";
  echo "エラーコード: " , $_FILES["file_data"]["error"] , "<br>";

  $nfn = time() . "_" . getmypid() . "." .
         pathinfo($_FILES["file_data"]["name"], PATHINFO_EXTENSION);

  // アップロードファイルを格納するファイルパスを指定
  // uploadsフォルダはパーミッション777にすること
  $filename = "uploads/" . $nfn;

  if ($_FILES["file_data"]["size"] === 0) {
    echo "ファイルはアップロードされてません! アップロードファイルを指定してください。";
  } else {
    $result = @move_uploaded_file($_FILES["file_data"]["tmp_name"], $filename);
    if ($result === true) {
      $title = $_POST['title'];
      echo "アップロード成功 (" . $title . ")!!";

      $sql = "INSERT INTO gupload (title, filename) VALUES (
                '" . pg_escape_string($title) . "',
                '" . pg_escape_string($nfn) . "');";
      $result = pg_query($sql) or die('Query failed: ' . pg_last_error());
    } else {
      echo "アップロード失敗!!";
    }
  }
}

// アップロード済み画像の一覧表示
$sql = "SELECT filename FROM gupload ORDER BY gid DESC;";
$result = pg_query($sql) or die('Query failed: ' . pg_last_error());

echo "<table>\n";
while ($line = pg_fetch_array($result, null, PGSQL_NUM)) {
  echo "\t<tr>\n";
  foreach ($line as $col_value) {
    echo "\t\t<td><img width=\"100\" src=\"./uploads/$col_value\"></td>\n";
  }
  echo "\t</tr>\n";
}
echo "</table>\n";
?>
</body>
</html>