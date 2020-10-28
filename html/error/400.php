<!-- HTML_FILES_DIRは/var/www/html/bbs/ebine_bbs6/html -->
<?php include(HTML_FILES_DIR . '/common/header.php') ?>

<div id="contents">
  <h2>400 Bad Request</h2>
  <div>
    Your browser sent a request that this server could not understand.<br />
    <!-- このメッセージはどこで受け取るの？ -->
    <?php if ($message) : ?>
      <?php echo $message ?>
    <?php endif ?>
  </div>
</div>

<?php include(HTML_FILES_DIR . '/common/footer.php') ?>
