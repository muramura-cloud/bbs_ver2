<?php

// このコントローラーでログインしているかどうかのチェックをして、それに応じて表示するページを変えるのかな？
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

    // 投稿を取得して投稿フォームと投稿を表示しているアクション
    public function index()
    {
        // ログインしているかどうかのチェック　
        $isLogin = false;
        $user_id = null;
        if (isset($this->sessions['login_user']) && $this->sessions['login_user']['id'] >= 1) {
            $isLogin   = true;
            $user_name = $this->sessions['login_user']['name'];
            $user_id   = $this->sessions['login_user']['id'];
        }

        $bulletin = new Storage_Bulletin();

        // ページネーションクラスをインスタンス化する際に現在の投稿件数の合計(is_deleted = 0となっている)とこのファイルのuriをページネーションのプロパティにセット。
        $pager = $this->createPager($bulletin->getRecordCount([
            [
                'col_name' => 'is_deleted',
                'operator' => '=',
                'value'    => 0,
            ]
        ]));

        // 外部からのページの指定がなかった時（一番最初にページを表示した時とか）は$pageはnullが入る。CurrentPageには１が自動的にセットされる。
        $page = $this->getParam('page');
        if ($page && !$pager->isValidPageNumber($page)) {
            $this->err404();
        }
        $pager->setCurrentPage($page);

        $comments = $bulletin->getRecords(
            [
                [
                    'col_name' => 'is_deleted',
                    'operator' => '=',
                    'value'    => 0,
                ]
            ],
            [
                'created_at' => 'DESC',
            ],
            [
                'limit'  => $pager->getItemsPerPage(),
                'offset' => $pager->getOffset(),
            ]
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

        // ログインしている人には全く別にページを読み込ませれば良いのかな？
    }

    // 投稿を保存してダメだった時に投稿フォームを表示するアクション
    public function post()
    {
        // ログインしているかどうかのチェック
        $isLogin = false;
        $user_id = null;
        if (isset($this->sessions['login_user']) && $this->sessions['login_user']['id'] >= 1) {
            $isLogin   = true;
            $user_name = $this->sessions['login_user']['name'];
            $user_id   = $this->sessions['login_user']['id'];
        }

        // 外部からの入力値を受け取る。
        $name    = $this->getParam('name');
        $title   = $this->getParam('title');
        $body    = $this->getParam('body');
        $pass    = $this->getParam('pass');

        $data = [
            'user_id' => $user_id,
            'name'    => $name,
            'title'   => $title,
            'body'    => $body,
            'pass'    => $pass,
        ];

        $bulletin   = new Storage_Bulletin();
        $validation = new Validation($bulletin->getValidationRules(['name', 'title', 'body', 'pass']));
        $errors     = $validation->validate($data);

        // /var/www/html/bbs/ebine_bbs6/upload/bulletinが$uploadDirPathプロパティにセットされる。要はどこに画像を保存するかを指定している。
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

            // ここってレンダーじゃダメ、なぜなら表示するはずの投稿を配列として受けっとていないから。レンダーにはどのテンプレートですか？とどのデータ使いますか？が必要です。
            $this->redirect('index.php');
        } else {
            // エラーがあった際にextractして再度HTML_FILES_DIR/bulletin/post.phpを読み込んでいる。この時にform.phpでエラーを吐いている。
            $this->render('bulletin/post.php', get_defined_vars());
        }
    }

    // 削除確認画面を表示して投稿を削除するアクション
    public function delete()
    {
        // ログインしているかどうか確認する。
        $isLogin = isset($this->sessions['login_user']) && $this->sessions['login_user']['id'] >= 1;

        // comment_idとpassはhtml/index.phpからフォームをとして送られてくるデータ。これキーが一致しなかったnull返ってくる。
        $id   = $this->getParam('comment_id');
        $pass = $this->getParam('pass');
        $page = $this->getParam('page');

        // $idに不正な場合がくることを想定している。httpステータスコードとはWebサーバのレスポンスという意味。レスポンスがないのに２００のままだったらウェブブラウザやクローマーにとって不適切だから。
        if (empty($id)) {
            $this->err400();
        }

        $page = (empty($page)) ? 1 : (int)$page;

        $bulletin = new Storage_Bulletin();
        $comment  = $bulletin->getById($id);

        if (empty($comment) || $comment['is_deleted'] === '1') {
            $this->err404();
        }

        $errors  = [];

        // 投稿にパスワードセットされていてかつログインしていないのなら。
        if (!empty($comment['pass']) && !$isLogin) {
            if (!$bulletin::verifyPassword($pass, $comment['pass'])) {
                $errors[] = 'パスワードが正しくありません。再度正しいパスワードを入力してください。';
            }
        }

        if (empty($errors) && $this->getParam('do_delete') === '1') {
            if (!empty($comment['image'])) {
                $this->createImageUploader()->delete($comment['image'], true);
            }

            // 投稿の数を数える。
            $count = $bulletin->getRecordCount([
                [
                    'col_name' => 'is_deleted',
                    'operator' => '=',
                    'value'    => 0,
                ]
            ]);

            // 現在のページ番号がはみ出ないように調整している。
            if (
                $page > 1 &&
                ($count % self::PAGER_ITEMS_PER_PAGE) === 1 &&
                ($page === (int)ceil($count / self::PAGER_ITEMS_PER_PAGE))
            ) {
                $page--;
            }

            // 完全に消さないでデータ的に消したことにしている。
            $bulletin->softDeleteById($id);

            // indexアクションをたたているのと同義
            $this->redirect('index.php', ['page' => $page]);
        }

        // 削除できないのならば再度同じページを読み込む
        // 表示するページでは掲示板に関するデータを全て変数として使える状態にしておく。
        $this->render('bulletin/delete.php', get_defined_vars());
    }

    // 投稿編集フォームを表示して投稿を編集するアクション
    public function edit()
    {
        // ログインしているかのチェック
        $isLogin = isset($this->sessions['login_user']) && $this->sessions['login_user']['id'] >= 1;

        $id   = $this->getParam('comment_id');
        $pass = $this->getParam('pass');
        $page = $this->getParam('page');

        if (empty($id)) {
            $this->err400();
        }

        // 編集ページを表示する際にページ番号が送られてこないパターンってあるか？アドレスバーから直接アクセスされた時とか
        if (empty($page)) {
            $page = 1;
        }

        $bulletin = new Storage_Bulletin();
        $comment  = $bulletin->getById($id);

        if (empty($comment) || $comment['is_deleted'] === '1') {
            $this->err404();
        }

        $errors  = [];

        // 投稿にセットされていた入力値
        $name         = $comment['name'];
        $title        = $comment['title'];
        $body         = $comment['body'];
        $currentImage = $comment['image'];

        $isEditForm      = true;
        $isPasswordMatch = false;

        // 投稿にパスワードが設定されていなくて,かつログインしていないのならば
        if (!empty($comment['pass'] && !$isLogin)) {
            if ($bulletin::verifyPassword($pass, $comment['pass'])) {
                $isPasswordMatch = true;
            } else {
                $errors[] = 'パスワードが正しくありません。再度正しいパスワードを入力してください。';
            }
        }

        if (empty($errors) && $this->getParam('do_edit') === '1') {
            $name  = $this->getParam('name');
            $title = $this->getParam('title');
            $body  = $this->getParam('body');

            $data = [
                'name'  => $name,
                'title' => $title,
                'body'  => $body,
            ];

            $doDeleteImage = ($this->getParam('del_image') === '1');

            $validation = new Validation($bulletin->getValidationRules(['name', 'title', 'body']));
            $errors     = $validation->validate($data);

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

                $bulletin->updateById($id, $data);

                $this->redirect('index.php', ['page' => $page]);
            }
        }

        // パスワードの認証がうまくいかなかった場合は再度ページをエラーメッセージとも編集用のフォームを表示する。
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
