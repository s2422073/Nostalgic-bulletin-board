<?php
// エラー表示設定 (開発中はOn、本番環境ではOffにするのが推奨)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// データベース接続情報
// ★重要★ これらの情報は本番環境では環境変数や安全な設定ファイルから読み込むべきです
$host = 'localhost';
$db   = 's2422073'; // データベース名を適切なものに置き換えてください
$user = 's2422073'; // ユーザー名を適切なものに置き換えてください
$pass = 'TVDa8jmA'; // パスワードを適切なものに置き換えてください

$dsn = "pgsql:host=$host;dbname=$db";

try {
    // PDOインスタンスの作成
    // PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION: エラー時に例外をスローする設定
    // これにより、try-catchブロックでエラーを捕捉できるようになります
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // デフォルトの取得モードを連想配列に設定
    ]);
    // 成功メッセージ (開発時のみ、本番では不要)
    // echo "データベースに接続しました！<br>";

} catch (PDOException $e) {
    // データベース接続失敗時の処理
    // ★重要★ 本番環境では詳細なエラーメッセージをユーザーに表示せず、ログに記録する
    echo 'データベース接続失敗: ' . $e->getMessage();
    exit; // 処理を停止
}
?>