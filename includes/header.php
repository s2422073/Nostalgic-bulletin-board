<?php
// このファイルはview.phpからのみ読み込まれることを想定
if (!defined('VIEW_FILE_INCLUDED')) {
    die('直接アクセスは許可されていません。');
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>投稿一覧・編集</title>
<style>
body {
  font-family: 'Inter', 'Noto Sans JP', sans-serif;
  background: #f0f2f5;
  padding: 20px;
  color: #333;
  line-height: 1.6;
}
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  margin-bottom: 30px;
  border-bottom: 1px solid #e0e0e0;
  flex-wrap: wrap; /* レスポンシブ対応 */
}
.header-links {
    display: flex;
    flex-wrap: wrap; /* レスポンシブ対応 */
    gap: 15px; /* リンク間のスペース */
}
.header-links a {
  margin-left: 0; /* flexboxでgapを使うため0に */
  color: #5b7e8d;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}
.header-links a:hover {
  color: #334a52;
}
h2 {
  text-align: center;
  color: #334a52;
  font-size: 2em;
  margin-bottom: 30px;
  font-weight: 700;
}
.controls {
    text-align: center;
    margin-bottom: 25px;
}
.controls a, .controls button {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s ease, color 0.3s ease;
    border: 1px solid #dcdfe6;
    background-color: #ffffff;
    color: #333;
    cursor: pointer;
}
.controls a:hover, .controls button:hover {
    background-color: #e6e6e6;
}
.controls .active {
    background-color: #5b7e8d;
    color: white;
    border-color: #5b7e8d;
}
.controls .active:hover {
    background-color: #334a52;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: #ffffff;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 30px;
}
th, td {
  padding: 15px;
  border: 1px solid #e0e6ec;
  text-align: left;
  vertical-align: top; /* コメント欄のためにtopに */
}
th {
  background-color: #334a52;
  color: white;
  font-weight: 600;
  text-align: center;
  font-size: 0.95em;
  white-space: nowrap;
}
td {
  font-size: 0.9em;
  color: #444;
}
tr:nth-child(even) {
  background-color: #f9fbfd;
}
a {
  color: #5b7e8d;
  text-decoration: none;
  transition: color 0.3s ease;
}
a:hover {
  color: #334a52;
  text-decoration: underline;
}
/* 投稿画像に特化したセレクタ */
.post-image {
  max-width: 120px;
  height: auto;
  border-radius: 4px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: transform 0.2s ease;
}
.post-image:hover {
  transform: scale(1.03);
}
.text-center {
  text-align: center;
}
.memo-cell {
  max-width: 300px;
  word-wrap: break-word;
}
.author-info {
    font-size: 0.8em;
    color: #777;
    margin-top: 5px;
    text-align: right;
}
.public-badge {
    display: inline-block;
    background-color: #28a745;
    color: white;
    font-size: 0.75em;
    padding: 3px 8px;
    border-radius: 4px;
    margin-left: 5px;
    vertical-align: middle;
}
.private-badge {
    display: inline-block;
    background-color: #6c757d;
    color: white;
    font-size: 0.75em;
    padding: 3px 8px;
    border-radius: 4px;
    margin-left: 5px;
    vertical-align: middle;
}

/* 画像拡大表示用のスタイル */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.8);
  backdrop-filter: blur(5px);
  -webkit-backdrop-filter: blur(5px);
  display: flex;
  justify-content: center;
  align-items: center;
}
.modal-content {
  max-width: 90%;
  max-height: 90%;
  display: block;
  margin: auto;
  border-radius: 8px;
  box-shadow: 0 0 20px rgba(0,0,0,0.5);
}
.close {
  position: absolute;
  top: 20px;
  right: 35px;
  color: #f1f1f1;
  font-size: 40px;
  font-weight: bold;
  transition: 0.3s;
  cursor: pointer;
}
.close:hover,
.close:focus {
  color: #bbb;
  text-decoration: none;
  cursor: pointer;
}
.action-buttons {
  display: flex;
  gap: 10px;
  justify-content: center;
}
.action-buttons a, .action-buttons form button { /* ボタンにもスタイル適用 */
  background-color: #5b7e8d;
  color: white;
  padding: 8px 15px;
  border-radius: 5px;
  text-decoration: none;
  font-size: 0.85em;
  white-space: nowrap;
  transition: background-color 0.2s ease;
  border: none; /* ボタンのデフォルトボーダーを消す */
  cursor: pointer;
}
.action-buttons a.delete-button, .action-buttons form button.delete-button {
  background-color: #dc3545;
}
.action-buttons a:hover, .action-buttons form button:hover {
  background-color: #334a52;
  text-decoration: none;
}
.action-buttons a.delete-button:hover, .action-buttons form button.delete-button:hover {
  background-color: #c82333;
}

