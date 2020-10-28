<?php include(HTML_FILES_DIR . '/common/header.php') ?>

<div id="contents">
    <h2>ログイン</h2>

    <?php include(HTML_FILES_DIR . '/common/error.php') ?>

    <form class="default" action="<?php echo get_uri('login.php') ?>" method="post">
        <div class="item">
            <p class="title">メール</p>
            <p class="email"><input type="text" name="email" value="<?php echo h($email) ?>" /></p>
        </div>
        <div class="item">
            <p class="title">パスワード</p>
            <p class="input"><input type="password" name="pass" value="<?php echo h($pass) ?>" /></p>
        </div>
        <div class="submit">
            <input type="hidden" name="do_login" value="1" />
            <input type="submit" value="&raquo; ログイン" />
            <input type="button" value="&raquo; 戻る" onclick="window.location.href='<?php echo get_uri('index.php') ?>?page=<?php echo $page ?>';">
        </div>
    </form>
</div>

<?php include(HTML_FILES_DIR . '/common/footer.php') ?>
