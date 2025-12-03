<?php

namespace Framework\Core;

use App\Configuration;
use Exception;
use Framework\DB\Connection;
use Framework\DB\IDbConvention;
use Framework\DB\ResultSet;
use Framework\Http\Request;
use PDO;
use PDOException;

abstract class Model implements \JsonSerializable
{
    protected static ?string $tableName = null;
    protected static ?string $primaryKey = null;
    protected static array $columnsMap = [];

    private static array $dbColumns = [];
    private static IDbConvention $dbConventions;

    private mixed $_dbId = null;
    private ?ResultSet $_resultSet = null;

    protected static function getTableName(): string
    {
        return static::$tableName ?? self::getConventions()->getTableName(get_called_class());
    }

    protected static function getPkColumnName(): string
    {
        return static::$primaryKey ?? self::getConventions()->getPkColumnName(get_called_class());
    }

    protected static function getColumnsMap(): array
    {
        return static::$columnsMap ?? [];
    }

    /** GET ALL **/
    public static function getAll(
        ?string $whereClause = null,
        array   $whereParams = [],
        ?string $orderBy = null,
        ?int    $limit = null,
        ?int    $offset = null
    ): array
    {
        try {
            $sql = "SELECT " . static::getDBColumnNamesList() . " FROM " . static::getTableName() . "";
            if ($whereClause != null) {
                $sql .= " WHERE $whereClause";
            }
            if ($orderBy !== null) {
                $sql .= " ORDER BY $orderBy";
            }
            if ($limit !== null) {
                $sql .= " LIMIT $limit";
            }
            if ($offset !== null) {
                $sql .= " OFFSET $offset";
            }

            $stmt = Connection::getInstance()->prepare($sql);
            $stmt->execute($whereParams);
            $models = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, static::class);
            $dataSet = new ResultSet($models);
            /** @var static $model */
            foreach ($models as $model) {
                $model->_dbId = $model->getIdValue();
                $model->_resultSet = $dataSet;
            }
            return $models;
        } catch (PDOException $exception) {
            throw new Exception('Query failed: ' . $exception->getMessage(), 0, $exception);
        }
    }


    /** GET ONE **/
    public static function getOne(mixed $id): ?static
    {
        if ($id === null) return null;

        try {
            $columns = static::getDBColumnNamesList();
            $sql = "SELECT $columns FROM " . static::getTableName()
                . " WHERE " . static::getPkColumnName() . " = ?";

            $stmt = Connection::getInstance()->prepare($sql);
            $stmt->execute([$id]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            $model = new static();

            foreach ($row as $prop => $val) {
                // use the actual model instance when checking properties so they are assigned
                if (property_exists($model, $prop)) {
                    $model->{$prop} = $val;
                }
            }

            $model->_dbId = $model->getIdValue();
            $model->_resultSet = new ResultSet([$model]);

            return $model;

        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    /** COUNT **/
    public static function getCount(?string $where = null, array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM " . static::getTableName();
        if ($where) $sql .= " WHERE $where";

        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /** SAVE **/
    public function save(): void
    {
        try {
            $columns = static::getDbColumns();

            // Build a map of column => value from model properties
            $fullData = [];
            foreach ($columns as $col) {
                $prop = static::toPropertyName($col);
                $fullData[$col] = $this->{$prop} ?? null;
            }

            $pkColumn = static::getPkColumnName();

            // determine columns to exclude (primary key + createdAt)
            $excludeColumns = [$pkColumn];
            foreach ($columns as $col) {
                if (static::toPropertyName($col) === 'createdAt') {
                    $excludeColumns[] = $col;
                }
            }

            if ($this->_dbId === null) {
                // INSERT: exclude primary key column and created_at so DB sequence/default generates them
                $insertColumns = array_filter($columns, fn($c) => !in_array($c, $excludeColumns, true));
                $cols = implode(', ', $insertColumns);
                $params = implode(', ', array_map(fn($c) => ':' . $c, $insertColumns));
                $sql = "INSERT INTO " . static::getTableName() .
                    " ($cols) VALUES ($params) RETURNING " . $pkColumn;

                // prepare data only for insert columns
                $data = [];
                foreach ($insertColumns as $c) {
                    $data[$c] = $fullData[$c];
                }

                $stmt = Connection::getInstance()->prepare($sql);
                $stmt->execute($data);

                $this->_dbId = $stmt->fetchColumn();
                $pkProp = static::toPropertyName($pkColumn);
                $this->{$pkProp} = $this->_dbId;

            } else {
                // UPDATE: exclude primary key and created_at from SET list
                $updateColumns = array_filter($columns, fn($c) => !in_array($c, $excludeColumns, true));
                $updates = implode(', ', array_map(fn($c) => "$c = :$c", $updateColumns));
                $sql = "UPDATE " . static::getTableName() .
                    " SET $updates WHERE " . $pkColumn . " = :__pk";

                $data = [];
                foreach ($updateColumns as $c) {
                    $data[$c] = $fullData[$c];
                }
                $data['__pk'] = $this->_dbId;

                $stmt = Connection::getInstance()->prepare($sql);
                $stmt->execute($data);
            }

        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    /** DELETE **/
    public function delete(): void
    {
        if ($this->_dbId === null) return;

        $sql = "DELETE FROM " . static::getTableName() .
            " WHERE " . static::getPkColumnName() . " = ?";

        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute([$this->_dbId]);
    }

    /** RAW SQL **/
    public static function executeRawSQL(string $sql, array $params = []): array
    {
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** COLUMN NAMES **/
    private static function getDbColumns(): array
    {
        if (isset(self::$dbColumns[static::class])) {
            return self::$dbColumns[static::class];
        }

        $sql = "
            SELECT column_name
            FROM information_schema.columns
            WHERE table_name = :table
            ORDER BY ordinal_position
        ";

        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute(['table' => static::getTableName()]);

        return self::$dbColumns[static::class] =
            array_column($stmt->fetchAll(), 'column_name');
    }

    protected function getIdValue(): mixed
    {
        $pk = static::getPkColumnName();
        $prop = static::toPropertyName($pk);
        return $this->{$prop} ?? null;
    }

    /** ALIASING **/
    private static function getDBColumnNamesList(): string
    {
        //die("COLUMNS: " . static::getTableName());
        $parts = [];

        foreach (static::getDbColumns() as $column) {
            $property = static::toPropertyName($column);
            $parts[] = "$column AS \"$property\"";
        }

        return implode(', ', $parts);
    }

    private static function toPropertyName(string $col): string
    {
        return static::getColumnsMap()[$col]
            ?? static::getConventions()->toPropertyName($col);
    }

    private static function getConventions(): IDbConvention
    {
        return static::$dbConventions
            ?? static::$dbConventions = new (Configuration::DB_CONVENTIONS_CLASS)();
    }

    public function jsonSerialize(): array
    {
        $props = get_object_vars($this);
        unset($props['_dbId'], $props['_resultSet']);
        return $props;
    }
}
