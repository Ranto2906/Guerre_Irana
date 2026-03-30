<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

abstract class BaseSelectModel
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id'];

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    public function selectAll(
        int $limit = 100,
        int $offset = 0,
        ?string $orderBy = null,
        string $direction = 'ASC',
        string $columns = '*'
    ): array {
        $safeOrderBy = $this->normalizeOrderBy($orderBy ?? $this->primaryKey);
        $safeDirection = $this->normalizeDirection($direction);

        $sql = sprintf(
            'SELECT %s FROM %s ORDER BY %s %s LIMIT :limit OFFSET :offset',
            $columns,
            $this->table,
            $safeOrderBy,
            $safeDirection
        );

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $this->normalizeLimit($limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', $this->normalizeOffset($offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function selectById(int|string $id, string $columns = '*'): ?array
    {
        $sql = sprintf(
            'SELECT %s FROM %s WHERE %s = :id LIMIT 1',
            $columns,
            $this->table,
            $this->primaryKey
        );

        $rows = $this->executeSelect($sql, [':id' => $id]);

        return $rows[0] ?? null;
    }

    public function selectWhere(
        array $filters = [],
        int $limit = 100,
        int $offset = 0,
        ?string $orderBy = null,
        string $direction = 'ASC',
        string $columns = '*'
    ): array {
        $params = [];
        $whereClause = $this->buildWhereClause($filters, $params);
        $safeOrderBy = $this->normalizeOrderBy($orderBy ?? $this->primaryKey);
        $safeDirection = $this->normalizeDirection($direction);

        $sql = sprintf(
            'SELECT %s FROM %s%s ORDER BY %s %s LIMIT :limit OFFSET :offset',
            $columns,
            $this->table,
            $whereClause,
            $safeOrderBy,
            $safeDirection
        );

        $stmt = $this->db->prepare($sql);

        foreach ($params as $name => $value) {
            $this->bindTypedValue($stmt, $name, $value);
        }

        $stmt->bindValue(':limit', $this->normalizeLimit($limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', $this->normalizeOffset($offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function selectOneWhere(
        array $filters = [],
        ?string $orderBy = null,
        string $direction = 'ASC',
        string $columns = '*'
    ): ?array {
        $rows = $this->selectWhere($filters, 1, 0, $orderBy, $direction, $columns);

        return $rows[0] ?? null;
    }

    public function countWhere(array $filters = []): int
    {
        $params = [];
        $whereClause = $this->buildWhereClause($filters, $params);

        $sql = sprintf('SELECT COUNT(*) FROM %s%s', $this->table, $whereClause);
        $rows = $this->executeSelect($sql, $params);

        return (int) ($rows[0]['COUNT(*)'] ?? 0);
    }

    protected function executeSelect(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);

        foreach ($params as $name => $value) {
            $this->bindTypedValue($stmt, $name, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    protected function buildWhereClause(array $filters, array &$params): string
    {
        if ($filters === []) {
            return '';
        }

        $clauses = [];

        foreach ($filters as $column => $value) {
            $safeColumn = $this->sanitizeColumn((string) $column);
            $paramBase = 'w_' . $safeColumn . '_' . count($params);

            if (is_array($value)) {
                if ($value === []) {
                    $clauses[] = '1 = 0';
                    continue;
                }

                $inPlaceholders = [];

                foreach (array_values($value) as $idx => $item) {
                    $name = ':' . $paramBase . '_' . $idx;
                    $inPlaceholders[] = $name;
                    $params[$name] = $item;
                }

                $clauses[] = sprintf('%s IN (%s)', $safeColumn, implode(', ', $inPlaceholders));
                continue;
            }

            if ($value === null) {
                $clauses[] = sprintf('%s IS NULL', $safeColumn);
                continue;
            }

            $paramName = ':' . $paramBase;
            $clauses[] = sprintf('%s = %s', $safeColumn, $paramName);
            $params[$paramName] = $value;
        }

        if ($clauses === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $clauses);
    }

    protected function normalizeOrderBy(string $orderBy): string
    {
        $safeOrderBy = $this->sanitizeColumn($orderBy);

        if (!in_array($safeOrderBy, $this->allowedOrderBy, true)) {
            throw new InvalidArgumentException('Invalid order by column for ' . $this->table . ': ' . $safeOrderBy);
        }

        return $safeOrderBy;
    }

    protected function normalizeDirection(string $direction): string
    {
        return strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
    }

    protected function normalizeLimit(int $limit): int
    {
        return max(1, min($limit, 500));
    }

    protected function normalizeOffset(int $offset): int
    {
        return max(0, $offset);
    }

    protected function sanitizeColumn(string $column): string
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $column)) {
            throw new InvalidArgumentException('Unsafe column name: ' . $column);
        }

        return $column;
    }

    protected function bindTypedValue(PDOStatement $stmt, string $name, mixed $value): void
    {
        if (is_int($value)) {
            $stmt->bindValue($name, $value, PDO::PARAM_INT);
            return;
        }

        if (is_bool($value)) {
            $stmt->bindValue($name, $value, PDO::PARAM_BOOL);
            return;
        }

        if ($value === null) {
            $stmt->bindValue($name, null, PDO::PARAM_NULL);
            return;
        }

        $stmt->bindValue($name, (string) $value, PDO::PARAM_STR);
    }
}
