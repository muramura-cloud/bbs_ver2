<?php include(HTML_FILES_DIR . '/common/header.php') ?>

<div id="contents">
    <!-- 後、これページ番号送り方がちょっと変かも。 -->
    <div class="login_register_btns">
        <?php if ($isLogin) : ?>
            <button><a href='logout.php?page=<?php echo $page ?>'>ログアウト</a></button>
        <?php else : ?>
            <button><a href='login.php?page=<?php echo $page ?>'>ログイン</a></button>
            <button><a href='pre_register.php?page=<?php echo $page ?>'>新規登録</a></button>
        <?php endif ?>
    </div>

    <!-- 入力フォームを表示。送信先は基本/bbs/ebine_bbs6/post.php -->
    <?php include(HTML_FILES_DIR . '/bulletin/form.php') ?>

    <?php if ($comments) : ?>
        <div class="comments">
            <?php foreach ($comments as $comment) : ?>
                <div class="comment">
                    <div class="name">
                        <!-- 名前がセットされていたら表示する。 -->
                        <?php if (!empty($comment['name'])) : ?>
                            <?php echo h($comment['name']) ?>
                        <?php else : ?>
                            名前無し
                        <?php endif ?>
                        : <?php echo h($comment['user_id']) ?>
                    </div>
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
                    <!-- ここら辺の条件分岐をもっとわかりやすくしたい。 -->
                    <form class="actionForm" action="" method="post">
                        <input type="hidden" name="comment_id" value="<?php echo $comment['id'] ?>" />
                        <input type="hidden" name="page" value="<?php echo $pager->getCurrentPage() ?>" />
                        <?php if ($isLogin) : ?>
                            <?php if ($user_id === $comment['user_id']) : ?>
                                <div class="submit">
                                    <input type="submit" value="&raquo; 削除" formaction="delete.php" />
                                    <input type="submit" value="&raquo; 編集" formaction="edit.php" />
                                </div>
                            <?php endif ?>
                        <?php else : ?>
                            <?php if (empty($comment['user_id'])) : ?>
                                <input type="password" name="pass" value="" />
                                <div class="submit">
                                    <input type="submit" value="&raquo; 削除" formaction="delete.php" />
                                    <input type="submit" value="&raquo; 編集" formaction="edit.php" />
                                </div>
                            <?php endif  ?>
                        <?php endif ?>
                    </form>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <?php include(HTML_FILES_DIR . '/common/pager.php') ?>
</div>

<?php include(HTML_FILES_DIR . '/common/footer.php') ?>
