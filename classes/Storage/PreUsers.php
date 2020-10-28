<?php

// ここのクラスに仕事は仮ユーザーテーブルに関すること。
class Storage_PreUsers extends Storage_Base
{
    protected $tableName = 'pre_users';

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

    // 仮テーブルにデータを保存する。送られてきたパスワードはハッシュかしていない。ここでハッシュ化するべきかな？
    public function insertPreUser($data)
    {
        if (!isset($data['token'])) {
            $data['token'] = self::createToken();
        }

        if (!isset($data['date'])) {
            $data['date'] = date('Y-m-d H:i:s');
        }

        if (isset($data['pass'])) {
            $data['pass'] = self::hashPassword($data['pass']);
        }

        $this->database->insertRecord($this->tableName, $data);

        return $data['token'];
    }

    public function getByToken($token)
    {
        $pre_users = $this->getRecords([
            [
                'col_name' => 'token',
                'operator' => '=',
                'value'    => $token,
            ]
        ]);

        return (!empty($pre_users)) ? $pre_users[0] : null;
    }

    private static function hashPassword($password)
    {
        return !is_empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
    }

    // トークンを生成するメソッドが必要になる。
    private static function createToken()
    {
        return bin2hex(random_bytes(32));
    }
}
