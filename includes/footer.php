<script>
// DOMContentLoadedイベントで全てのスクリプトを実行
document.addEventListener('DOMContentLoaded', function() {

  // ===============================================
  // 画像拡大表示機能
  // ===============================================
  const modal = document.getElementById('imageModal');
  const modalImage = document.getElementById('modalImage');
  const closeBtn = document.querySelector('.close');

  // .post-image クラスを持つ画像のみを対象にイベントリスナーを設定
  // 動的に追加される要素にも対応するため、イベント委譲を使用
  document.body.addEventListener('click', function(e) {
    if (e.target && e.target.matches('.post-image[data-src]')) {
      // クリックされた要素が .post-image で data-src を持つ場合のみ処理
      modal.style.display = 'flex';
      modalImage.src = e.target.getAttribute('data-src');
    }
  });

  if (closeBtn) { // closeBtnが存在することを確認
    closeBtn.addEventListener('click', function() {
      modal.style.display = 'none';
    });
  }

  if (modal) { // modalが存在することを確認
    modal.addEventListener('click', function(e) {
      if (e.target === modal) { // モーダルの背景をクリックした場合のみ閉じる
        modal.style.display = 'none';
      }
    });
  }

  document.addEventListener('keydown', function(e) {
    // Escキーが押され、かつモーダルが表示されている場合のみ閉じる
    if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
      modal.style.display = 'none';
    }
  });


  // ===============================================
  // URLパラメータからのメッセージ表示
  // ===============================================
  const urlParams = new URLSearchParams(window.location.search);
  const messageParam = urlParams.get('message');
  const errorParam = urlParams.get('error');
  const messageBoxSuccess = document.querySelector('.message-box.success-message');
  const messageBoxError = document.querySelector('.message-box.error-message');

  // 成功メッセージの表示とURLパラメータ削除
  if (messageParam && messageBoxSuccess) {
    messageBoxSuccess.textContent = decodeURIComponent(messageParam);
    messageBoxSuccess.style.display = 'block';
    urlParams.delete('message');
    history.replaceState(null, '', '?' + urlParams.toString());
  } else if (messageBoxSuccess) {
    messageBoxSuccess.style.display = 'none';
  }

  // エラーメッセージの表示とURLパラメータ削除
  if (errorParam && messageBoxError) {
    messageBoxError.textContent = decodeURIComponent(errorParam);
    messageBoxError.style.display = 'block';
    urlParams.delete('error');
    history.replaceState(null, '', '?' + urlParams.toString());
  } else if (messageBoxError) {
    messageBoxError.style.display = 'none';
  }

  // ===============================================
  // コメント編集機能
  // ===============================================
  // 動的に生成される可能性のあるボタンにも対応するため、イベント委譲を使用
  document.body.addEventListener('click', function(e) {
    if (e.target && e.target.matches('.edit-comment-button')) {
        const button = e.target;
        const commentId = button.dataset.commentId;
        const currentTextElement = document.querySelector(`.comment-text-${commentId}`);
        const deleteForm = button.nextElementSibling;
        const editForm = document.querySelector(`.comment-edit-form-${commentId}`);

        if (currentTextElement) currentTextElement.style.display = 'none';
        button.style.display = 'none';
        if (deleteForm) deleteForm.style.display = 'none';
        if (editForm) editForm.style.display = 'block';
    } else if (e.target && e.target.matches('.cancel-button')) {
        const button = e.target;
        const commentId = button.dataset.commentId;
        const currentTextElement = document.querySelector(`.comment-text-${commentId}`);
        const editButton = document.querySelector(`.edit-comment-button[data-comment-id="${commentId}"]`);
        const deleteForm = (editButton && editButton.nextElementSibling) ? editButton.nextElementSibling : null; // editButtonが存在しない場合も考慮
        const editForm = document.querySelector(`.comment-edit-form-${commentId}`);

        if (currentTextElement) currentTextElement.style.display = 'block';
        if (editButton) editButton.style.display = 'inline';
        if (deleteForm) deleteForm.style.display = 'inline';
        if (editForm) editForm.style.display = 'none';
    }
  });

}); // DOMContentLoaded 終了
</script>