<?php

// MySQLを使う時にデータの編集を行いやすくするためのクラス
class Storage_Database_MySQL extends Storage_Database
{
    // 設定を諸々やってデータベースハンドラをプロパティにセット。ここでコンストラクを用意したのはオプション（文字コード）をセットできるようにするため。
    public function __construct($config = [])
    {
        if (!isset($config['charset'])) {
            $config['charset'] = 'utf8';
        }

        // 親クラスのコンストラクをオーバーライドさせないため。
        parent::__construct($config);
    }

    public function getRecords($table_name, $wheres = [], $orders = [], $limit = [], $column = '*')
    {
        $sql    = "SELECT {$column} FROM {$table_name}";
        $params = [];

        if (!empty($wheres)) {
            $sql    = $this->addWhereClause($sql, $wheres);
            $params = $this->setWhereParams($params, $wheres);
        }

        if (!empty($orders)) {
            $sql = $this->addOrderByClause($sql, $orders);
        }

        if (!empty($limit)) {
            $sql    = $this->addLimitClause($sql, $limit);
            $params = $this->setLimitParams($params, $limit);
        }

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getRecordCount($table_name, $wheres = [])
    {
        return $this->getRecords($table_name, $wheres, [], [], 'COUNT(*) AS cnt')[0]['cnt'];
    }

    public function insertRecord($table_name, $data)
    {
        $col_names = array_keys($data);

        $stmt = $this->dbh->prepare("INSERT INTO {$table_name} (" . implode(',', $col_names) . ') VALUES (:' . implode(', :', $col_names) . ')');

        foreach ($data as $col_name => $value) {
            $stmt->bindValue(':' . $col_name, $value);
        }

        $stmt->execute();
    }

    public function updateRecord($table_name, $data, $wheres = [])
    {
        $sql    = "UPDATE {$table_name} SET";
        $params = [];

        $items = [];
        foreach ($data as $col_name => $value) {
            $items[]           = " {$col_name} = :{$col_name}";
            $params[$col_name] = $value;
        }
        $sql .= implode(' ,', $items);

        if (!empty($wheres)) {
            $sql    = $this->addWhereClause($sql, $wheres);
            $params = $this->setWhereParams($params, $wheres);
        }

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($params);
    }

    public function deleteRecord($table_name, $wheres = [])
    {
        $sql    = "DELETE FROM {$table_name}";
        $params = [];

        if (!empty($wheres)) {
            $sql    = $this->addWhereClause($sql, $wheres);
            $params = $this->setWhereParams($params, $wheres);
        }

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($params);
    }

    private function addWhereClause($sql, $wheres)
    {
        $sql .= ' WHERE ';

        $items = [];
        foreach ($wheres as $where) {
            $col_name = $where['col_name'];
            $items[]  = "{$col_name} {$where['operator']} :where_{$col_name}";
        }
        $sql .= implode(' AND ', $items);

        return $sql;
    }

    private function setWhereParams($params, $wheres)
    {
        foreach ($wheres as $where) {
            $params['where_' . $where['col_name']] = $where['value'];
        }

        return $params;
    }

    private function addOrderByClause($sql, $orders)
    {
        $sql .= ' ORDER BY ';

        $items = [];
        foreach ($orders as $col_name => $sort_pattern) {
            $items[] = "{$col_name} {$sort_pattern}";
        }
        $sql .= implode(', ', $items);

        return $sql;
    }

    private function addLimitClause($sql, $limit)
    {
        $sql .= ' LIMIT :limit';
        if (isset($limit['offset'])) {
            $sql .= ' OFFSET :offset';
        }

        return $sql;
    }

    private function setLimitParams($params, $limit)
    {
        $params['limit'] = $limit['limit'];
        if (isset($limit['offset'])) {
            $params['offset'] = $limit['offset'];
        }

        return $params;
    }

    protected function connect()
    {
        $config = $this->config;

        $host = $config['host'];
        if (isset($config['port']) && !empty($config['port'])) {
            $host .= ':' . $config['port'];
        }

        $dbh = new PDO("mysql:host={$host};dbname={$config['name']};charset={$config['charset']}", $config['user'], $config['password'], [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return $dbh;
    }
}
