<?php

// ここのクラスに仕事は仮ユーザーテーブルに関すること。
class Storage_PreUsers extends Storage_Base
{
    protected $tableName = 'pre_users';

    // 入力されたパスワードと日付とトークンを追加して仮テーブルに登録する
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

    public function deleteByToken($token)
    {
        $this->database->deleteRecord($this->tableName, [
            [
                'col_name' => 'token',
                'operator' => '=',
                'value'    => $token,
            ]
        ]);
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
