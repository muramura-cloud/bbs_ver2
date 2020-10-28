<?php

// ここで全てのページをレンダリングしている。ページをレンダリングするというのはコントローラの基本的な操作なのでBase_Controllerの役割。
class Controller_Bulletin extends Controller_Base
{
    const PAGER_ITEMS_PER_PAGE = 10;
    const PAGER_WINDOW_SIZE    = 5;

    //   upload/bulletin
    protected $imageDir = '';

    public function __construct()
    {
        $this->imageDir = Uploader_File::UPLOAD_DIR_NAME . '/bulletin';
    }

    // 投稿を取得して投稿フォームと投稿を表示しているメソッド
    public function index()
    {
        // まず、Storage_Baseクラス（Storage_Bulletinの抽象クラス）のコンストラクタが叩かれ、その中でStorage_Database_MySQLがインスタンス化されてこのクラスのプロパティである$databaseにセットされる。
        // Storage_Database_MySQLがインスタンス化される際にデータベースの接続設定にUTF-８を追加して、Storage_Database_MySQLの抽象クラスであるStorage_Databaseのコンストラクタを叩く。
        // その中ではデータベースの接続情報を取得（get_database_config）してデータベースと接続している。
        // 要は、MySQLインスタンスをプロパティにセットしてデータベースに接続している。
        $bulletin = new Storage_Bulletin();

        // ページネーションクラスをインスタンス化する際に現在の投稿件数の合計(is_deleted = 0となっている)と現在のこのファイルのuriをプロパティにセットしている。
        $pager = $this->createPager($bulletin->getCount(
            null,
            'is_deleted = 0'
        ));

        // 外部からのページの指定がなかった時（一番最初にページを表示した時とか）は$pageはnullが入る。CurrentPageには１が自動的にセットされる。
        $page = $this->getParam('page');
        if ($page && !$pager->isValidPageNumber($page)) {
            $this->err404();
        }
        $pager->setCurrentPage($page);

        // 投稿を取得する。ここの部分はちょっと変わる。
        $comments = $bulletin->fetch(
            null,
            'is_deleted = 0',
            'created_at DESC',
            $pager->getOffset(),
            $pager->getItemsPerPage()
        );

        // Storage_Bulletinのプロパティ
        // table名
        // Storage_Database_MySQLクラスのプロパティ
        // データベースハンドラ
        // データベースの接続情報
        // ページネーションクラスのプロパティ。具体的にはpageUri（/bbs/ebine_bbs6/index.php、デフォルトでprotected $pageUri = '/'となっている）と現在ページ番号（デフォルト）と１ページあたり何個投稿を表示するかとか、
        // このメソッド内で入手した$pageと$comments（投稿データ10件まで）
        // get_defined_vars()は今まで定義された変数を全て配列として取得する。クラス内で定義したプロパティも含める。主に上の列挙した変数。
        $this->render('bulletin/index.php', get_defined_vars());
    }

    // 投稿を保存してダメだった時に投稿フォームを表示するメソッド。
    public function post()
    {
        // 外部からの入力値を受け取る。
        $title = $this->getParam('title');
        $body  = $this->getParam('body');
        $pass  = $this->getParam('pass');

        $data = [
            'title' => $title,
            'body'  => $body,
            'pass'  => $pass,
        ];

        $bulletin   = new Storage_Bulletin();
        $validation = new Validation($bulletin->getValidationRules(['title', 'body', 'pass']));
        $errors     = $validation->validate($data);

        // /var/www/html/bbs/ebine_bbs6/upload/bulletinが$uploadDirPathプロパティにセットされる。
        // 要はどこに画像を保存するかを指定している。
        $uploader = $this->createImageUploader();
        $image    = $this->getFile('image');
        $hasImage = !empty($image);

        if ($hasImage) {
            $errors = array_merge($errors, $uploader->validate($image));
        }

        if (empty($errors)) {
            if ($hasImage) {
                $data['image'] = $uploader->uploadImage($image['data']);
            } else {
                $data['image'] = null;
            }

            $bulletin->insert($data);

            // BASE_URI_PATH/index.phpに移動
            $this->redirect('index.php');
        } else {
            // エラーがあった際にextractして再度HTML_FILES_DIR/bulletin/post.phpを読み込んでいる。この時にform.phpでエラーを吐いている。
            // 表示する際にここで処理の結果をデータとして受け取る。で、そのデータを利用して表示を行っている。
            $this->render('bulletin/post.php', get_defined_vars());
        }
    }

