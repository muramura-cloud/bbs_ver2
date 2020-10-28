<!-- 使用するマークアップ言語とそのバージョンを指定している -->
<!-- とりあえずXHTMLを使ってるんだということを理解する -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- XMLネームスペース属性 -->
<!-- XHTML 1.0ではlang属性とxml:lang属性を併用しなければならない -->
<!-- 複数のマークアップ言語の中で同じ名前のタグが使用されていた場合に衝突が起こる問題が生じます -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Bulletin Board System</title>
    <!-- これは/bbs/ebine_bbs6/css/default.cssが入る -->
    <link type="text/css" rel="stylesheet" href="<?php echo get_uri('css/reset.css') ?>" />
    <link type="text/css" rel="stylesheet" href="<?php echo get_uri('css/default.css') ?>" />
  </head>
  <body>
    <div id="header">
      <h1>Bulletin Board System</h1>
    </div>
