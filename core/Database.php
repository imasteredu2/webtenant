<?php
/**
 * Database Handler with Multi-Tenant Support
 */

class Database {
    private $conn;
    private $currentTenantId = null;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database Connection Failed: " . $e->getMessage());
            } else {
                die("Database Connection Failed. Please contact administrator.");
            }
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function setTenantId($tenantId) {
        $this->currentTenantId = $tenantId;
    }

    public function getTenantId() {
        return $this->currentTenantId;
    }

    /**
     * Execute a query with automatic tenant filtering
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                throw new Exception("Query Error: " . $e->getMessage());
            } else {
                throw new Exception("Database query failed.");
            }
        }
    }

    /**
     * Fetch all results
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single result
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Insert data
     */
    public function insert($table, $data) {
        // Add tenant_id if tenant isolation is enabled
        if (TENANT_ISOLATION && $this->currentTenantId) {
            $data['tenant_id'] = $this->currentTenantId;
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->conn->lastInsertId();
    }

    /**
     * Update data with tenant isolation
     */
    public function update($table, $data, $where, $whereParams = []) {
        // Check for parameter name collisions to prevent overwriting
        $collisions = array_intersect_key($data, $whereParams);
        if (!empty($collisions)) {
            throw new Exception("Parameter name collision in update: " . implode(', ', array_keys($collisions)));
        }
        
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);

        // Add tenant isolation to WHERE clause
        if (TENANT_ISOLATION && $this->currentTenantId) {
            $where .= " AND tenant_id = :tenant_id_where";
            $whereParams['tenant_id_where'] = $this->currentTenantId;
        }

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        return $this->query($sql, $params);
    }

    /**
     * Delete data with tenant isolation
     */
    public function delete($table, $where, $whereParams = []) {
        // Add tenant isolation to WHERE clause
        if (TENANT_ISOLATION && $this->currentTenantId) {
            $where .= " AND tenant_id = :tenant_id";
            $whereParams['tenant_id'] = $this->currentTenantId;
        }

        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $whereParams);
    }

    /**
     * Check if database is set up
     */
    public function isSetup() {
        try {
            $result = $this->fetchOne("SHOW TABLES LIKE 'tenants'");
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
}