    // 外部からデータを取得して、Bulletin_storageでデータとのやり取りをして結果を返す
    // その結果を受け取って表示する。というのが大まかな流れな気がする。
    // 削除確認画面を表示して投稿を削除するメソッド
    public function delete()
    {
        // comment_idとpassはhtml/index.phpからフォームをとして送られてくるデータ。これキーが一致しなかったnull返ってくる。
        $id   = $this->getParam('comment_id');
        $pass = $this->getParam('pass');
        $page = $this->getParam('page');

        // $idに不正な場合がくることを想定している。
        if (empty($id)) {
            // httpステータスコードとはWebサーバのレスポンスという意味。
            // レスポンスがないのに２００のままだったらウェブブラウザクローマーにとって不適切だから。
            $this->err400();
        }

        // これって$pageがnullの場合あるのかな？外部からnullが送られてくる可能性を考慮しているのか？
        $page = (empty($page)) ? 1 : (int)$page;

        $bulletin = new Storage_Bulletin();
        $results  = $bulletin->fetch(null, 'id = ' . $bulletin->escape($id));

        if (!isset($results[0]) || $results[0]['is_deleted'] === '1') {
            $this->err404();
        }

        $errors  = [];
        $comment = $results[0];

        // 投稿にパスワードがセットされているのなら
        if (!empty($comment['pass'])) {
            // 入力されたパスワードチェックをしている。
            if ($bulletin->hashPassword($pass) !== $comment['pass']) {
                $errors[] = 'The password you entered, do not match.';
            }

            if (empty($errors) && $this->getParam('do_delete') === '1') {
                if (!empty($comment['image'])) {
                    // 画像を削除してさらに削除した画像を別途ディレクトリに保存している。
                    $this->createImageUploader()->delete($comment['image'], true);
                }

                // 投稿の数を数える。
                $count = $bulletin->getCount(null, 'is_deleted = 0');
                // 現在のページが２以上でかつ最後のページで現在の投稿数がちょうどぴったりより一件はみ出ていた場合（言い換えると一件消せば最大ページが変更する場合）
                // 現在のページ番号がはみ出ないように調整している。
                if (
                    $page > 1 &&
                    ($count % self::PAGER_ITEMS_PER_PAGE) === 1 &&
                    ($page === (int)ceil($count / self::PAGER_ITEMS_PER_PAGE))
                ) {
                    $page--;
                }

                // 完全に消さないでデータ的に消したことにしている。
                $bulletin->softDelete($id);

                $this->redirect('index.php', ['page' => $page]);
            }
        }

        // 削除できないのならば再度同じページを読み込む
        // 表示するページでは掲示板に関するデータを全て変数として使える状態にしておく。
        $this->render('bulletin/delete.php', get_defined_vars());
    }

    // 投稿編集フォームを表示して投稿を編集するメソッド
    public function edit()
    {
        $id   = $this->getParam('comment_id');
        $pass = $this->getParam('pass');
        $page = $this->getParam('page');

        // $idが不正だった場合
        if (empty($id)) {
            $this->err400();
        }

        // 編集ページを表示する際にページ番号が送られてこないパターンってあるか？
        // アドレスバーから直接アクセスされた時とか
        if (empty($page)) {
            $page = 1;
        }

        $bulletin = new Storage_Bulletin();
        $results  = $bulletin->fetch(null, 'id = ' . $bulletin->escape($id));

        // 投稿がうまく取得できなかった場合
        if (!isset($results[0]) || $results[0]['is_deleted'] === '1') {
            $this->err404();
        }

        $errors  = [];
        $comment = $results[0];

        // 投稿にセットされていた入力値
        $title        = $comment['title'];
        $body         = $comment['body'];
        $currentImage = $comment['image'];

        $isEditForm      = true;
        $isPasswordMatch = false;

        // 投稿にパスワードがセットされているかどうかのチェック
        if (!empty($comment['pass'])) {
            // 入力されたパスワードがあっているかどうかのチェック
            if ($bulletin->hashPassword($pass) === $comment['pass']) {
                $isPasswordMatch = true;
            } else {
                $errors[] = 'The password you entered, do not match.';
            }

            // 認証に成功した場合さらに、do_editが押されている場合。
            if (empty($errors) && $this->getParam('do_edit') === '1') {
                // 外部からの編集後の入力値を取得
                $title = $this->getParam('title');
                $body  = $this->getParam('body');

                $data = [
                    'title' => $title,
                    'body'  => $body,
                ];

                $doDeleteImage = ($this->getParam('del_image') === '1');

                $errors   = $bulletin->validate($data);

                $uploader = $this->createImageUploader();
                $image    = $this->getFile('image');
                $hasImage = !empty($image);

                // 削除ボタンが押されれずに画像がアップロードされたら入力値と画像のチェックをする
                if (!$doDeleteImage && $hasImage) {
                    $errors = array_merge($errors, $uploader->validate($image));
                }

                if (empty($errors)) {
                    // 画像削除ボタンが押されたら
                    if ($doDeleteImage) {
                        if (!empty($currentImage)) {
                            $uploader->delete($currentImage, true);
                            $data['image'] = null;
                        }
                    } elseif ($hasImage) {
                        $data['image'] = $uploader->uploadImage($image['data']);
                    }

                    $bulletin->update($id, $data);

                    $this->redirect('index.php', ['page' => $page]);
                }
            }
        }

        // パスワードの認証がうまくいかなかった場合は再度ページをエラーメッセージともにリロードする。
        // 編集用のフォームを表示する
        $this->render('bulletin/edit.php', get_defined_vars());
    }

    // 現在ページのurlをページネーターのプロパティにセットしてページネーターのインスタンス化する
    protected function createPager($itemsCount)
    {
        $pager = new Pager(
            $itemsCount,
            self::PAGER_ITEMS_PER_PAGE,
            self::PAGER_WINDOW_SIZE
        );

        $pager->setUri($this->getEnv('Request-Uri'));

        return $pager;
    }

    protected function createImageUploader()
    {
        return new Uploader_Image($this->imageDir);
    }
}
