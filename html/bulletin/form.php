<!-- 投稿ページに追加的に編集ページを組み込んでいる。 -->
<?php include(HTML_FILES_DIR . '/common/error.php') ?>

<?php $_action = (isset($isEditForm)) ? 'edit.php' : 'post.php' ?>

<!-- /bbs/ebine_bbs6/edit.php -->
<form class="default" action="<?php echo get_uri($_action) ?>" method="post" enctype="multipart/form-data">
    <div class="item">
        <p class="title">
            名前 (任意)
        </p>
        <p class="input">
            <?php if ($isLogin && !isset($isEditForm)) : ?>
                <input type="text" name="name" value="<?php echo h($user_name) ?>" />
            <?php else : ?>
                <input type="text" name="name" value="<?php if (isset($name)) echo h($name) ?>" />
            <?php endif ?>
        </p>
    </div>
    <div class="item">
        <p class="title">
            タイトル
        </p>
        <p class="input">
            <input type="text" name="title" value="<?php if (isset($title)) echo h($title) ?>" />
        </p>
    </div>
    <div class="item">
        <p class="title">
            本文
        </p>
        <p class="input">
            <textarea style="height: 80px;" name="body"><?php if (isset($body)) echo h($body) ?></textarea>
        </p>
    </div>
    <div class="item">
        <p class="title">
            画像 (任意)
        </p>
        <p class="input">
            <input type="file" name="image" />
        </p>
    </div>
    <!-- 編集だろう投稿だろうと上の投稿インターフェースは変わらないからここで追加的に編集の時にポストで送るものを揃えている。 -->
    <?php if (isset($isEditForm)) : ?>
        <!-- 投稿に画像がセットされているのならば画像削除ボタンをセットする。 -->
        <?php if (!empty($currentImage)) : ?>
            <div class="item">
                <p class="title">
                    現在の画像
                </p>
                <p class="input">
                    <img class="photo" src="<?php echo $imageDir ?>/<?php echo $currentImage ?>" /><br />
                    <input id="cpd" type="checkbox" name="del_image" value="1" />
                    <label for="cpd">画像を削除</label>
                </p>
            </div>
        <?php endif ?>
        <div class="submit">
            <input type="hidden" name="do_edit" value="1" />
            <input type="hidden" name="comment_id" value="<?php if (isset($id)) echo $id ?>" />
            <input type="hidden" name="page" value="<?php if (isset($page)) echo h($page) ?>" />
            <input type="hidden" name="pass" value="<?php if (isset($pass)) echo h($pass) ?>" />
            <input type="submit" value="&raquo; 編集" />
            <!-- なんでこれaタグを使わないのだろうか？ -->
            <input type="button" value="&raquo; キャンセル" onclick="window.location.href='<?php echo get_uri('index.php') ?>?page=<?php echo $page ?>';">
        </div>
    <?php else : ?>
        <!-- 以下はpost.phpでの表示内容 -->
        <?php if (!$isLogin) : ?>
            <div class="item">
                <p class="title">
                    パスワード (任意)
                </p>
                <p class="input">
                    <input type="password" name="pass" value="<?php if (isset($pass)) echo h($pass) ?>" />
                </p>
            </div>
        <?php endif ?>
        <div class="submit">
            <input type="submit" value="&raquo; 投稿" />
        </div>
    <?php endif ?>
</form>
