<?php

ini_set('memory_limit', '-1');

require_once "BaseConnection.php";

class Order extends BaseConnection
{
    protected $driver  = 'pgsql';
    protected $db_user = 'postgres';
    protected $db_pass = 'postgres';
    protected $db_name = 'postgres';
    protected $db_host = 'localhost';
    protected $db_port = 5432;

    protected $table = 'users';
    protected $batch = 20000;

    /**
     * Build data for inserting many records
     *
     * @return array
     */
    public function rows()
    {
        $rows = [];
        for ($i = 1; $i <= 1000000; $i++) {
            // $rows[] = [
            //     'from'.$i,
            //     'handle'.$i,
            //     'name'.$i,
            //     'bio'.$i,
            //     date('Ymd H:i:s')
            // ];

            $rows[] = $this->toStringEscape([
                'from'.$i,
                'handle'.$i,
                'name'.$i,
                'bio'.$i,
                date('Ymd H:i:s')
            ]);
        }
        return $rows;
    }

    /**
     * Set columns for table
     * Note: name of columns should be surrounded by the double quotes
     *
     * @param  string $table
     *
     * @return array
     */
    public function columns($table = '')
    {
        return [
            '"from"',
            '"handle"',
            '"name"',
            '"bio"',
            '"created_at"'
        ];
    }

    /**
     * Dump data
     *
     * @return int
     */
    public function dump()
    {
        echo "\nProcessing data...\n";
        $s = microtime(true); // seconds

        $affectedRows = $this->insertBatch($this->table, $this->columns(), $this->nomalize($this->rows()));

        $e = microtime(true); // seconds
        echo "\nExecute time: ".round($e - $s, 2)." second(s)\n";

        $this->close();

        return $affectedRows;
    }
}

// run
(new Order())->dump();
