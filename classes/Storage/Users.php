<?php

// ここのクラスに仕事はユーザーテーブルに関すること。
class Storage_Users extends Storage_Base
{
    protected $tableName = 'users';

    // ここは投稿テーブルに保存されるデータのルール。ユーザー情報のついてのルールはここで定義するべきでない気がする。
    private $validationRules = [
        'name' => [
            'name'  => '名前',
            'rules' => [
                'required' => true,
                'length'   => ['min' => 3, 'max' => 16],
            ],
        ],
        'email' => [
            'name'  => 'メール',
            'rules' => [
                'required' => true,
                'pattern'  => ['regex' => '/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', 'meaning' => 'RFCに準拠した形式'],
            ],
        ],
        'pass' => [
            'name'  => 'パスワード',
            'rules' => [
                'required' => true,
                'pattern'  => ['regex' => '/^[0-9]{8,16}$/', 'meaning' => '半角8桁~16桁の数字'],
            ],
        ],
    ];

    // バリデーションルール取得するメソッドはどこテーブルクラスにおいても必要だからbaseで定義すれば？ってことはvalidationRulesはprotectedになるのかな？
    public function getValidationRules($validation_keys)
    {
        $rules = [];
        foreach ($validation_keys as $validation_key) {
            $rules[$validation_key] = $this->validationRules[$validation_key];
        }

        return $rules;
    }

    // 仮登録のデータベースのパスワード既にハッシュ化ずみなのでここで再度ハッシュかする必要ない気がした。オプションでパスワードをハッシュ化するか選べるようにした。
    // トークンによって取得したデータはハッシュ化しない。
    public function insertUser($data, $hash_password = true)
    {
        if ($hash_password && isset($data['pass'])) {
            $data['pass'] = self::hashPassword($data['pass']);
        }

        $this->database->insertRecord($this->tableName, $data);
    }

    public function sameAddressExists($email)
    {
        $same_count = $this->getRecordCount([
            [
                'col_name' => 'email',
                'operator' => '=',
                'value'    => $email,
            ]
        ]);

        return ($same_count >= 1);
    }

    // emailを受け取ってユーザーを検索して入力されたパスワードがあっているかどうか確認する。要はログインできるかどうか確認する。
    public function canLogin($email, $pass)
    {
        $can_login = false;

        $user = $this->getByEmail($email);
        if (!empty($user) && self::verifyPassword($pass, $user['pass'])) {
            $can_login = true;
        }

        return $can_login;
    }

    public function getByEmail($email)
    {
        $users = $this->getRecords([
            [
                'col_name' => 'email',
                'operator' => '=',
                'value'    => $email,
            ]
        ]);

        return (!empty($users)) ? $users[0] : null;
    }

    // パスワードの認証方法は呼び出しもと意識することではない。テーブルに責任を持つこのクラスがやるべきこと。
    public static function verifyPassword($password, $hash_password)
    {
        return password_verify($password, $hash_password);
    }

    private static function hashPassword($password)
    {
        return !is_empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
    }
}
