<?php

class Controller_Users extends Controller_Base
{
    // 仮登録をするアクション
    public function preRegister()
    {
        $name  = $this->getParam('name');
        $email = $this->getParam('email');
        $pass  = $this->getParam('pass');
        $page  = $this->getParam('page');

        $data = [
            'name'  => $name,
            'email' => $email,
            'pass'  => $pass,
        ];

        $users      = new Storage_Users();
        $pre_users  = new Storage_PreUsers();
        $validation = new Validation($users->getValidationRules(['name', 'email', 'pass']));

        $do_confirm   = $this->getParam('do_confirm') === '1';
        $pre_register = $this->getParam('pre_register') === '1';
        $registered   = false;

        $errors = [];

        if ($do_confirm || $pre_register) {
            $errors = $validation->validate($data);

            if ($users->sameAddressExists($email)) {
                $errors[] = '入力されたメールアドレスを既に使われています。違うメールアドレスを入力してください。';
            }
        }

        if (empty($errors) && $pre_register) {
            $token = $pre_users->insertPreUser($data);

            mb_language("japanese");
            mb_internal_encoding("UTF-8");

            $link    = $this->getEnv('http-origin') . "/bbs/register.php?token={$token}";
            $to      = $email;
            $title   = '会員登録を完了してください。';
            $message = "{$name}様\nアカウントを有効にするには、下のlinkをクリックするか、お気に入りのブラウザのアドレスバーにコピーしてください。\n24時間以内にクリックしてください。\n{$link}";

            // メール送信に失敗したらログに書き込むことにする
            if (!mb_send_mail($to, $title, $message)) {
                $this->log(__METHOD__ . '() Failed to send a mail.');
            } else {
                echo 'メールを送信しました。';
            }
        }

        $this->render('bulletin/register.php', get_defined_vars());
    }

    // 本登録をするアクション
    public function register()
    {
        $page  = $this->getParam('page');
        $token = $this->getParam('token');

        $users = new Storage_Users();

        $registered = false;

        $errors = [];

        if (!empty($token)) {
            $pre_users = new Storage_PreUsers();
            $pre_user  = $pre_users->getByToken($token);

            if (!empty($pre_user)) {
                $elapsed_hours = (int) round((strtotime(date('Y-m-d H:i:s')) - strtotime($pre_user['date'])) / 60 / 60);
                if ($elapsed_hours <= 24) {
                    $user = [
                        'name'  => $pre_user['name'],
                        'email' => $pre_user['email'],
                        'pass'  => $pre_user['pass'],
                    ];

                    $users->insertUser($user, false);
                    $registered = true;

                } else {
                    $errors[] = 'このリンクは期限切れで使えません。最初からやり直してください。';
                }

                $pre_users->deleteByToken($token);
            } else {
                $errors[] = 'ユーザー情報が取得できませんでした。最初から新規登録をやり直してください。';
            }
        }

        $this->render('bulletin/register.php', get_defined_vars());
    }

    // ログイン処理をするアクション、プログラムの処理の中でグローバル変数は使ってはならない気がする。
    public function login()
    {
        $email = $this->getParam('email');
        $pass  = $this->getParam('pass');
        $page  = $this->getParam('page');

        $users = new Storage_Users();

        $errors = [];
        if ($this->getParam('do_login') === '1') {
            if ($users->canLogin($email, $pass)) {
                session_regenerate_id(true);
                $_SESSION['login_user'] = $users->getByEmail($email);
                // $this->setSession($key,$value)
                $this->redirect('index.php', ['page' => $page]);
            } else {
                $errors[] = '入力されたメールアドレスあるいはパスワードが正しくありません。';
            }
        }

        $this->render('bulletin/login_form.php', get_defined_vars());
    }

    // 仮のログアウト機能
    public function logout()
    {
        $_SESSION['login_user'] = [];
        session_destroy();

        $this->redirect('index.php', ['page' => $this->getParam('page')]);
    }
}
