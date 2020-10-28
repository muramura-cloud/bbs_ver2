<?php include(HTML_FILES_DIR . '/common/header.php') ?>

<div id="contents">
  <div class="confirmForm">
      <!-- 投稿にパスワードが設定されていなかった、あるいは、入力されてパスワードがあっていな方場合はとりあえず、投稿内容を表示する。 -->
    <?php if (empty($comment['pass']) || !$isPasswordMatch) : ?>
      <div class="comments">
        <div class="comment">
          <div class="title">
            <?php echo h($comment['title']) ?>
          </div>
          <div class="body">
            <?php echo nl2br(h($comment['body'])) ?>
          </div>
          <?php if (!empty($comment['image']) && file_exists("{$imageDir}/{$comment['image']}")) : ?>
          <div class="photo">
            <a href="<?php echo get_uri("{$imageDir}/{$comment['image']}") ?>" target="_blank">
              <img src="<?php echo $imageDir ?>/<?php echo $comment['image'] ?>" />
            </a>
          </div>
          <?php endif ?>
          <div class="date">
            <?php echo date('d-m-Y H:i', strtotime($comment['created_at'])) ?>
          </div>
        </div>
      </div>
      <!-- 投稿にパスワードが設定されていない場合は「削除できないです。」と伝える。 -->
      <?php if (empty($comment['pass'])) : ?>
        <form class="default" action="<?php echo get_uri('index.php') ?>" method="get">
          <div class="message">
            This comment can't be edited.
          </div>
          <div class="submit">
            <input type="hidden" name="page" value="<?php echo $page ?>" />
            <input type="submit" value="&raquo; BACK">
          </div>
        </form>
      <?php endif ?>
    <?php endif ?>

    <!-- 投稿にパスワードが設定されていたら、パスワードをチェックして、あってたら、編集版の投稿（$isEditForm=trueより）を表示して、間違っていたら再度パスワード入力フォームを表示。 -->
    <?php if (!empty($comment['pass'])) : ?>
      <?php if ($isPasswordMatch) : ?>
        <?php include(HTML_FILES_DIR . '/bulletin/form.php') ?>
      <?php else : ?>
        <?php include(HTML_FILES_DIR . '/common/error.php') ?>
        <form class="default" action="<?php echo get_uri('edit.php') ?>" method="post">
          <div class="submit">
            <input type="hidden" name="comment_id" value="<?php echo $id ?>" />
            <input type="hidden" name="page" value="<?php echo $page ?>" />
            <input type="password" name="pass" value="<?php echo $pass ?>" />
            <input type="submit" value="&raquo; EDIT" />
          </div>
        </form>
      <?php endif ?>
    <?php endif ?>
  </div>
</div>

<?php include(HTML_FILES_DIR . '/common/footer.php') ?>
