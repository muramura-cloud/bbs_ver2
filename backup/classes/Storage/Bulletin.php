<?php

class Storage_Bulletin extends Storage_Base
{
    // const TITLE_MIN_LENGTH = 8;
    // const TITLE_MAX_LENGTH = 32;
    // const BODY_MIN_LENGTH  = 8;
    // const BODY_MAX_LENGTH  = 200;
    // const PASSWORD_LENGTH  = 4;

    private $validation_rules = [
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
                'pattern'  => ['regex' => '/^[0-9]{4}$/', 'meaning' => '半角4桁の数字'],
            ],
        ],
    ];

    protected $tableName = 'sample_bulletin';

    public function getValidationRules($validation_keys)
    {
        $rules = [];
        foreach ($validation_keys as $validation_key) {
            $rules[$validation_key] = $this->validation_rules[$validation_key];
        }

        return $rules;
    }

    public static function hashPassword($pass)
    {
        // このやり方でのパスワードハッシュ化は推奨されていない。hash_password()を使うようにしよう。
        return sha1($pass);
    }

    // $forUpdateをtrueにしておけば、$dataにtitleとbodyがない場合でもエラーメッセージを吐かない挙動にできる。つまり、デフォルトでは必ずtitleとbodyは必ずあるかどうかのチェックをしている。
    // これはデフォルトでは更新ではなくて、投稿版のバリデーションとなっている。
    // public function validate($data, $forUpdate = false)
    // {
    //     $errors = [];

    //     // 編集版のバリデーションを行うときはこの$forUpdateをtrueに変える。
    //     // bodyだけ編集可能という制限があった場合dataにはおそらくtitleというキーはなく、bodyだけといういった時に何もしないとこれ
    //     if (array_key_exists('title', $data) || !$forUpdate) {
    //         $_len = (isset($data['title'])) ? strlen($data['title']) : 0;
    //         if ($_len === 0) {
    //             $errors[] = 'title is empty.';
    //         } elseif ($_len < self::TITLE_MIN_LENGTH || $_len > self::TITLE_MAX_LENGTH) {
    //             $errors[] = 'title must be ' . self::TITLE_MIN_LENGTH . ' to ' . self::TITLE_MAX_LENGTH . ' characters.';
    //         }
    //     }

    //     if (array_key_exists('body', $data) || !$forUpdate) {
    //         $_len = (isset($data['body'])) ? strlen($data['body']) : 0;
    //         if ($_len === 0) {
    //             $errors[] = 'body is empty.';
    //         } elseif ($_len < self::BODY_MIN_LENGTH || $_len > self::BODY_MAX_LENGTH) {
    //             $errors[] = 'body must be ' . self::BODY_MIN_LENGTH . ' to ' . self::BODY_MAX_LENGTH . ' characters.';
    //         }
    //     }

    //     if (isset($data['pass'])) {
    //         if (preg_match('/^[0-9]{' . self::PASSWORD_LENGTH . '}$/', $data['pass']) === 0) {
    //             $errors[] = 'password must be ' . self::PASSWORD_LENGTH . ' digit number.';
    //         }
    //     }

    //     return $errors;
    // }

    public function insert($data)
    {
        if (isset($data['pass'])) {
            $data['pass'] = self::hashPassword($data['pass']);
        }

        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $this->database->insert($this->tableName, $data);
    }

    public function update($postId, $data)
    {
        if ($errors = $this->validate($data)) {
            return $errors;
        }

        $condition = 'id = ' . $this->escape($postId);
        $this->database->update($this->tableName, $data, $condition);
    }

    public function delete($postId)
    {
        $condition = 'id = ' . $this->escape($postId);

        return $this->database->delete($this->tableName, $condition);
    }

    // 削除しているのではなく、レコードのis_deletedを１にしている。しかし、なんの意味がある？
    public function softDelete($postId)
    {
        $condition = 'id = ' . $this->escape($postId);

        return $this->database->update($this->tableName, [
            'is_deleted' => 1,
        ], $condition);
    }
}
