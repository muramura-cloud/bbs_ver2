<?php

// MySQLを使う時にデータの編集を行いやすくするためのクラス
class Storage_Database_MySQL extends Storage_Database
{
    public function __construct($config = [])
    {
        if (!isset($config['charset'])) {
            $config['charset'] = 'utf8';
        }

        // 設定を諸々やってデータベースハンドラをセットしている
        parent::__construct($config);
    }

    public function fetch($tableName, $column = null, $condition = null, $order = null, $offset = null, $limit = null)
    {
        if (empty($column)) {
            $column = '*';
        }

        $sql = "SELECT {$column} FROM {$tableName}";

        if (!empty($condition)) {
            $sql = $sql . ' WHERE ' . $condition;
        }

        if (!empty($order)) {
            $sql = $sql . ' ORDER BY ' . $order;
        }

        if (!empty($limit)) {
            $sql = $sql . ' LIMIT ' . $limit;
        }

        if (!empty($offset)) {
            $sql = $sql . ' OFFSET ' . $offset;
        }

        // これは多分俺の掲示板で言う$dbh->query($sql);って言う意味だと思う。
        // だから多分ステートメントが返ってくるんだと思う。
        $result = mysqli_query($this->conn, $sql);

        if ($result === false) {
            throw new Exception(__METHOD__ . '() ' . mysql_error($this->conn));
        }

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        // これは取得してきたレコード
        return $rows;
    }

    public function getCount($tableName, $column = null, $condition = null)
    {
        if (empty($column)) {
            $column = '*';
        }

        // 'COUNT({$column}) AS c'こっちのほうが若干柔軟な気がするな
        $result = $this->fetch($tableName, "COUNT({$column}) AS c", $condition);

        if (isset($result[0]['c'])) {
            return $result[0]['c'];
        } else {
            throw new Exception(__METHOD__ . '() failed.');
        }
    }

    public function insert($tableName, $data)
    {
        if (empty($data)) {
            throw new Exception(__METHOD__ . '() data is empty.');
        }

        $sql     = "INSERT INTO {$tableName}";
        $keys    = array_keys($data);
        $columns = implode(', ', $keys);
        $values  = [];

        foreach ($data as $value) {
            $values[] = $this->escape($value);
        }

        $values = implode(', ', $values);
        $sql    = $sql . "({$columns}) VALUES({$values})";

        $result = mysqli_query($this->conn, $sql);

        if ($result === false) {
            throw new Exception(__METHOD__ . '() ' . mysql_error($this->conn));
        }

        // インサートで返り値必要なのかな？
        return $result;
    }

    // $conditionはセルフで設定する感じなんだな
    public function update($tableName, $data, $condition = null)
    {
        if (empty($data)) {
            throw new Exception(__METHOD__ . '() data is empty.');
        }

        $sql = "UPDATE {$tableName}";

        $values = [];
        foreach ($data as $key => $value) {
            $values[] = $key . ' = ' . $this->escape($value);
        }

        $values = implode(', ', $values);
        $sql = $sql . " SET {$values}";

        if (!empty($condition)) {
            $sql = $sql . " WHERE {$condition}";
        }

        $result = mysqli_query($this->conn, $sql);

        if ($result === false) {
            throw new Exception(__METHOD__ . '() ' . mysql_error($this->conn));
        }

        return $result;
    }

    public function delete($tableName, $condition = null)
    {
        $sql = "DELETE FROM {$tableName}";
        if (!empty($condition)) {
            $sql = $sql . ' WHERE ' . $condition;
        }

        $result = mysql_query($sql, $this->conn);

        if ($result === false) {
            throw new Exception(__METHOD__ . '() ' . mysql_error($this->conn));
        }

        return $result;
    }

    // SQLインジェクション対策
    public function escape($value, $withQuotes = true)
    {
        if (empty($value)) {
            return 'NULL';
        } elseif (is_string($value)) {
            // 現在の接続の文字セットでmysql_query() で安全に利用できる形式に変換している
            $value = mysqli_real_escape_string($this->conn, $value);
            return ($withQuotes) ? "'{$value}'" : $value;
        } else {
            return $value;
        }
    }

    protected function connect()
    {
        $config = $this->config;

        $host = $config['host'];
        if (isset($config['port']) && !empty($config['port'])) {
            $host .= ':' . $config['port'];
        }

        // 俺の掲示板で言う$dbhが返ってくると思う。
        $conn = mysqli_connect($host, $config['user'], $config['password'], $config['name']);

        if (!$conn) {
            throw new Exception(__METHOD__ . "() Can't connect to the database server. " . mysql_error());
        }

        return $conn;
    }
}
