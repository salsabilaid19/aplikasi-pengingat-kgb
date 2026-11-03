<?php
/**
 * core/Model.php
 * Base model dengan helper CRUD generik. Model turunan cukup
 * menentukan nama tabel dan primary key.
 */
abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';

    public function all(string $orderBy = ''): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy !== '') {
            $sql .= " ORDER BY {$orderBy}";
        }
        return Database::select($sql);
    }

    public function find(int $id): ?array
    {
        return Database::selectOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        Database::execute($sql, array_values($data));
        return (int) Database::lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?";
        $params = array_merge(array_values($data), [$id]);
        return Database::execute($sql, $params);
    }

    public function delete(int $id): int
    {
        return Database::execute(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
}
