<!-- このエラーは存在しないファイルにアクセスした時とか、権限がないのに、ファイルを編集した時とかに起こるエラー -->
<?php include(HTML_FILES_DIR . '/common/header.php') ?>

<div id="contents">
  <h2>500 Internal Server Error</h2>
  <div>
    The server encountered an internal error or misconfiguration and was unable to complete your request.<br />
    <?php if ($message) : ?>
      <?php echo $message ?>
    <?php endif ?>
  </div>
</div>

<?php include(HTML_FILES_DIR . '/common/footer.php') ?>
