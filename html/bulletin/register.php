<?php include(HTML_FILES_DIR . '/common/header.php') ?>

<!-- <?php $_action = (isset($pre_registered)) ? 'register.php' : 'pre_register.php' ?> -->

<div id="contents">
    <h2>会員登録</h2>
    <?php if (empty($errors) && !empty($do_confirm) && $do_confirm) : ?>
        <div class="confirm">
            <table>
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td><?php echo h($name) ?></td>
                    </tr>
                    <tr>
                        <th>メール</th>
                        <td><?php echo h($email) ?></td>
                    </tr>
                    <tr>
                        <th>パスワード</th>
                        <td><?php echo h($pass) ?></td>
                    </tr>
                </tbody>
            </table>
            <form class="actionForm" action="pre_register.php" method="post">
                <input type="hidden" name="name" value="<?php echo $name ?>" />
                <input type="hidden" name="email" value="<?php echo $email ?>" />
                <input type="hidden" name="pass" value="<?php echo $pass ?>" />
                <input type="hidden" name="page" value="<?php echo $page ?>" />
                <div class="submit">
                    <input type="hidden" name="pre_register" value="1" />
                    <input type="submit" value="&raquo; 登録" />
                    <input type="button" value="&raquo; 戻る" onclick="window.location.href='<?php echo get_uri('index.php') ?>?page=<?php echo $page ?>';">
                </div>
            </form>
        </div>
    <?php elseif (empty($errors) && !empty($pre_register) && $pre_register) : ?>
        <div class="comment">
            <p>会員登録ありがとうございます。まだ登録は完了していません。<br>確認メールをお送りします。確認URLをクリックして登録を完了してください。</p>
            <div class="submit">
                <input type="button" value="&raquo; TOPページへ戻る" onclick="window.location.href='<?php echo get_uri('index.php') ?>?page=<?php echo $page ?>';">
            </div>
        </div>
    <?php elseif (empty($errors) && $registered) : ?>
        <div class="comment">
            <p>会員登録が完了しました。</p>
            <div class="submit">
                <input type="button" value="&raquo; TOPページへ戻る" onclick="window.location.href='<?php echo get_uri('index.php') ?>?page=<?php echo $page ?>';">
            </div>
        </div>
    <?php else : ?>
        <?php include(HTML_FILES_DIR . '/common/error.php') ?>
        <form class="default" action="<?php echo get_uri('pre_register.php') ?>" method="post">
            <div class="item">
                <p class="title">名前</p>
                <p class="input"><input type="text" name="name" value="<?php if(!empty($name)) echo h($name) ?>" /></p>
            </div>
            <div class="item">
                <p class="title">メール</p>
                <p class="email"><input type="text" name="email" value="<?php if(!empty($email)) echo h($email) ?>" /></p>
            </div>
            <div class="item">
                <p class="title">パスワード</p>
                <p class="input"><input type="password" name="pass" value="<?php if(!empty($pass)) echo h($pass) ?>" /></p>
            </div>
            <div class="submit">
                <input type="hidden" name="do_confirm" value="1" />
                <input type="submit" value="&raquo; 確認" />
                <input type="button" value="&raquo; 戻る" onclick="window.location.href='<?php echo get_uri('index.php') ?>?page=<?php echo $page ?>';">
            </div>
        </form>
    <?php endif ?>
</div>

<?php include(HTML_FILES_DIR . '/common/footer.php') ?>
