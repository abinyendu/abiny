<?php
/**
 * Database Connection Class
 * Handles database connections and basic operations
 */

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;
    private $pdo;
    private static $instance = null;

    public function __construct() {
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
    }

    /**
     * Singleton pattern - get database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create PDO connection
     */
    public function connect() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
                ];
                
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        return $this->pdo;
    }

    /**
     * Execute a query with parameters
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch multiple rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insert data and return last insert ID
     */
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->connect()->lastInsertId();
    }

    /**
     * Update data
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            $setClause[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete data
     */
    public function delete($table, $where, $params = []) {
        $sql = "UPDATE {$table} SET deleted_at = NOW() WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Hard delete data
     */
    public function hardDelete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Count records
     */
    public function count($table, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $result = $this->fetchOne($sql, $params);
        return (int) $result['count'];
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connect()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connect()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connect()->rollBack();
    }

    /**
     * Check if record exists
     */
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }

    /**
     * Get table columns
     */
    public function getColumns($table) {
        $sql = "DESCRIBE {$table}";
        return $this->fetchAll($sql);
    }

    /**
     * Execute raw SQL
     */
    public function raw($sql) {
        return $this->connect()->exec($sql);
    }

    /**
     * Escape string for SQL
     */
    public function escape($string) {
        return $this->connect()->quote($string);
    }
}
?>