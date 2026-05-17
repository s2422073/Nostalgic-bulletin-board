<?php
// このファイルはtemplates/post_list_table.phpからのみ読み込まれることを想定
if (!defined('VIEW_FILE_INCLUDED')) {
    die('直接アクセスは許可されていません。');
}

// このファイルは$current_post_for_comments変数がセットされていることを前提とする
if (!isset($current_post_for_comments)) {
    die('コメントセクションには投稿データが必要です。');
}

$post_id = $current_post_for_comments['id'];
?>
<div class="comments-section">
    <h4>コメント</h4>
    <ul class="comment-list">
        <?php
        // コメントを取得
        $comments_sql = "
            SELECT c.id, c.comment_text, c.created_at, u.username AS comment_author_username, u.id AS comment_author_id
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = $1
            ORDER BY c.created_at ASC;
        ";
        $comments_result = pg_query_params($dbconn, $comments_sql, [$post_id]);
        $comments = pg_fetch_all($comments_result);

        if ($comments):
            foreach ($comments as $comment):
        ?>
            <li class="comment-item">
                <span class="comment-author"><?php echo htmlspecialchars($comment['comment_author_username']); ?></span>
                <span class="comment-date"><?php echo htmlspecialchars($comment['created_at']); ?></span>
                <div class="comment-text comment-text-<?php echo htmlspecialchars($comment['id']); ?>"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></div>

                <?php if ($comment['comment_author_id'] == $user_id): // 自分のコメントのみ編集・削除可能 ?>
                    <div class="comment-actions">
                        <button class="edit-comment-button" data-comment-id="<?php echo htmlspecialchars($comment['id']); ?>" data-current-text="<?php echo htmlspecialchars($comment['comment_text']); ?>">編集</button>
                        <form method="post" style="display:inline;" onsubmit="return confirm('このコメントを本当に削除しますか？');">
                            <input type="hidden" name="action" value="delete_comment">
                            <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['id']); ?>">
                            <button type="submit" class="delete-comment-button">削除</button>
                        </form>
                    </div>
                    <div class="comment-edit-form comment-edit-form-<?php echo htmlspecialchars($comment['id']); ?>" style="display:none;">
                        <form method="post">
                            <input type="hidden" name="action" value="edit_comment">
                            <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['id']); ?>">
                            <textarea name="new_comment_text" required><?php echo htmlspecialchars($comment['comment_text']); ?></textarea><br>
                            <button type="submit">更新</button>
                            <button type="button" class="cancel-button" data-comment-id="<?php echo htmlspecialchars($comment['id']); ?>">キャンセル</button>
                        </form>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach;
        else: ?>
            <li style="color: #888;">まだコメントはありません。</li>
        <?php endif; ?>
    </ul>
    <form method="post" class="comment-form">
        <input type="hidden" name="action" value="add_comment">
        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_id); ?>">
        <textarea name="comment_text" placeholder="コメントを入力..." required></textarea>
        <button type="submit" class="comment-submit-button">コメントする</button>
    </form>
</div>