<?php

namespace Puggan\Ibn\D;

// require_once("/mnt/data/www/libs/database.php");
// $database = new database('use dbname', 'username', 'password');

class database
{
    public ?\mysqli $link = null;
    public string $last_query = '';
    public string $last_error = '';

    public function __construct($database, $username, $password, $host = null, $port = null)
    {
        if (!$host) {
            $this->link = new \mysqli('localhost', $username, $password, $database);
        } elseif (!$port) {
            $this->link = new \mysqli($host, $username, $password, $database);
        } else {
            $this->link = new \mysqli($host, $username, $password, $database, $port);
        }

        $this->link->set_charset("utf8");
    }

    /** @noinspection PhpMissingReturnTypeInspection not allowed on __destruct */
    public function __destruct()
    {
        unset($this->link);
    }

    /**
     * @param string $query
     * @return \mysqli_result|true
     */
    public function query(string $query): \mysqli_result|bool
    {
        if (!$this->link || !$this->link->ping()) {
            $this->link = null;
            $this->last_error = 'No database connection';
            throw new \RuntimeException('Database fel: ' . $this->last_error);
        }

        $this->last_error = null;
        $this->last_query = $query;

        $result = $this->link->query($query);
        if ($result === false) {
            throw new \RuntimeException('query failed');
        }
        return $result;
    }

    public function readQuery(string $query): \mysqli_result
    {
        $result = $this->query($query);
        if ($result instanceof \mysqli_result) {
            return $result;
        }
        throw new \RuntimeException('none select-query');
    }

    public function write(string $query): void
    {
        $result = $this->query($query);
        if ($result instanceof \mysqli_result) {
            $result->free();
            throw new \RuntimeException('Select query in write-statement');
        }
        if ($result !== true) {
            throw new \RuntimeException('query failed');
        }
    }

    public function insert(string $query): int|string
    {
        $this->write($query);

        return $this->link->insert_id;
    }

    public function update(string $query): int|string
    {
        $this->write($query);

        return $this->link->affected_rows;
    }

    public function read(string $query, ?string $index = null, ?string $column = null): array
    {
        $resource = $this->readQuery($query);

        $result = $resource->fetch_all(MYSQLI_ASSOC);
        $resource->free();

        if ($index) {
            if ($column) {
                return array_column($result, $column, $index);
            }

            return array_column($result, null, $index);
        }

        if ($column) {
            return array_column($result, $column);
        }

        return $result;
    }

    public function gRead(string $query, ?string $index = null, ?string $column = null): \Generator
    {
        $resource = $this->readQuery($query);

        if ($index && $column) {
            while (null !== ($row = $resource->fetch_array(MYSQLI_ASSOC))) {
                yield $row[$index] => $row[$column];
            }
        } elseif ($index) {
            while (null !== ($row = $resource->fetch_array(MYSQLI_ASSOC))) {
                yield $row[$index] => $row;
            }
        } elseif ($column) {
            while (null !== ($row = $resource->fetch_array(MYSQLI_ASSOC))) {
                yield $row[$column];
            }
        } else {
            while (null !== ($row = $resource->fetch_array(MYSQLI_ASSOC))) {
                yield $row;
            }
        }

        $resource->free();
    }

    /**
     * @param string $query
     * @param ?string $index
     * @param ?string $className
     * @return array
     * @phpstan-return T[]
     * @phpstan-template T
     */
    public function objects(string $query, ?string $index = null, ?string $className = null): array
    {
        $result = [];
        $resource = $this->readQuery($query);

        $class = $className ?: \stdClass::class;
        if ($index) {
            while (null !== ($row = $resource->fetch_object($class))) {
                $result[$row->$index] = $row;
            }
        } else {
            while (null !== ($row = $resource->fetch_object($class))) {
                $result[] = $row;
            }
        }

        $resource->free();

        return $result;
    }

    /**
     * @param string $query
     * @param ?string $index
     * @param ?string $className
     * @phpstan-param class-string<T>
     * @return \Generator
     * @phpstan-return \Generator<T>
     * @phpstan-template T
     */
    public function gObjects(string $query, ?string $index = null, ?string $className = null): \Generator
    {
        $class = $className ?: \stdClass::class;
        $resource = $this->readQuery($query);

        if ($index) {
            while (null !== ($row = $resource->fetch_object($class))) {
                yield $row->$index => $row;
            }
        } else {
            while (null !== ($row = $resource->fetch_object($class))) {
                yield $row;
            }
        }

        $resource->free();
    }

    public function get(string $query, ?array $default = null)
    {
        $resource = $this->readQuery($query);

        $row = $resource->fetch_array(MYSQLI_ASSOC);

        $resource->free();

        if ($row === false) {
            throw new \RuntimeException('Fetch failed');
        }

        if ($row === null) {
            return $default;
        }

        if (is_array($row) && count($row) === 1) {
            return array_values($row)[0];
        }

        return $row;
    }

    /**
     * @param string $query
     * @param ?object $default
     * @phpstan-param ?T
     * @param ?string $className
     * @phpstan-param ?class-string<T>
     * @return object
     * @phpstan-return T
     * @phpstan-template T
     */
    public function object(string $query, ?object $default = null, ?string $className = null): object
    {
        $resource = $this->readQuery($query);

        $row = $resource->fetch_object($className ?: \stdClass::class);

        if ($row === false) {
            throw new \RuntimeException('Fetch failed');
        }

        $resource->free();

        return $row ?: $default;
    }

    /**
     * @param \mysqli_result $resource
     * @return ?array<int,string|int|bool|float|null>
     */
    public function fetch(\mysqli_result $resource): ?array
    {
        $result = $resource->fetch_array(MYSQLI_ASSOC);
        if ($result === false) {
            throw new \RuntimeException('fetch failed');
        }
        return $result;
    }

    public function close(\mysqli_result $resource): void
    {
        $resource->free();
    }

    public function quote(string $string): string
    {
        return "'" . $this->link->real_escape_string($string) . "'";
    }
}
