<?php include(HTML_FILES_DIR . '/common/header.php') ?>

<div id="contents">
    <!-- 入力フォームを表示。送信先は基本/bbs/ebine_bbs6/post.php -->
    <?php include(HTML_FILES_DIR . '/bulletin/form.php') ?>

    <?php if ($comments) : ?>
        <div class="comments">
            <?php foreach ($comments as $comment) : ?>
                <div class="comment">
                    <div class="title">
                        <?php echo h($comment['title']) ?>
                    </div>
                    <div class="body">
                        <?php echo nl2br(h($comment['body'])) ?>
                    </div>
                    <!-- 画像がディレクトリにあるかどうかを確認しているけど、俺は確認する必要ある？って聞かれたけど。後、$imageDirにはupload/bulletinが入るけどちゃんとパス取れているの？なぜ、 -->
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
                    <form class="actionForm" action="" method="post">
                        <input type="hidden" name="comment_id" value="<?php echo $comment['id'] ?>" />
                        <input type="hidden" name="page" value="<?php echo $pager->getCurrentPage() ?>" />
                        <input type="password" name="pass" value="" />
                        <div class="submit">
                            <input type="submit" value="&raquo; DELETE" formaction="delete.php" />
                            <input type="submit" value="&raquo; EDIT" formaction="edit.php" />
                        </div>
                    </form>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <?php include(HTML_FILES_DIR . '/common/pager.php') ?>
</div>

<?php include(HTML_FILES_DIR . '/common/footer.php') ?>
