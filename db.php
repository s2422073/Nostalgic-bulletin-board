<?php
// セッション開始（すべてのページで必要）
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// DB接続
$host = 'localhost';
$db   = 's2422073';
$user = 's2422073';
$pass = 'TVDa8jmA';

$dbconn = pg_connect("host=$host dbname=$db user=$user password=$pass")
    or die('Could not connect: ' . pg_last_error());
?>