/* 編集フォーム用のスタイル */
.edit-form-container {
  background-color: #ffffff;
  padding: 35px 45px;
  border-radius: 12px;
  max-width: 550px;
  margin: 30px auto;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
  box-sizing: border-box;
}
.edit-form-container label {
  display: block;
  margin-bottom: 8px;
  color: #555;
  font-weight: 500;
  font-size: 0.95em;
}
.edit-form-container input[type=text], .edit-form-container input[type=file], .edit-form-container textarea {
  width: calc(100% - 20px);
  padding: 12px 10px;
  margin-bottom: 20px;
  border: 1px solid #dcdfe6;
  border-radius: 6px;
  font-size: 1em;
  color: #333;
  transition: border-color 0.3s, box-shadow 0.3s;
  box-sizing: border-box;
}
.edit-form-container textarea {
    min-height: 80px;
    resize: vertical;
}
.edit-form-container input[type=file] {
  padding: 10px 10px;
}
.edit-form-container input[type=text]:focus, .edit-form-container input[type=file]:focus, .edit-form-container textarea:focus {
  border-color: #5b7e8d;
  box-shadow: 0 0 0 3px rgba(91, 126, 141, 0.2);
  outline: none;
}
.edit-form-container input[type=submit] {
  background-color: #334a52;
  color: white;
  border: none;
  padding: 12px 25px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1.1em;
  font-weight: 500;
  width: 100%;
  margin-top: 10px;
  transition: background-color 0.3s ease;
}
.edit-form-container input[type=submit]:hover {
  background-color: #273a42;
}
.edit-form-container .form-group {
  margin-bottom: 20px;
}
.current-image {
    display: block;
    margin-top: 10px;
    margin-bottom: 20px;
    text-align: center;
}
.current-image img {
    max-width: 200px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.message-box {
    padding: 10px;
    margin: 10px auto;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
    max-width: 550px;
}
.message-box.success-message { /* 成功メッセージ */
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.message-box.error-message { /* エラーメッセージ */
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* ページネーションスタイル */
.pagination {
    text-align: center;
    margin-top: 20px;
    margin-bottom: 40px; /* 下にスペースを追加 */
}
.pagination a, .pagination span {
    display: inline-block;
    padding: 8px 14px;
    margin: 0 4px;
    border: 1px solid #dcdfe6;
    border-radius: 5px;
    text-decoration: none;
    color: #5b7e8d;
    background-color: #ffffff;
    transition: background-color 0.2s ease, color 0.2s ease;
}
.pagination a:hover {
    background-color: #eef2f6;
    color: #334a52;
}
.pagination .current-page {
    background-color: #334a52;
    color: white;
    border-color: #334a52;
    font-weight: bold;
    cursor: default;
}

/* コメントセクションのスタイル */
.comments-section {
    margin-top: 15px;
    border-top: 1px solid #e0e6ec;
    padding-top: 15px;
}
.comment-form textarea {
    width: calc(100% - 20px);
    min-height: 60px;
    padding: 8px;
    border: 1px solid #dcdfe6;
    border-radius: 5px;
    margin-bottom: 10px;
    box-sizing: border-box;
    resize: vertical;
}
.comment-form button {
    background-color: #5cb85c;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9em;
    transition: background-color 0.2s ease;
}
.comment-form button:hover {
    background-color: #4cae4c;
}
.comment-list {
    list-style: none;
    padding: 0;
    margin-top: 15px;
}
.comment-item {
    background-color: #f9fbfd;
    border: 1px solid #e0e6ec;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    font-size: 0.85em;
    position: relative; /* 編集・削除ボタンの位置調整用 */
}
.comment-author {
    font-weight: bold;
    color: #334a52;
}
.comment-date {
    font-size: 0.75em;
    color: #777;
    margin-left: 10px;
}
.comment-text {
    margin-top: 5px;
    margin-bottom: 10px;
    color: #555;
    word-wrap: break-word;
}
.comment-actions {
    position: absolute;
    top: 10px;
    right: 10px;
}
.comment-actions button {
    background: none;
    border: none;
    color: #5b7e8d;
    cursor: pointer;
    font-size: 0.8em;
    margin-left: 8px;
    text-decoration: underline;
    transition: color 0.2s ease;
    padding: 0; /* ボタンのパディングを削除 */
}
.comment-actions button:hover {
    color: #334a52;
}
.comment-actions button.delete-comment-button {
    color: #dc3545;
}
.comment-actions button.delete-comment-button:hover {
    color: #c82333;
}
.comment-edit-form {
    margin-top: 10px;
    border-top: 1px dashed #e0e6ec;
    padding-top: 10px;
}
.comment-edit-form textarea {
    width: calc(100% - 20px);
    min-height: 60px;
    padding: 8px;
    border: 1px solid #dcdfe6;
    border-radius: 5px;
    margin-bottom: 10px;
    box-sizing: border-box;
    resize: vertical;
}
.comment-edit-form button {
    background-color: #007bff;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9em;
    transition: background-color 0.2s ease;
}
.comment-edit-form button:hover {
    background-color: #0056b3;
}
.comment-edit-form button.cancel-button {
    background-color: #6c757d;
    margin-left: 10px;
}
.comment-edit-form button.cancel-button:hover {
    background-color: #5a6268;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    body {
        padding: 10px;
    }
    header {
        flex-direction: column;
        align-items: flex-start;
    }
    .header-links {
        margin-top: 10px;
        gap: 10px;
    }
    h2 {
        font-size: 1.6em;
        margin-bottom: 20px;
    }
    table, tbody, thead, tr, th, td {
        display: block; /* テーブルをブロック要素化 */
        width: 100%;
    }
    thead {
        display: none; /* ヘッダーを非表示 */
    }
    tr {
        margin-bottom: 15px;
        border: 1px solid #e0e6ec;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    td {
        border: none;
        border-bottom: 1px solid #e0e6ec;
        position: relative;
        padding-left: 100px; /* ラベル用のスペース */
        text-align: right;
    }
    td:last-child {
        border-bottom: none;
    }
    td:before {
        content: attr(data-label); /* data-label属性を疑似要素で表示 */
        position: absolute;
        left: 10px;
        width: 85px;
        padding-right: 10px;
        white-space: nowrap;
        text-align: left;
        font-weight: bold;
        color: #555;
    }
    td.text-center {
        text-align: right;
    }
    td.action-buttons {
        text-align: center;
        padding-left: 15px;
    }
    .edit-form-container {
        padding: 25px 30px;
    }
    .edit-form-container input[type=text], .edit-form-container input[type=file], .edit-form-container textarea {
        font-size: 0.9em;
        padding: 10px;
    }
    .comment-list {
        margin-top: 10px;
    }
    .comment-item {
        padding: 10px;
        margin-bottom: 8px;
    }
    .comment-actions {
        position: static; /* レスポンシブで位置を調整 */
        display: block;
        text-align: right;
        margin-top: 5px;
    }
}
</style>
</head>
<body>
<header>
  <h1>あなたの投稿</h1>
  <div class="header-links">
    <a href='index.php'>← 投稿へ戻る</a>
    <?php if (isset($post_id_to_edit) && $post_id_to_edit > 0): // 編集モードの場合 ?>
      <a href='view.php'>← 投稿一覧へ</a>
    <?php endif; ?>
    <a href='logout.php'>ログアウト</a>
  </div>
</header>