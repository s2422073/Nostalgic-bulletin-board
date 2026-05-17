<?php
// このファイルはview.phpからのみ読み込まれることを想定
if (!defined('VIEW_FILE_INCLUDED')) {
    die('直接アクセスは許可されていません。');
}
?>
<?php if ($posts): ?>
    <table>
      <thead>
        <tr>
          <th data-label="ID">ID</th>
          <th data-label="メモ">メモ</th>
          <th data-label="画像">画像</th>
          <th data-label="日時">日時</th>
          <th data-label="公開状態">公開状態</th>
          <th data-label="ナビ">ナビ</th>
          <th data-label="操作">操作</th>
          <th data-label="コメント">コメント</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
            <tr>
                <td data-label="ID" class="text-center"><?php echo htmlspecialchars($post['id']); ?></td>
                <td data-label="メモ" class="memo-cell">
                    <?php echo nl2br(htmlspecialchars($post['memo'])); ?>
                    <div class="author-info">投稿者: <?php echo htmlspecialchars($post['author_username']); ?></div>
                </td>
                <td data-label="画像" class="text-center">
                    <?php if (!empty($post['filename']) && file_exists("uploads/" . $post['filename'])): ?>
                        <img src='uploads/<?php echo htmlspecialchars($post['filename']); ?>' data-src='uploads/<?php echo htmlspecialchars($post['filename']); ?>' alt='投稿画像' class="post-image">
                    <?php else: ?>
                        画像なし
                    <?php endif; ?>
                </td>
                <td data-label="日時" class="text-center"><?php echo htmlspecialchars($post['created_at']); ?></td>
                <td data-label="公開状態" class="text-center">
                    <?php if ($post['is_public'] === 't'): ?>
                        <span class="public-badge">公開</span>
                    <?php else: ?>
                        <span class="private-badge">非公開</span>
                    <?php endif; ?>
                </td>
                <td data-label="ナビ" class="text-center">
                    <a href='https://maps.apple.com/maps?q=<?php echo htmlspecialchars($post['lat']); ?>,<?php echo htmlspecialchars($post['lng']); ?>' target='_blank'>ナビ起動</a>
                </td>
                <td data-label="操作" class="text-center action-buttons">
                    <?php if ($post['author_id'] == $user_id): // 自分の投稿のみ編集・削除可能 ?>
                        <a href='view.php?edit_id=<?php echo htmlspecialchars($post['id']); ?>'>編集</a>
                        <form action="delete.php" method="post" onsubmit="return confirm('本当にこの投稿を削除しますか？');" style="display:inline;">
                            <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id']); ?>">
                            <button type="submit" class="delete-button" style="background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 0.9em; margin-left: 5px;">削除</button>
                        </form>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td data-label="コメント">
                    <?php
                    // コメントセクションを読み込む
                    // $post 変数を comment_section.php で利用するために渡す
                    $current_post_for_comments = $post;
                    require 'templates/comment_section.php';
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php
                $page_url_params = [
                    'page' => $i,
                    'mode' => $display_mode,
                ];
                if ($search_query) {
                    $page_url_params['search'] = $search_query;
                }
                $page_url = '?' . http_build_query($page_url_params);
            ?>
            <?php if ($i == $current_page): ?>
                <span class="current-page"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars($page_url); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
<?php else: ?>
    <p style="text-align: center;">まだ投稿がありません。または、検索条件に一致する投稿が見つかりませんでした。</p>
<?php endif; ?>