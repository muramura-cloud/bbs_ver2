<?php

// 内部でurlを組み立てている。
class Pager
{
    // /bbs/ebine_bbs6/index.phpとか /bbs/ebine_bbs6/delete.phpとかが入る
    protected $pageUri      = '/';
    // $params = [
    //     ['page'] => 2,
    // ];要するにこれはページ番号とかのクエリが入る。
    protected $params       = [];
    protected $currentPage  = 1;
    protected $itemsPerPage = 10;
    protected $windowSize   = 5;
    protected $totalPage    = 1;
    protected $itemsCount   = 0;

    // インスタンス化される時に現在の投稿件数をプロパティにセットしている。
    public function __construct($itemsCount, $itemsPerPage = null, $windowSize = null)
    {
        $this->setItemsCount($itemsCount);

        // インスタンス化する時に１ページあたりの表示件数がセットされていたらプロパティを上書きする。
        if (!empty($itemsPerPage)) {
            $this->setItemsPerPage($itemsPerPage);
        }

        if (!empty($windowSize)) {
            $this->setWindowSize($windowSize);
        }
    }

    //　外部からページのuriを受け取って、それをプロパティにセット。クエリがあったらパラメーターとしてプロパティにセットする。
    // もし、$uriが/bbs/ebine_bbs6/index.php?page=6&page=5だったら、下のような連想配列
    // $parsed = [
    //     [path] => /bbs/ebine_bbs6/index.php,
    //     [query] => page=6&page=5,
    // ];
    public function setUri($uri, $params = [])
    {
        $parsed = parse_url($uri);
        if (isset($parsed['path'])) {
            $this->pageUri = $parsed['path'];
        }

        if (isset($parsed['query'])) {
            // $parsed['query']がpage=５となっている場合は$_paramsには['page' => 3]が格納される。
            parse_str($parsed['query'], $_params);

            $params = array_merge($_params, $params);
        }

        $this->setParams($params);
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    // クエリ情報を含めたuriを返す。
    public function createUri($page = null)
    {
        $params = $this->params;

        if (empty($page)) {
            unset($params['page']);
        } else {
            $params['page'] = $page;
        }

        if (empty($params)) {
            return $this->pageUri;
        } else {
            return $this->pageUri . '?' . http_build_query($params, '', '&');
        }
    }

    // データベースの投稿件数の合計をプロパティにセットすると同時にページ数の合計も出力している。
    public function setItemsCount($itemsCount)
    {
        if (!is_natural_number($itemsCount, true)) {
            // trigger_errorはユーザーレベルのエラーメッセージを生成してエラーハンドル（コントローラーで設定した）に報告する
            // __METHOD__は自分自身のメソッド名setItemsCountのこと
            trigger_error(__METHOD__ . '() Invalid number: ' . $itemsCount, E_USER_WARNING);
            $itemsCount = 0;
        }

        $this->itemsCount = (int)$itemsCount;
        $this->setTotalPage($this->calcTotalPage());
    }

    public function getItemsCount()
    {
        return $this->itemsCount;
    }

    public function setItemsPerPage($number)
    {
        if (is_natural_number($number)) {
            // $numberは文字列の場合もあるので、キャストしている。
            $this->itemsPerPage = (int)$number;
            $this->setTotalPage($this->calcTotalPage());
        } else {
            trigger_error(__METHOD__ . '() Invalid number: ' . $number, E_USER_ERROR);
        }
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    public function setWindowSize($size)
    {
        if (is_natural_number($size)) {
            $this->windowSize = (int)$size;
        } else {
            trigger_error(__METHOD__ . '() Invalid number: ' . $size, E_USER_ERROR);
        }
    }

    public function getWindowSize()
    {
        return $this->windowSize;
    }

    public function setTotalPage($totalPage)
    {
        if (is_natural_number($totalPage)) {
            $this->totalPage = (int)$totalPage;
        } else {
            trigger_error(__METHOD__ . '() Invalid number: ' . $totalPage, E_USER_WARNING);
            $this->totalPage = 1;
        }

        // なるほど。
        if ($this->currentPage > $this->totalPage) {
            $this->currentPage = $this->totalPage;
        }
    }

    public function getTotalPage()
    {
        return $this->totalPage;
    }

    // 引数に何も設定されていなかったらプロパティの$itemsCountを参照する　
    public function calcTotalPage($itemsCount = null)
    {
        if (empty($itemsCount)) {
            $itemsCount = $this->itemsCount;
        }

        $totalPage = (int)ceil($itemsCount / $this->itemsPerPage);

        return ($totalPage < 1) ? 1 : $totalPage;
    }

    public function setCurrentPage($page)
    {
        if (is_natural_number($page)) {
            if ($page > $this->totalPage) {
                $this->currentPage = $this->totalPage;
            } elseif ($page < 1) {
                $this->currentPage = 1;
            } else {
                $this->currentPage = (int)$page;
            }
        } else {
            $this->currentPage = 1;
        }
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function isValidPageNumber($page)
    {
        return (is_natural_number($page) && $page <= $this->totalPage && $page >= 1);
    }

    // オフセットは用意していなかったな
    public function getOffset()
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public function hasPreviousPage()
    {
        return ($this->currentPage > 1);
    }

    public function getPreviousPageNumber()
    {
        return $this->currentPage - 1;
    }

    public function hasNextPage()
    {
        return ($this->currentPage < $this->totalPage);
    }

    public function getNextPageNumber()
    {
        return $this->currentPage + 1;
    }

    public function getPageNumbers()
    {
        $total = $this->totalPage;

        // 早期リターンしている。
        if ($total <= 1) {
            return [];
        }

        // $sizeが５として$middleが３とする
        $middle  = (int)ceil($this->windowSize / 2);
        $current = $this->currentPage;
        $size    = $this->windowSize;

        // 場合分けをすることによって処理がシンプルになっている。
        // min()とmax()を使うとシンプルに場合分けができる。
        if ($current <= $middle) {
            $start = 1;
            $end   = min($size, $total);
        } else {
            // $size - $middleは次ボタンの数
            $end   = min($total, $current + ($size - $middle));
            $start = max(1, $end - ($size - 1));
        }

        $numbers = array();
        for ($i = $start; $i <= $end; $i++) {
            $numbers[] = $i;
        }

        return $numbers;
    }
}
