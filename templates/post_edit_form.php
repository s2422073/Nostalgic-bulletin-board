<?php
// このファイルはview.phpからのみ読み込まれることを想定
if (!defined('VIEW_FILE_INCLUDED')) {
    die('直接アクセスは許可されていません。');
}

// このファイルは$post_to_edit変数がセットされていることを前提とする
if (!isset($post_to_edit)) {
    die('編集フォームには投稿データが必要です。');
}
?>
<h2>投稿内容を編集</h2>
<div class="edit-form-container">
    <script>
      // 編集フォームの緯度経度入力フィールドに初期値をセット
      // ただし、navigator.geolocationが利用可能なら現在の位置で上書き
      document.addEventListener('DOMContentLoaded', function() {
        const latInput = document.getElementById("lat_edit");
        const lngInput = document.getElementById("lng_edit");

        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(
            function(pos) {
              latInput.value = pos.coords.latitude;
              lngInput.value = pos.coords.longitude;
            },
            function(error) {
              let errorMessage = "位置情報の取得に失敗しました。";
              switch(error.code) {
                case error.PERMISSION_DENIED:
                  errorMessage = "位置情報の利用が許可されませんでした。手動で入力してください。";
                  break;
                case error.POSITION_UNAVAILABLE:
                  errorMessage = "位置情報の取得に失敗しました。";
                  break;
                case error.TIMEOUT:
                  errorMessage = "位置情報の取得がタイムアウトしました。";
                  break;
                case error.UNKNOWN_ERROR:
                  errorMessage = "不明なエラーが発生しました。";
                  break;
              }
              alert(errorMessage + " 現在の緯度: " + latInput.value + ", 経度: " + lngInput.value);
            }
          );
        } else {
          alert("お使いのブラウザは位置情報に対応していません。");
        }
      });
    </script>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="update_post">
      <input type="hidden" name="old_image_filename" value="<?php echo htmlspecialchars($post_to_edit['filename']); ?>">
      <div class="form-group">
        <label for="memo">メモ:</label>
        <textarea name="memo" id="memo" placeholder="今日の出来事をメモ"><?php echo htmlspecialchars($post_to_edit['memo']); ?></textarea>
      </div>
      <div class="form-group">
        <label>現在の画像:</label>
        <?php if (!empty($post_to_edit['filename']) && file_exists("uploads/" . $post_to_edit['filename'])): ?>
          <div class="current-image">
              <img src="uploads/<?php echo htmlspecialchars($post_to_edit['filename']); ?>" alt="現在の画像">
          </div>
        <?php else: ?>
          <p style="text-align: center;">画像はありません。</p>
        <?php endif; ?>
        <label for="new_file_data">新しい画像 (変更する場合のみ):</label>
        <input type="file" name="new_file_data" id="new_file_data" accept="image/*">
      </div>
      <div class="form-group">
          <label for="lat_edit">緯度:</label>
          <input type="text" name="lat" id="lat_edit" value="<?php echo htmlspecialchars((float)$post_to_edit['lat']); ?>" required>
      </div>
      <div class="form-group">
          <label for="lng_edit">経度:</label>
          <input type="text" name="lng" id="lng_edit" value="<?php echo htmlspecialchars((float)$post_to_edit['lng']); ?>" required>
      </div>
      <div class="form-group">
          <input type="checkbox" id="is_public_edit" name="is_public" <?php echo $post_to_edit['is_public'] ? 'checked' : ''; ?>>
          <label for="is_public_edit" style="display: inline;">この投稿を公開する</label>
      </div>
      <input type="submit" value="更新">
    </form>
</div>