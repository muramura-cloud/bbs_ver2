<?php

// これはこれ単体で使う場合もあるから抽象クラスではない
// ここで$_FILESを参照すると
// 画像データを受け取って自分が指定したディレクトリに画像を移動させることがしごと
class Uploader_File
{
    const UPLOAD_DIR_NAME = 'upload';

    protected $maxSize           = 1; // MB
    protected $allowedExtensions = [];

    // /var/www/html/bbs/ebine_bbs6/upload/bulletinがデフォルト
    // /var/www/html/bbs/ebine_bbs6/upload/bulletin_subとかもできる。
    protected $uploadDirPath = "";

    // デフォルトではuploadが画像の保存先になるが自分で何かしら別の保存先（今回だとbulletin）を用意したい時にはここの引数にそれディレクトリ名を入れる。
    // コントローラーではupload/bulletinを引数に入れている。
    public function __construct($dir = null)
    {
        $this->setDir($dir);
    }

    public function setDir($dir, $append = true)
    {
        if (empty($dir)) {
            // $dirには/var/www/html/bbs/ebine_bbs6/upload/bulletinが入る。
            $dir = PROJECT_ROOT . '/' . self::UPLOAD_DIR_NAME;
        } else {
            $dir = PROJECT_ROOT . '/' . ltrim($dir, '/');
        }

        // ディレクトリが存在しその中にファイルがあるならば
        if (file_exists($dir) && is_file($dir)) {
            throw new Exception(__METHOD__ . "() '{$dir}' is a file.");
        }

        // 保存先のディレクトリがなかったら作る（$appendがtrueになっている場合）
        if (!file_exists($dir)) {
            if ($append) {
                if (!mkdir($dir, 0777, true)) {
                    throw new Exception(__METHOD__ . "() Failed to create directory '{$dir}'.");
                }
            } else {
                throw new Exception(__METHOD__ . "() Directory not found. '{$dir}'");
            }
        }

        $this->uploadDirPath = $dir;
    }

    public function setAllowedExtensions($exts)
    {
        // これ拡張子一個だけ指定する場合みたいな配列じゃない時も配列にする
        if (!is_array($exts)) {
            $exts = array($exts);
        }

        // ビルトイン関数をマップに指定できるのか。
        $this->allowedExtensions = array_map('strtolower', $exts);
    }

    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;
    }

    public function getMaxSize()
    {
        return $this->maxSize;
    }

    // 拡張子が指定した拡張子であるかどうのチェック
    public function isExtensionValid($fileName)
    {
        if (empty($this->allowedExtensions)) {
            return true;
        } else {
            return (in_array($this->getExtension($fileName), $this->allowedExtensions));
        }
    }

    // 配列にしてやった方が使い分けしやすい気がする。
    public function isSizeValid($size)
    {
        return ($size <= ($this->maxSize * 1048576));
    }

    public function generateFileName($ext = null)
    {
        $name = generate_random_string();

        if (!empty($ext)) {
            $name .= '.' . $ext;
        }

        if (file_exists($this->uploadDirPath . '/' . $name)) {
            $name = $this->generateFileName($ext);
        }

        return $name;
    }

    public function getExtension($fileName, $toLowerCase = true)
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        return ($toLowerCase) ? strtolower($ext) : $ext;
    }

    // 画像のデータ（マジックナンバー）が$dataに入る。
    public function upload($data, $fileName)
    {
        // /var/www/html/bbs/ebine_bbs6/upload/bulletin/~
        $filePath = $this->uploadDirPath . '/' . $fileName;

        // これは俺が実験的に追加した。保存先のディレクトリ(upload/bulletin)に権限を与えないとダメっぽい。
        chmod($this->uploadDirPath, 0777);

        // $dataを$filePathに書き込む
        if (!file_put_contents($filePath, $data)) {
            throw new Exception(__METHOD__ . "() Failed to upload a file. '{$filePath}'");
        }

        // 所有者自身、所有者が属するグループ、その他のユーザーに全ての権限を付与する
        // 1 は実行権限、2 はファイルに対する書き込み権限、 4 はファイルに対する読み込み権限を与えます
        chmod($filePath, 0777);

        return true;
    }

    // デフォルトでは画像は削除リストに移さずに完全に削除する
    public function delete($fileName, $move = false)
    {
        if ($move) {
            $srcPath = $this->uploadDirPath . '/' . $fileName;

            // $this->uploadDirPathは/var/www/html/bbs/ebine_bbs6/upload/bulletin
            // $dstDirは/var/www/html/bbs/ebine_bbs6/upload/bulletin_deleted
            $dstDir  = dirname($this->uploadDirPath) . '/' . basename($this->uploadDirPath) . '_deleted';
            $dstPath = $dstDir . '/' . $fileName;

            // 既に削除用ディレクトリがなかったら作る。
            if (!is_dir($dstDir)) {
                if (!mkdir($dstDir, 0777, true)) {
                    throw new Exception(__METHOD__ . "() Failed to create directory '{$dstDir}'.");
                }
            }

            // これは俺が実験的に追加した。保存先のディレクトリ(upload/bulletin_deleted)に権限を与えないとダメっぽい。
            chmod($dstDir, 0777);

            // ファイル保存ディレクトリからファイル削除済みディレクトリにファイルを移動する
            if (!rename($srcPath, $dstPath)) {
                throw new Exception(__METHOD__ . "() Failed to move a file. '{$srcPath}' -> '{$dstPath}'");
            }
        } else {
            $filePath = $this->uploadDirPath . '/' . $fileName;

            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    throw new Exception(__METHOD__ . "() Failed to delete a file. '{$filePath}'");
                }
            }
        }

        return true;
    }

    public function validate($file)
    {
        $errors = [];

        if (isset($file['name']) && !$this->isExtensionValid($file['name'])) {
            $exts     = implode(', ', $this->allowedExtensions);
            $errors[] = "拡張子が不正です。拡張子は {$exts} のいずれかでお願いします。";
        }

        if (isset($file['size']) && !$this->isSizeValid($file['size'])) {
            $errors[] = "画像のサイズは{$this->maxSize}MB以下にしてください。";
        }

        return $errors;
    }
}
