<?php

// getAllowedTypes、isTypeValid、getTypeって全部画像の中身を確認するためのメソッドだよね多分。
// 渡ってきた画像が指定した拡張子を満たしているのか画像の中身をチェックして独自の名前をセットして
class Uploader_Image extends Uploader_File
{
    protected $allowedTypes = ['jpeg', 'gif', 'png'];

    public function setAllowedTypes($types)
    {
        if (!is_array($types)) {
            $types = array($types);
        }

        $this->allowedTypes = array_map('strtolower', $types);
    }

    public function getAllowedTypes()
    {
        return $this->allowedTypes;
    }

    // 画像の中身を確認して画像かどうかを判定し、更に、画像の拡張子もチェックしている。返り値はブール
    public function isTypeValid($data)
    {
        if (empty($this->allowedTypes)) {
            return true;
        } else {
            // ここで画像かどうかの判定をしている
            return (in_array(self::getType($data), $this->allowedTypes));
        }
    }

    public function uploadImage($data, $fileName = null)
    {
        // 画像かどうかのチェック
        $ext = self::getType($data);

        if ($ext === false) {
            throw new Exception(__METHOD__ . "() Can't upload. Invalid image type.");
        }

        if (empty($fileName)) {
            $fileName = $this->generateFileName($ext);
        }

        $this->upload($data, $fileName);

        return $fileName;
    }

    /**
     * @Override
     */
    // 親クラスのバリデーションでがサイズと拡張子チェックして、さらにこの画像クラス独自の決まりとして画像の中身をチェックしている。
    public function validate($file)
    {
        $errors = parent::validate($file);

        $data = null;
        if (isset($file['data'])) {
            $data = $file['data'];
        } elseif (isset($file['tmp_name'])) {
            // 画像を文字列（マジックナンバー）として取得。
            $data = file_get_contents($file['tmp_name']);
        }

        if (empty($data)) {
            trigger_error(__METHOD__ . "() Can't find the image data.", E_USER_WARNING);
        } elseif (!$this->isTypeValid($data)) {
            $types = implode(', ', $this->allowedTypes);
            $errors[] = "Invalid image type. {$types} only.";
        }

        return $errors;
    }

    // $dataは画像のデータが入る。ファイルのマジックナンバー見て画像の種類を判別している
    public static function getType($data)
    {
        if (strncmp("\xff\xd8", $data, 2) === 0) {
            return 'jpeg';
        } elseif (preg_match('/^GIF8[79]a/', $data) === 1) {
            return 'gif';
        } elseif (strncmp("\x89PNG\x0d\x0a\x1a\x0a", $data, 8) === 0) {
            return 'png';
        } elseif (strncmp("BM", $data, 2) === 0) {
            return 'bitmap';
        } elseif (strncmp("\x00\x00\x01\x00", $data, 4) === 0) {
            return 'ico';
        } elseif (
            strncmp("\x49\x49\x2a\x00\x08\x00\x00\x00", $data, 8) === 0 ||
            strncmp("\x4d\x4d\x00\x2a\x00\x00\x00\x08", $data, 8) === 0
        ) {
            return 'tiff';
        } else {
            return false;
        }
    }
}
