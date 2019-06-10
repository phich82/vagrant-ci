<?php

/**
 * Connection to database
 */
abstract class BaseConnection
{
    private $_conn;
    private static $_instance;

    protected $driver = 'mysql';
    protected $db_user;
    protected $db_pass;
    protected $db_name;
    protected $db_host;
    protected $db_port;
    protected $batch = 100;
    protected $showLog = true;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        try {
            $this->_conn = new \PDO(
                $this->_connString($this->driver),
                $this->db_user,
                $this->db_pass,
                $this->_options()
            );
        } catch (\PDOException $e) {
            echo "\nCould not connect to database: {$e->getMessage()}\n";
            exit;
        }
    }

    /**
     * Get an instance of Database
     *
     * @return Instance
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * Get the connection string
     *
     * @param  string $db_type
     *
     * @return string
     */
    private function _connString($driver)
    {
        switch ($driver) {
            case 'pgsql':
            case 'postgre':
                $driver = 'pgsql';
                break;
            default:
                $driver = 'mysql';
        }
        return $this->_dsn($driver);
    }

    /**
     * Get options
     *
     * @return array
     */
    private function _options()
    {
        return [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }

    /**
     * Get dsn
     *
     * @param  string $driver
     *
     * @return string
     */
    private function _dsn($driver)
    {
        return sprintf(
            "%s:host=%s;port=%d;dbname=%s",
            $driver,
            $this->db_host,
            $this->db_port,
            $this->db_name
        );
    }

    /**
     * Execute sql query
     *
     * @param  string $sql
     *
     * @return \PDOStatement
     */
    public function query($sql)
    {
        return $this->_conn->query($sql);
    }

    /**
     * Get all
     *
     * @param  string $sql
     *
     * @return array
     */
    public function all($sql)
    {
        return $this->query($sql)->fetchAll();
    }

    /**
     * Get one
     *
     * @param  string $sql
     *
     * @return array
     */
    public function fetch($sql)
    {
        return $this->query($sql)->fetch();
    }

    /**
     * Execute the sql for UPDATE|INSERT|DELETE actions
     *
     * @param  string $sql
     *
     * @return void
     */
    public function exec($sql)
    {
        $affected = $this->_conn->exec($sql);
        if ($affected === false) {
            $err = $this->_conn->errorInfo();
            if ($err[0] === '00000' || $err[0] === '01000') {
                return 0; // or return true
            }
        }
        return $affected;
    }

    /**
     * Insert many records at once time
     *
     * @param  string $table
     * @param  string|array $columns
     * @param  array $chunk
     *
     * @return int
     */
    public function insertMany($table, $columns, $chunk = [])
    {
        if (is_array($columns)) {
            $columns = implode(',', $columns);
        }
        $values = '('.implode('),(', $chunk).')';
        $sql = "INSERT INTO $table ($columns) VALUES $values";
        return $this->exec($sql);
    }

    /**
     * Insert batch
     *
     * @param  string $table
     * @param  string|array $columns
     * @param  array $data
     * @param  int $batch
     *
     * @return int
     */
    public function insertBatch($table, $columns, $data = [], $batch = 0)
    {
        if (is_array($columns)) {
            $columns = implode(',', $columns);
        }

        $batch = $batch > 0 ? $batch: $this->batch;

        echo "\nTotal records wil be inserted: {$this->formatNumber(count($data))}\n";
        echo "\nBatch: {$this->formatNumber($batch)}\n";
        echo "\n==========================START========================\n";

        $chunks = array_chunk($data, $batch);

        $affected = 0;
        foreach ($chunks as $k => $chunk) {
            $affected += $this->insertMany($table, $columns, $chunk);
            echo "\nTotal records inserted [".($k + 1)."]: {$this->formatNumber($affected)}\n";
        }

        echo "\n==========================END==========================\n";

        return $affected;
    }

    /**
     * Escape the input string
     *
     * @param  string|numeric $value
     *
     * @return string|numeric
     */
    public function escape($value)
    {
        try {
            return $this->_conn->quote($value);
        } catch (\PDOException $e) {
            echo "\nError: {$e->getMessage()}\n";
            exit;
        }
    }

    /**
     * Format number
     *
     * @param  mixed $num
     * @param  string $decimals
     * @param  string $dec_point
     * @param  string $thousands_sep
     *
     * @return mixed
     */
    public function formatNumber($num, $decimals = 0, $dec_point = '.', $thousands_sep = ',')
    {
        return number_format($num, $decimals, $dec_point, $thousands_sep);
    }

    /**
     * Nomalize rows
     *
     * @param  array $rows
     *
     * @return array
     */
    public function nomalize($rows = [])
    {
        if (!empty($rows) && is_array($rows[0])) {
            $data = [];
            for ($j = 0; $j < count($rows); $j++) {
                $data[$j] = $this->toStringEscape($rows[$j]);
            }
            return $data;
        }
        return $rows;
    }

    /**
     * Convert array to String and escape all elements of this array
     *
     * @param  array $data
     *
     * @return string
     */
    public function toStringEscape($data = [])
    {
        return implode(',', array_map(function ($item) {
            return $this->escape($item);
        }, $data));
    }

    /**
     * Close PDO connection
     *
     * @return void
     */
    public function close()
    {
        $this->_conn = null;
    }
}
