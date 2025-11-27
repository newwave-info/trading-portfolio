<?php
/**
 * Base Repository - Abstract Repository Pattern
 *
 * Provides common database operations for all repositories.
 *
 * @package TradingPortfolio\Database\Repositories
 * @version 0.3.0-MySQL
 */

require_once __DIR__ . '/../DatabaseManager.php';

abstract class BaseRepository
{
    /**
     * Database manager instance
     */
    protected $db;

    /**
     * Table name (must be set by child classes)
     */
    protected $table;

    /**
     * Primary key column name
     */
    protected $primaryKey = 'id';

    /**
     * Constructor
     *
     * @param DatabaseManager $db
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    /**
     * Find record by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Find all records
     *
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findAll($limit = null, $offset = null)
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }

        if ($offset !== null) {
            $sql .= " OFFSET " . (int)$offset;
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * Create new record
     *
     * @param array $data Column => Value pairs
     * @return int Last insert ID
     */
    public function create(array $data)
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->db->execute($sql, array_values($data));
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update record by ID
     *
     * @param int $id
     * @param array $data Column => Value pairs
     * @return bool
     */
    public function update($id, array $data)
    {
        $columns = array_keys($data);
        $setClause = implode(' = ?, ', $columns) . ' = ?';

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            $setClause,
            $this->primaryKey
        );

        $params = array_values($data);
        $params[] = $id;

        return $this->db->execute($sql, $params) > 0;
    }

    /**
     * Delete record by ID
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }

    /**
     * Count records
     *
     * @param array $where WHERE conditions [column => value]
     * @return int
     */
    public function count(array $where = [])
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if (!empty($where)) {
            $conditions = [];
            foreach (array_keys($where) as $column) {
                $conditions[] = "$column = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        return (int)$this->db->fetchColumn($sql, array_values($where));
    }

    /**
     * Check if record exists
     *
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->fetchColumn($sql, [$id]) !== false;
    }

    /**
     * Find by column value
     *
     * @param string $column
     * @param mixed $value
     * @param int|null $limit
     * @return array
     */
    public function findBy($column, $value, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE $column = ?";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }

        return $this->db->fetchAll($sql, [$value]);
    }

    /**
     * Find single record by column value
     *
     * @param string $column
     * @param mixed $value
     * @return array|null
     */
    public function findOneBy($column, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE $column = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$value]);
    }

    /**
     * Find with complex WHERE conditions
     *
     * @param array $where WHERE conditions [column => value]
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findWhere(array $where, $limit = null, $offset = null)
    {
        $conditions = [];
        foreach (array_keys($where) as $column) {
            $conditions[] = "$column = ?";
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $conditions);

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }

        if ($offset !== null) {
            $sql .= " OFFSET " . (int)$offset;
        }

        return $this->db->fetchAll($sql, array_values($where));
    }

    /**
     * Execute custom query
     *
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    protected function query($sql, array $params = [])
    {
        return $this->db->query($sql, $params);
    }

    /**
     * Fetch all results from custom query
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    protected function fetchAll($sql, array $params = [])
    {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Fetch one result from custom query
     *
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    protected function fetchOne($sql, array $params = [])
    {
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Execute custom query (INSERT/UPDATE/DELETE)
     *
     * @param string $sql
     * @param array $params
     * @return int Affected rows
     */
    protected function execute($sql, array $params = [])
    {
        return $this->db->execute($sql, $params);
    }

    /**
     * Begin transaction
     *
     * @return bool
     */
    protected function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * @return bool
     */
    protected function commit()
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     *
     * @return bool
     */
    protected function rollback()
    {
        return $this->db->rollback();
    }
}
