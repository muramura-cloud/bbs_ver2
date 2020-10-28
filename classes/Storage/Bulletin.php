<?php

class Storage_Bulletin extends Storage_Base
{
    protected $tableName = 'sample_bulletin';

    // ここは投稿テーブルに保存されるデータのルール。ユーザー情報のついてのルールはここで定義するべきでない気がする。
    private $validationRules = [
        'name' => [
            'name'  => '名前',
            'rules' => [
                'length' => ['min' => 3, 'max' => 16],
            ],
        ],
        'title' => [
            'name'  => 'タイトル',
            'rules' => [
                'required' => true,
                'length'   => ['min' => 10, 'max' => 32],
            ],
        ],
        'body' => [
            'name'  => 'メッセージ',
            'rules' => [
                'required' => true,
                'length'   => ['min' => 10, 'max' => 200],
            ],
        ],
        'pass' => [
            'name'  => 'パスワード',
            'rules' => [
                'pattern' => ['regex' => '/^[0-9]{4}$/', 'meaning' => '半角4桁の数字'],
            ],
        ],
    ];

    public function getValidationRules($validation_keys)
    {
        $rules = [];
        foreach ($validation_keys as $validation_key) {
            $rules[$validation_key] = $this->validationRules[$validation_key];
        }

        return $rules;
    }

    public function insert($data)
    {
        if (isset($data['pass'])) {
            $data['pass'] = self::hashPassword($data['pass']);
        }

        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $this->database->insertRecord($this->tableName, $data);
    }

    // これ取得でいなかった場合どうなるの？多分、[0]はないよ。多分警告されるよね？取得できたかどうか確認する必要がある。
    public function getById($id)
    {
        return $this->getRecords([
            [
                'col_name' => 'id',
                'operator' => '=',
                'value'    => $id,
            ]
        ])[0];
    }

    public function updateById($id, $data)
    {
        $this->database->updateRecord($this->tableName, $data, [
            [
                'col_name' => 'id',
                'operator' => '=',
                'value'    => $id,
            ]
        ]);
    }

    public function deleteById($id)
    {
        $this->database->deleteRecord($this->tableName, [
            [
                'col_name' => 'id',
                'operator' => '=',
                'value'    => $id,
            ]
        ]);
    }

    public function softDeleteById($id)
    {
        return $this->database->updateRecord(
            $this->tableName,
            [
                'is_deleted' => 1,
            ],
            [
                [
                    'col_name' => 'id',
                    'operator' => '=',
                    'value' => $id,
                ]
            ]
        );
    }

    // パスワードの認証方法は呼び出しもと意識することではない。テーブルに責任を持つこのクラスがやるべきこと。
    public static function verifyPassword($password, $hash_password)
    {
        return password_verify($password, $hash_password);
    }

    // 実験で使うためにパブリックにした
    private static function hashPassword($password)
    {
        return !is_empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
    }
}
