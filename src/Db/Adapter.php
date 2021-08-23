<?php

/*
 * Copyright 2021 WPPConnect Team
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);

namespace WPPConnect\Db;

use PDO;
use PDOException;
use WPPConnect\Db\Exception;

class Adapter
{

    public function __construct(array $options = [], string $database = null)
    {

        if (
            $database !== null and (!isset($options['databases']['config'][$database]) or
            !in_array($options['databases']['config'][$database], $options['databases']['config']))
        ) :
            die(Exception::unknownDriver());
        endif;

        $database = ($database !== null) ? $options['databases']['config'][$database] :
                        $options['databases']['config'][$options['databases']['default']];

        $database['dbname'] = ($database['driver'] === 'sqlite') ? $database['dir'] : $database['dbname'];

        $this->options = [
            'driver' => (isset($database['driver'])) ? $database['driver'] : null,
            'host' => (isset($database['host'])) ? $database['host'] : null,
            'dbname' => (isset($database['dbname'])) ? $database['dbname'] : null,
            'username' => (isset($database['username'])) ? $database['username'] : null,
            'password' => (isset($database['password'])) ? $database['password'] : null,
            'timeout' => (isset($database['timeout'])) ? $database['timeout'] : null,
            'port' => (isset($database['port'])) ? $database['port'] : null
        ];
    }

    /**
     * Connect
     *
     * @return void
     */
    public function connect(): void
    {

        try {
            if ($this->options['driver'] === 'mysql') :
                $pdo = new PDO(
                    $this->options['driver'] . ':host=' . $this->options['host'] . ';port=' . $this->options['port'] .
                    ($this->options['dbname'] !== null ? ';dbname=' . $this->options['dbname'] : ''),
                    $this->options['username'],
                    $this->options['password'],
                    [
                    PDO::ATTR_CASE => PDO::CASE_LOWER,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => $this->options['timeout'],
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
                $this->pdo = $pdo;
            elseif ($this->options['driver'] === 'pgsql') :
                $pdo = new PDO(
                    $this->options['driver'] . ':host=' . $this->options['host']  . ';port=' . $this->options['port'] .
                    ($this->options['dbname'] !== null ? ';dbname=' . $this->options['dbname'] : ''),
                    $this->options['username'],
                    $this->options['password']
                );
                $pdo->query('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
                $this->pdo = $pdo;
            elseif ($this->options['driver'] === 'sqlite') :
                $pdo = new PDO($this->options['driver'] . ':' . $this->options['dbname'], null, null, [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => $this->options['timeout']
                ]);
                $this->pdo = $pdo;
            else :
                die(Exception::unknownDriver());
            endif;
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Disconnect
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }

    /**
     * Create Database
     *
     * @param string $database
     * @return boolean
     */
    public function createDatabase(string $dbname): bool
    {
        try {
            if ($this->options['driver'] === 'mysql') :
                $stmt = $this->pdo->prepare('CREATE DATABASE IF NOT EXISTS ' . $dbname . ';');
                if ($stmt->execute()) :
                    return true;
                endif;
            elseif ($this->options['driver'] === 'pgsql') :
                $stmt = $this->pdo->prepare('CREATE DATABASE ' . $dbname . ';');
                if ($stmt->execute()) :
                    return true;
                endif;
            elseif ($this->options['driver'] === 'sqlite') :
                $path = implode('/', explode('/', $this->options['dbname'], -1));
                $dbname = (!empty($path)) ? $path . '/' . $dbname : $dbname;
                if (@touch($dbname)) :
                    return true;
                endif;
            endif;
            return false;
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Connect and Create Database
     *
     * @param array $config
     * @param string $dbname
     * @return void
     */
    public function connectAndCreateDatabase(array $config, string $dbname): void
    {
        try {
            $this->connect($config);
            if ($this->createDatabase($dbname)) :
                $config['dbname'] = $dbname;
                $this->disconnect();
                $this->connect($config);
            else :
                die(Exception::unknownConfig());
            endif;
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Drop Database
     *
     * @param string $dbname
     * @return boolean
     */
    public function dropDatabase(string $dbname): bool
    {
        try {
            if ($this->options['driver'] === 'mysql' or $this->options['driver'] === 'pgsql') :
                $stmt = $this->pdo->prepare('DROP DATABASE ' . $dbname . ';');
                if ($stmt->execute()) :
                    return true;
                endif;
            elseif ($this->options['driver'] === 'sqlite') :
                $path = implode('/', explode('/', $this->options['dbname'], -1));
                $dbname = (!empty($path)) ? $path . '/' . $dbname : $dbname;
                if (@unlink($dbname)) :
                    return true;
                endif;
            endif;
            return false;
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Fetch All
     *
     * @param string $table
     * @param array $field
     * @param array $whereAnd
     * @param array $whereOr
     * @param array $whereLike
     * @param array $orderBy
     * @param string $orderType
     * @param int $limit
     * @return array
     */
    public function fetchAll(
        string $table,
        array $columns = [],
        array $whereAnd = [],
        array $whereOr = [],
        array $whereLike = [],
        array $orderBy = [],
        string $orderType = null,
        int $limit = 0
    ): array {

        try {
            $cond = null;
            $s = 1;
            $params = [];
            $column = '*';

            if (count($columns) > 0) :
                $column =  implode(',', $columns);
            endif;

            foreach ($whereAnd as $key => $val) :
                if ($val == 'IS NOT NULL' or $val == 'IS NULL') :
                    $cond   .=  " AND " . $key . " " . $val;
                else :
                    $cond   .=  " AND " . $key . " = :a" . $s;
                    $params['a' . $s] = $val;
                    $s++;
                endif;
            endforeach;

            foreach ($whereOr as $key => $val) :
                $cond   .=  " OR " . $key . " = :a" . $s;
                $params['a' . $s] = $val;
                $s++;
            endforeach;

            foreach ($whereLike as $key => $val) :
                $cond   .=  " OR " . $key . " LIKE '% :a" . $s . "%'";
                $params['a' . $s] = $val;
                $s++;
            endforeach;

            if (count($orderBy) > 0) :
                $orderType = (empty($orderType)) ? "ASC" : $orderType;
                $cond   .=  " ORDER BY " . implode(",", $orderBy) . " " . $orderType;
            endif;

            if ($limit > 0) :
                $cond   .=  " LIMIT " . $limit;
            endif;

            $stmt = $this->pdo->prepare('SELECT ' . $column . ' FROM ' . $table . ' WHERE 1=1  ' . $cond);
            $stmt->execute($params);
            $res = $stmt->fetchAll();
            if (! $res or count($res) != 1) :
                return $res;
            endif;
            return $res;
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Fetch All Prepare
     *
     * @param string $query
     * @return array
     */
    private function fetchAllPrepare(string $query): array
    {
        try {
            $params = func_get_args();
            unset($params[0]);
            $params = array_values($params);
            list($query, $params) = $this->preParseQuery($query, $params);

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            if ($stmt->errorCode() != 0) :
                $errors = $stmt->errorInfo();
                die(Exception::genericError($errors[2]));
            endif;

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Fetch Columns Prepare function
     *
     * @param string $query
     * @return array
     */
    private function fetchColumnsPrepare(string $query): array
    {
        try {
            $data = [];
            $params = func_get_args();
            unset($params[0]);
            $params = array_values($params);

            list($query, $params) = $this->preParseQuery($query, $params);

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            if ($stmt->errorCode() != 0) :
                $errors = $stmt->errorInfo();
                die(Exception::genericError($errors[2]));
            endif;

            $data = $stmt->fetchAll();

            if (!empty($data)) :
                $dataTmp = [];
                foreach ($data as $dat) :
                    $dataTmp[] = $dat[array_keys($dat)[0]];
                endforeach;
                $data = $dataTmp;
            endif;

            return $data;
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Fetch Var Prepare
     *
     * @param string $query
     * @return string|null
     */
    private function fetchVarPrepare(string $query): ?string
    {
        try {
            $data = [];
            $params = func_get_args();
            unset($params[0]);

            $params = array_values($params);
            list($query, $params) = $this->preParseQuery($query, $params);

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            if ($stmt->errorCode() != 0) :
                $errors = $stmt->errorInfo();
                die(Exception::genericError($errors[2]));
            endif;

            $data = $stmt->fetchObject();
            if (empty($data)) :
                return null;
            endif;

            $data = (array) $data;
            $data = (string)current($data);

            return $data;
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Fetch Row
     *
     * @param string $table
     * @param array $field
     * @param array $whereAnd
     * @param array $whereOr
     * @param array $whereLike
     * @param array $orderBy
     * @param string $orderType
     * @return array
     */
    public function fetchRow(
        string $table,
        array $columns = [],
        array $whereAnd = [],
        array $whereOr = [],
        array $whereLike = [],
        array $orderBy = [],
        string $orderType = null
    ): array {

        try {
            $cond = null;
            $s = 1;
            $params = [];
            $column = '*';

            if (count($columns) > 0) :
                $column =  implode(',', $columns);
            endif;

            foreach ($whereAnd as $key => $val) :
                if ($val == 'IS NOT NULL' or $val == 'IS NULL') :
                    $cond   .=  " AND " . $key . " " . $val;
                else :
                    $cond   .=  " AND " . $key . " = :a" . $s;
                    $params['a' . $s] = $val;
                    $s++;
                endif;
            endforeach;

            foreach ($whereOr as $key => $val) :
                $cond   .=  " OR " . $key . " = :a" . $s;
                $params['a' . $s] = $val;
                $s++;
            endforeach;

            foreach ($whereLike as $key => $val) :
                $cond   .=  " OR " . $key . " LIKE '% :a" . $s . "%'";
                $params['a' . $s] = $val;
                $s++;
            endforeach;

            if (count($orderBy) > 0) :
                $orderType = (empty($orderType)) ? "ASC" : $orderType;
                $cond   .=  " ORDER BY " . implode(",", $orderBy) . " " . $orderType;
            endif;

            $stmt = $this->pdo->prepare('SELECT ' . $column . ' FROM ' . $table . ' WHERE 1=1 ' . $cond);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Fetch Row Prepare
     *
     * @param string $query
     * @return array
     */
    private function fetchRowPrepare(string $query): array
    {
        try {
            $data = [];
            $params = func_get_args();
            unset($params[0]);

            $params = array_values($params);
            list($query, $params) = $this->preParseQuery($query, $params);

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            if ($stmt->errorCode() != 0) :
                $errors = $stmt->errorInfo();
                die(Exception::genericError($errors[2]));
            endif;

            $data = $stmt->fetch();
            return $data;
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
    * Fetch Columns
    *
    * @param string $query
    * @return array
    */
    private function fetchColumns(string $query): array
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Exec
    *
    * @param string $query
    * @return boolean
    */
    public function exec(string $query): bool
    {
        try {
            if (
                stripos($query, 'TRUNCATE') === 0 or
                stripos($query, 'CREATE') === 0 or
                stripos($query, 'DROP') === 0
            ) :
                if ($this->pdo->query($query)) :
                    return true;
                endif;
            else :
                $stmt = $this->pdo->query($query);
                return ($stmt->fetch() !== false)  ? true : false;
            endif;
            return false;
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Query
    *
    * @param string $query
    * @return boolean
    */
    public function query(string $query): bool
    {
        try {
            $params = func_get_args();
            unset($params[0]);
            $params = array_values($params);
            list($query, $params) = $this->preParseQuery($query, $params);

            if (
                stripos($query, 'CREATE') === 0 ||
                stripos($query, 'DROP') === 0 ||
                stripos($query, 'PRAGMA') === 0 ||
                stripos($query, 'BEGIN') === 0
            ) :
                $this->pdo->exec($query);
            else :
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($params);
                if ($stmt->errorCode() != 0) :
                    $errors = $stmt->errorInfo();
                    die(Exception::genericError($errors[2]));
                endif;
                return ($stmt->rowCount() > 0 )  ? true : false;
            endif;
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }


    /**
     * Insert
     *
     * @param string $table
     * @param array $data
     * @return integer
     */
    public function insert(string $table, array $data): int
    {

        try {
            $stmt = $this->pdo->prepare("INSERT INTO $table (" . implode(',', array_keys($data)) . ")
            VALUES (" . implode(',', array_fill(0, count($data), '?')) . ")");
            $stmt->execute(array_values($data));
            return $this->lastInsertId($table);
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Last Insert Id
     *
     * @param string $table
     * @param string $column
     * @return integer
     */
    private function lastInsertId(string $table = null, string $column = null): int
    {
        try {
            $lastId = 0;
            if ($this->options['driver'] == 'mysql') :
                $stmt = $this->pdo->prepare('SELECT LAST_INSERT_ID() as id;');
                $stmt->execute();
                $lastId = $stmt->fetch();
            elseif ($this->options['driver'] == 'pgsql') :
                if ($table === null or $column === null) :
                    $stmt = $this->pdo->prepare('SELECT LASTVAL() AS id;');
                else :
                    $stmt = $this->pdo->prepare(
                        "SELECT CURRVAL(pg_get_serial_sequence('" . $table . "','" . $column . "')) AS id;"
                    );
                endif;
                $stmt->execute();
                $lastId = $stmt->fetch();
            elseif ($this->options['driver'] == 'sqlite') :
                    $stmt = $this->pdo->prepare('SELECT last_insert_rowid() AS id;');
                    $stmt->execute();
                    $lastId = $stmt->fetch();
            endif;

            return (is_array($lastId) and isset($lastId['id'])) ? intval($lastId['id']) : intval($lastId);
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Update
     *
     * @param  string $table
     * @param  array  $set
     * @param  array  $where
     * @return integer    number of affected rows
     */
    public function update(string $table, array $set, array $where): int
    {
        try {
            $arrSet = array_map(function ($value) {
                return $value . '=:' . $value;
            }, array_keys($set));

            $stmt = $this->pdo->prepare(
                "UPDATE $table SET " . implode(',', $arrSet) . ' WHERE ' . key($where) . '=:' . key($where) . 'Field'
            );
            foreach ($set as $field => $value) :
                $stmt->bindValue(':' . $field, $value);
            endforeach;

            $stmt->bindValue(':' . key($where) . 'Field', current($where));
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Delete
     *
     * @param  string $table
     * @param  array  $where
     * @return integer
     */
    public function delete(string $table, array $where): int
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $table WHERE " . key($where) . ' = ?');
            $stmt->execute(array(current($where)));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(Exception::debug($e));
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Create Table
     *
     * @param string $table
     * @param array $columns
     * @return boolean
     */
    public function createTable(string $table, array $columns): bool
    {
        try {
            $query = 'CREATE TABLE IF NOT EXISTS ' . $table . ' ';
            $query .= '(';
            foreach ($columns as $key => $value) :
                $query .= $key . ' ' . $value . ',';
            endforeach;
            $query = substr($query, 0, -1);
            $query .= ')';
            if ($this->exec($query)) :
                return true;
            endif;
            return false;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Clear Truncate
     *
     * @param string $table
     * @return boolean
     */
    public function truncateTable(string $table): bool
    {
        try {
            if ($this->options['driver'] === 'mysql') :
                if ($this->exec('TRUNCATE TABLE ' . $table)) :
                    return true;
                endif;
            elseif ($this->options['driver'] === 'pgsql') :
                if ($this->exec('TRUNCATE TABLE ' . $table . ' RESTART IDENTITY')) :
                    return true;
                endif;
            elseif ($this->options['driver'] === 'sqlite') :
                if ($this->exec('DELETE FROM ' . $table)) :
                    if ($this->exec('VACUUM')) :
                        return true;
                    endif;
                endif;
            endif ;

            return false;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Drop Table
     *
     * @param string $table
     * @return boolean
     */
    public function dropTable(string $table): bool
    {
        try {
            if ($this->exec('DROP TABLE ' . $table)) :
                return true;
            endif;
            return  false;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Get All Tables
     *
     * @return array
     */
    public function getAllTables(): array
    {
        try {
            if ($this->options['driver'] === 'mysql') :
                return $this->fetchColumnsPrepare(
                    'SELECT table_name FROM information_schema.tables WHERE table_catalog = ? AND table_schema = ? 
                        ORDER BY table_name',
                    'def',
                    $this->options['dbname']
                );
            elseif ($this->options['driver'] === 'pgsql') :
                 return $this->fetchColumnsPrepare(
                     'SELECT table_name FROM information_schema.tables WHERE table_catalog = ? AND table_schema = ? 
                        ORDER BY table_name',
                     $this->options['dbname'],
                     'public'
                 );
            elseif ($this->options['driver'] === 'sqlite') :
                 return $this->fetchColumnsPrepare('SELECT name FROM sqlite_master WHERE type = ?', 'table');
            endif;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Get Columns
     *
     * @param string $table
     * @return array
     */
    public function getColumns(string $table): array
    {
        try {
            if ($this->options['driver'] === 'mysql') :
                return $this->fetchColumnsPrepare(
                    'SELECT column_name FROM information_schema.columns WHERE table_catalog = ? AND table_schema = ? 
                        AND table_name = ?',
                    'def',
                    $this->options['dbname'],
                    $table
                );
            elseif ($this->options['driver'] === 'pgsql') :
                return $this->fetchColumnsPrepare(
                    'SELECT column_name FROM information_schema.columns WHERE table_catalog = ? AND table_schema = ? 
                        AND table_name = ?',
                    $this->options['dbname'],
                    'public',
                    $table
                );
            elseif ($this->options['driver'] === 'sqlite') :
                $pragma = $this->fetchColumns('PRAGMA table_info (' .  $this->quote($table) . ')');
                $cols = [];
                foreach ($pragma as $value) :
                    $cols[] = $value['name'];
                endforeach;
                return $cols;
            endif;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Get Foreing Keys
     *
     * @param string $table
     * @return array
     */
    public function getForeignKeys(string $table): array
    {
        try {
            if ($this->options['driver'] === 'mysql') :
                $return = [];
                $cols = $this->fetchAllPrepare(
                    'SELECT
                    kcu.column_name AS column_name,
                    kcu.referenced_table_name as foreign_table_name,
                    kcu.referenced_column_name as foreign_column_name
                FROM 
                    information_schema.table_constraints AS tc 
                    JOIN information_schema.key_column_usage AS kcu
                    ON tc.constraint_name = kcu.constraint_name
                    AND tc.table_schema = kcu.table_schema
                WHERE
                    tc.constraint_type = ? AND
                    tc.table_schema = ? AND
                    tc.table_name = ?',
                    'FOREIGN KEY',
                    $this->options['dbname'],
                    $table
                );

                foreach ($cols as $value) :
                        $return[$value['column_name']] = [
                            $value['foreign_table_name'],
                            $value['foreign_column_name']
                        ];
                endforeach;
                return $return;
            elseif ($this->options['driver'] === 'pgsql') :
                $return = [];
                $cols = $this->fetchAllPrepare(
                    'SELECT
                    kcu.column_name AS column_name, 
                    ccu.table_name AS foreign_table_name,
                    ccu.column_name AS foreign_column_name 
                FROM 
                    information_schema.table_constraints AS tc 
                    JOIN information_schema.key_column_usage AS kcu
                    ON tc.constraint_name = kcu.constraint_name
                    AND tc.table_schema = kcu.table_schema
                    JOIN information_schema.constraint_column_usage AS ccu
                    ON ccu.constraint_name = tc.constraint_name
                    AND ccu.table_schema = tc.table_schema
                WHERE
                    tc.constraint_type = ? AND
                    tc.table_catalog = ? AND
                    tc.table_schema = ? AND
                    tc.table_name = ?',
                    'FOREIGN KEY',
                    $this->options['dbname'],
                    'public',
                    $table
                );
                foreach ($cols as $value) :
                    $return[$value['column_name']] = [
                    $value['foreign_table_name'],
                    $value['foreign_column_name']
                    ];
                endforeach;
                return $return;
            elseif ($this->options['driver'] === 'sqlite') :
                $pragma = $this->fetchAllPrepare('PRAGMA foreign_key_list(' . $this->quote($table) . ');');
                $return = [];
                foreach ($pragma as $value) :
                    $return[$value['from']] = [$value['table'], $value['to']];
                endforeach;
                return $return;
            endif;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Is Foreign Key
     *
     * @param string $table
     * @param string $column
     * @return boolean
     */
    public function isForeignKey(string $table, string $column): bool
    {
        try {
            if (!empty($this->getForeignKeys($table))) :
                return (array_key_exists($column, $this->getForeignKeys($table))) ? true : false;
            else :
                return false;
            endif;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Get Foreign Tables Out
     *
     * @param string $table
     * @return array
     */
    public function getForeignTablesOut(string $table): array
    {
        try {
            $return = [];
            foreach ($this->getForeignKeys($table) as $key => $value) :
                if (!array_key_exists($value[0], $return)) :
                    $return[$value[0]] = [];
                endif;
                $return[$value[0]][] = [$key, $value[1]];
            endforeach;
            return $return;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Get Foreign Tables In
     *
     * @param string $table
     * @return array
     */
    public function getForeignTablesIn(string $table): array
    {
        try {
            $return = [];
            $tables = $this->getAllTables();
            foreach ($tables as $tableName) :
                if ($tableName === $table) :
                    continue;
                endif;
                foreach ($this->getForeignTablesOut($tableName) as $key => $value) :
                    if ($key !== $table) :
                        continue;
                    endif;
                    if (!array_key_exists($tableName, $return)) :
                        $return[$tableName] = [];
                    endif;
                    $return[$tableName] = array_merge($return[$tableName], $value);
                endforeach;
            endforeach;
            return $return;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Has Table
     *
     * @param string $table
     * @return boolean
     */
    public function hasTable(string $table): bool
    {
        try {
            return (in_array($table, $this->getAllTables())) ? true : false;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Has Column
     *
     * @param string $table
     * @param string $column
     * @return boolean
     */
    public function hasColumn(string $table, string $column): bool
    {
        try {
            return (in_array($column, $this->getColumns($table))) ? true : false;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Get Column Data Type
     *
     * @param string $table
     * @param string $column
     * @return string|null
     */
    public function getColumnDatatype(string $table, string $column): ?string
    {
        try {
            if ($this->options['driver'] === 'mysql') :
                return $this->fetchVarPrepare(
                    'SELECT data_type FROM information_schema.columns WHERE table_catalog = ? 
                    AND table_schema = ? AND table_name = ? and column_name = ?',
                    'def',
                    $this->options['dbname'],
                    $table,
                    $column
                );
            elseif ($this->options['driver'] === 'pgsql') :
                return $this->fetchVarPrepare(
                    'SELECT data_type FROM information_schema.columns WHERE table_catalog = ? 
                    AND table_schema = ? AND table_name = ? and column_name = ?',
                    $this->options['dbname'],
                    'public',
                    $table,
                    $column
                );
            elseif ($this->options['driver'] === 'sqlite') :
                $pragma = $this->fetchAllPrepare('PRAGMA table_info(' . $this->quote($table) . ');');
                foreach ($pragma as $value) :
                    if ($value['name'] === $column) :
                        return $value['type'];
                    endif;
                endforeach;
                return null;
            endif;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Get Primary Key
     *
     * @param string $table
     * @return string|null
     */
    public function getPrimaryKey(string $table): ?string
    {
        try {
            if ($this->options['driver'] === 'mysql') :
                return ((object) $this->fetchRowPrepare(
                    'SHOW KEYS FROM ' . $this->quote($table) . ' WHERE Key_name = ?',
                    'PRIMARY'
                ))->column_name;
            elseif ($this->options['driver'] === 'pgsql') :
                return $this->fetchVarPrepare('SELECT pg_attribute.attname 
                    FROM pg_index 
                    JOIN pg_attribute ON pg_attribute.attrelid = pg_index.indrelid 
                    AND pg_attribute.attnum = ANY(pg_index.indkey) 
                    WHERE pg_index.indrelid = \'' . $table . '\'::regclass AND pg_index.indisprimary');
            elseif ($this->options['driver'] === 'sqlite') :
                $pragma = $this->fetchAllPrepare('PRAGMA table_info(' . $this->quote($table) . ');');
                foreach ($pragma as $value) :
                    if ($value['pk'] == 1) :
                        return $value['name'];
                    endif;
                endforeach;
                return null;
            endif;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Count
     *
     * @param string $table
     * @param array $condition
     * @return integer
     */
    public function count(string $table, array $condition = []): int
    {
        try {
            $query = 'SELECT COUNT(*) FROM ' . $this->quote($table);
            if (!empty($condition)) :
                $query .= ' WHERE ';
                foreach ($condition as $key => $value) :
                    $query .= $this->quote($key) . ' = ? ';
                    end($condition);
                    if ($key !== key($condition)) :
                        $query .= ' AND ';
                    endif;
                endforeach;
            endif;
            $args = [];
            if (!empty($condition)) :
                foreach ($condition as $c) :
                    if ($c === true) :
                        $c = 1;
                    elseif ($c === false) :
                        $c = 0;
                    endif;
                    $args[] = $c;
                endforeach;
            endif;
            $ret = $this->fetchVarPrepare($query, $args);
            if (is_numeric($ret)) :
                $ret = intval($ret);
            endif;
            return intval($ret);
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Debug Query
     *
     * @param string $query
     * @return string
     */
    public function debugQuery(string $query): string
    {
        try {
            $params = func_get_args();
            unset($params[0]);
            $params = array_values($params);
            list($query, $params) = $this->preParseQuery($query, $params);

            $keys = [];
            $values = $params;

            foreach ($params as $key => $value) :
                /**
                 * Check if named parameters (':param') or anonymous parameters ('?') are used
                 */
                if (is_string($key)) :
                    $keys[] = '/:' . $key . '/';
                else :
                    $keys[] = '/[?]/';
                endif;
                /**
                 * Bring parameter into human-readable format
                 */
                if (is_string($value)) :
                    $values[$key] = "'" . $value . "'";
                elseif (is_array($value)) :
                     $values[$key] = implode(',', $value);
                elseif (is_null($value)) :
                     $values[$key] = 'NULL';
                endif;
            endforeach;
            $query = preg_replace($keys, $values, $query, 1, $count);
            return $query;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Quote
     *
     * @param string $name
     * @return string
     */
    private function quote(string $name): string
    {
        try {
            if ($this->options['driver'] === 'mysql') :
                return '`' . $name . '`';
            elseif ($this->options['driver'] === 'pgsql' or $this->options['driver'] === 'sqlite') :
                return '"' . $name . '"';
            endif;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Find Occurrences
     *
     * @param string $haystack
     * @param string $needle
     * @return array
     */
    private function findOccurences(string $haystack, string $needle): array
    {
        try {
            $positions = [];
            $posLast = 0;
            while (($posLast = strpos($haystack, $needle, $posLast)) !== false) :
                $positions[] = $posLast;
                $posLast = $posLast + strlen($needle);
            endwhile;
            return $positions;
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * Find Nth Occurrence
     *
     * @param string $haystack
     * @param string $needle
     * @param integer $index
     * @return string|null
     */
    private function findNthOccurence(string $haystack, string $needle, int $index): ?string
    {
        try {
            $positions = $this->findOccurences($haystack, $needle);
            if (empty($positions) or $index > count($positions) - 1) :
                return null;
            endif;
            return $positions[$index];
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }

    /**
     * PreParse Query
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    private function preParseQuery(string $query, array $params): array
    {
        $return = $query;

        try {
            /**
             * Handling IN Condition
             */
            if (strpos($query, 'IN (') !== false or strpos($query, 'IN(') !== false) :
                if (!empty($params)) :
                    $inIndex = 0;
                    foreach ($params as $paramsKey => $paramsValue) :
                        if (
                            is_array($paramsValue) and
                            count($paramsValue) > 0 and
                            ((count($params) === 1 and substr_count($query, '?') === 1) or count($params) >= 2)
                        ) :
                            $inOccurence = $this->findNthOccurence($return, '?', $inIndex);
                            if (substr($return, $inOccurence - 1, 3) == '(?)') :
                                $return =
                                substr($return, 0, $inOccurence - 1) .
                                '(' .
                                (str_repeat('?,', count($paramsValue) - 1) . '?') .
                                ')' .
                                substr($return, $inOccurence + 2);
                            endif;
                            foreach ($paramsValue as $value) :
                                $inIndex++;
                            endforeach;
                        else :
                            $inIndex++;
                        endif;
                    endforeach;
                endif;
            endif;

            if (!empty($params)) :
                $paramsFlattened = [];
                array_walk_recursive($params, function ($a) use (&$paramsFlattened) {
                    $paramsFlattened[] = $a;
                });
                $params = $paramsFlattened;
            endif;

            /**
             * Try to sort out bad queries
             */
            foreach ($params as $paramsKey => $paramsValue) :
                if (is_object($paramsValue)) :
                    die(Exception::objectInQuery());
                endif;
            endforeach;

            /**
             * NULL values are treated specially: modify the query
             */
            $pos = 0;
            $deleteKeys = [];
            foreach ($params as $paramsKey => $paramsValue) :
                /**
                 * No more ?s are left
                 */
                if (($pos = strpos($return, '?', $pos + 1)) === false) :
                    break;
                endif;

                /**
                 * If param is not null, nothing must be done
                 */
                if (!is_null($paramsValue)) :
                    continue;
                endif;

                /**
                 * Case 1: If query contains WHERE before ?, then convert != ? to IS NOT NULL and = ? to IS NULL
                 */
                if (strpos(substr($return, 0, $pos), 'WHERE') !== false) :
                    if (strpos(substr($return, $pos - 5, 6), '<> ?') !== false) :
                        $return =
                        substr($return, 0, $pos - 5) .
                        preg_replace('/<> \?/', 'IS NOT NULL', substr($return, $pos - 5), 1);
                    elseif (strpos(substr($return, $pos - 5, 6), '!= ?') !== false) :
                        $return =
                        substr($return, 0, $pos - 5) .
                        preg_replace('/\!= \?/', 'IS NOT NULL', substr($return, $pos - 5), 1);
                    elseif (strpos(substr($return, $pos - 5, 6), '= ?') !== false) :
                        $return =
                        substr($return, 0, $pos - 5) . preg_replace('/= \?/', 'IS NULL', substr($return, $pos - 5), 1);
                    endif;
                    /**
                     * Case 2: In all other cases, convert ? to NULL
                     */
                else :
                    $return = substr($return, 0, $pos) . 'NULL' . substr($return, $pos + 1);
                endif;

                /**
                 * Delete params
                 */
                $deleteKeys[] = $paramsKey;
            endforeach;

            if (!empty($deleteKeys)) :
                foreach ($deleteKeys as $deleteKeysValue) :
                    unset($params[$deleteKeysValue]);
                endforeach;
            endif;
            $params = array_values($params);

            $return = trim($return);
            return [$return, $params];
        } catch (Exception $e) {
            die(Exception::debug($e));
        }
    }
}
