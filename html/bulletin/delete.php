<?php include(HTML_FILES_DIR . '/common/header.php') ?>

<div id="contents">
    <?php include(HTML_FILES_DIR . '/common/error.php') ?>

    <div class="comments">
        <div class="comment">
            <?php if (!empty($comment['name'])) : ?>
                <div class="name">
                    <?php echo h($comment['name']) ?>
                </div>
            <?php endif ?>
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
                <?php echo date('Y-m-d H:i', strtotime($comment['created_at'])) ?>
            </div>
        </div>
    </div>

    <div class="confirmForm">
        <?php if (!empty($comment['pass']) || $isLogin) : ?>
            <form class="default" action="<?php echo get_uri('delete.php') ?>" method="post">
                <input type="hidden" name="comment_id" value="<?php echo $comment['id'] ?>" />
                <!-- パスワードはエラーある内にかかわらず送信するようにしている。だからここで一回パスワードの入力を指定しておけば良い。 -->
                <input type="hidden" name="page" value="<?php echo $page ?>" />
                <input type="hidden" name="pass" value="<?php echo $pass ?>" />
                <?php if (empty($errors)) : ?>
                    <div class="message">
                        本当に削除して良いですか？
                    </div>
                    <div class="submit">
                        <input type="hidden" name="do_delete" value="1" />
                        <input type="submit" value="&raquo; 削除" />
                        <input type="button" value="&raquo; 戻る" onclick="window.location.href='<?php echo get_uri('index.php') ?>?page=<?php echo $page ?>';">
                    </div>
                <?php else : ?>
                    <div class="submit">
                        <input type="password" name="pass" value="<?php echo $pass ?>" />
                        <input type="submit" value="&raquo; 削除" />
                    </div>
                <?php endif ?>
            </form>
        <?php else : ?>
            <form class="default" action="<?php echo get_uri('index.php') ?>" method="get">
                <div class="message">
                    このコメントにはパスワードが設定されていないため削除できません。
                </div>
                <div class="submit">
                    <input type="hidden" name="page" value="<?php echo $page ?>" />
                    <input type="submit" value="&raquo; 戻る">
                </div>
            </form>
        <?php endif ?>
    </div>
</div>

<?php include(HTML_FILES_DIR . '/common/footer.php') ?>
