<?php

namespace IApplication;

class DBService {

    private $connection = null;

    private $dsn;
    private $username;
    private $password;
    private $opts;

    public function __construct($host, $username, $password, $db, $opts = []) {
        $this->dsn = sprintf("mysql:host=%s;dbname=%s", $host, $db);
        $this->opts = array_merge($opts, [ \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,  \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"]);
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return null|PDO
     */
    public function getConnection() {
        if (null === $this->connection) {
            $this->connect();
        }

        return $this->connection;
    }


    public function fetchAll($query, $params = []) {
        $stmt = $this->getConnection()->prepare($query);
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fetch($query, $params = []) {
        $stmt = $this->getConnection()->prepare($query);
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function insert($table, $keyValuePairs) {
        $query = sprintf("INSERT INTO %s(%s) VALUES(%s)", $table, implode(',', array_keys($keyValuePairs)), implode(',', array_map(function ($value) {
            return sprintf(':%s', $value);
        }, array_keys($keyValuePairs))));

        $stmt = $this->getConnection()->prepare($query);

        foreach ($keyValuePairs as $key => $value) {
            $stmt->bindValue(sprintf(":%s", $key), $value);
        }

        $stmt->execute();

        return $this->getConnection()->lastInsertId();
    }

    private function connect() {
        $this->connection = new \PDO($this->dsn, $this->username, $this->password, $this->opts);
    }

    public function query($query, $params = []) {
        $stmt = $this->getConnection()->prepare($query);
        if (empty($params)) {
            $stmt->execute();
        } else {
            $stmt->execute($params);
        }

        return $stmt->rowCount();
    }

}