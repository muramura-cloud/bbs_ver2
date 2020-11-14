# アプリの説明

## アプリ名  
掲示板

## 使用した技術
言語：PHP  
データベース：MySQL  
仮装環境：virtualBox  
（フレームワークの仕組みを理解する事が目的なのでフレームワークは使わなかった。）

## 実装した機能
投稿機能  
投稿削除機能  
投稿編集機能  
画像アップロード機能  
ログイン機能  

## 作成した目的
・PHPフレームワークに取り入れらているMVCモデルを理解する目的として掲示板を作りました。  
・題材を掲示板にした事には特にこだわりはなく、自前で用意したMVCでCRUD操作が生かせる題材としてとっつき易いと思い掲示板にしました。  
・フレームワークがどのような仕組みで動いているか完璧に理解しなくても、おおよそどのような仕組みで動いているのかを理解する事はこれからフレームワークを使った開発をしていく上で必要だと思ったから。

## 工夫した点
・Laravelのような大それたものとは程遠いが、MVCモデルを意識して、htmlとプログラムを分離した事。具体的には、表示はhtmlディレクトリで管理して、データベースのやり取りはclasses/Storageディレクトリで管理して、それら二つを結びつけるコントローラーはclasses/Controllerで管理した点。  
・クラスを毎回読み込むのがめんどくさいのでクラスを自動で読み込むクラスを用意した点。  
・libにあるファイルアップロードやページネーションやバリデーションなど他のアプリケーションでも使えるようにクラス化して使いまわせるようにした点。  
・エラーが発生した際にデバッグしやすいようにログファイルに書き込むようにした点。  
・外部からの不正な入力を自分なりに対策した点。例えば、ページネーションクラスで不正なページ番号が送られてきた際はトップページにリダイレクトさせるなど。  
・SQLインジェクション対策を行った事。lib/Storage/Database/MySQLにデータベース操作の処理をクラス化したのだが、メソッド内部で外部からの入力を無害化する事で、メソッドを使う側(コントローラー)が
入力値を対策を意識する必要がなくなり、より簡単で安全にデータベースにアクセスできるようにした点。  
・lib/Validation.phpのバリデーションクラスでvalidateメソッド内部で叩かれる各項目をチェックするメソッドの引数を統一させて使いやすくした点（それぞれのメソッドは入力値、ルールの名前、ルールの内容を引数として受け取る）。  
・アプリケーションに依存したクラスはそのまま用意するのではなく、ベースとなる汎用的なクラスを用意してそれを継承させる事でメンテナンス性を向上させた点。lib/Storage/Base.phpなど  

## 学んだ感想
・プログラムとHTMLを分離する事で例えば、デザイナーとエンジニアの仕事を分けて同時並行すると言ったチームプレーできるから、MVCモデルというのは効率的な開発手法なんだなと理解できた。  
・今後機能をアップデートさせていくことを考えると、出来るだけクラスや関数は他のコードに依存させないようにして、そのクラスだけで完結させるということを意識する事が大事だと感じた。  
・MVCについて少しは理解できたのではないかと思った。ビューから入力値をコントローラーが受け取り、コントローラーの中でデータベース操作のアクションを叩き、結果として帰ってきたデータを使って利用して表示を更新すると言った一連の流れを把握できた気がする。  
・キータだけでなくGitHubのソースコードが結構参考になる事に気付いた。最初は読むのが難しくて辛かったけど、じっくり読んでなんとなく理解できる気はした。

## 参考にした記事
・[phpでデータベースへの接続方法について](https://qiita.com/mpyw/items/b00b72c5c95aac573b71)  
・[ページネーションクラスに関して](https://qiita.com/horikeso/items/21083de7cddcde32d54c)  
・[データベースクラス化に関して](https://github.com/jlake/EasyPDO/blob/master/EasyPDO.php)  
