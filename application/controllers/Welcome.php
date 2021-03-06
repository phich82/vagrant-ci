<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function index()
    {
        $this->load->database();
        /*
        $s = microtime(true); // return seconds
        $out = [];
        for ($i=1; $i < 20000; $i++) {
            $orderId = '0123456'.substr('000'.$i, -3);
            $params = [
                'company_code' => 'c00001',
                'store_code'   => '001',
                'order_id'     => $orderId,
                'first_name'   => 'Jhp',
                'last_name'    => 'Phich',
                'qty'          => $i,
                'updated_at'   => date('Ymd H:i:s')
            ];
            $conditions = [
                'company_code' => 'c00001',
                'store_code'   => '001',
                'order_id'     => $orderId
            ];
            $out[] = $this->updateOrInsert('orders', $params, $conditions);
        }
        $e = microtime(true); // return seconds
        $spend = ($e - $s);

        //var_dump('Executed: '.round($spend, 2).' second(s)', $out, $this->db->error());
        var_dump('Executed: '.round($spend, 2).' second(s)');
        */

        /* insert batch
        $s = microtime(true); // return seconds
        $this->insertTestData(1000000);
        $e = microtime(true); // return seconds
        $spend = ($e - $s);
        var_dump('Executed: '.round($spend, 2).' second(s)', $this->db->error());
        */

        $data = $this->dumpDataInsertMany(1500);
        $constraints = ['company_code', 'store_code', 'order_id'];
        $condtions = ['qty' => ['is not null', '']];

        var_dump($this->updateOrInsertMany($data, $constraints, $condtions, 500));

        $this->load->view('welcome_message');
    }

    private function dumpDataInsertMany($total = 100)
    {
        $out = [];
        for ($i=1; $i <= $total; $i++) {
            $out[] = [
                'company_code' => 'c00001',
                'store_code'   => '001',
                'order_id'     => 'A'.substr('000000000'.$i, -9),
                'first_name'   => 'Jhp '.$i,
                'last_name'    => 'Phich '.$i,
                'qty'          => $i,
                'created_at'   => date('Ymd H:i:s'),
                'updated_at'   => date('Ymd H:i:s'),
            ];
        }
        return $out;
    }

    public function updateOrInsert($table, $params = [], $conditions = [])
    {
        $this->load->database();
        $this->db->query($this->buildSQL($table, $params, $conditions, $this->db));
        return $this->db->affected_rows();
    }

    public function updateOrInsert1($table, $params = [], $conditions = [])
    {
        $this->load->database();
        $exist = $this->db->where($conditions)->get($table);
        $existRow = !empty($exist) ? $exist->result_array() : [];

        if (!empty($existRow)) {
            $this->db->update($table, $params, $conditions);
            return $this->db->affected_rows();
        }

        $this->db->insert($table, $params);
        return $this->db->affected_rows();
    }

    private function buildSQL($table = '', $params = [], $conditions = [], $db = null, $upsert = 'upsert')
    {
        $where = $this->prepareWhere($conditions, $this->db);
        $upsertWhere = $this->prepareWhere($conditions, $this->db, $upsert);
        $params = $this->prepareParams($params, $this->db);

        return "with $upsert as (
			update $table set (".implode(', ', $params['fields']).") = (".implode(', ', $params['values']).")
			$where 
			returning *
		)
		insert into $table (".implode(', ', $params['fields']).") 
		select ".implode(', ', $params['values'])." 
		where not exists (select 1 from $upsert $upsertWhere)";
    }

    public function prepareParams($params = [], $db = null)
    {
        $fields = [];
        $values = [];
        foreach ($params ?: [] as $k => $v) {
            $fields[] = $k;
            $values[] = $this->escape($v, $db);
        }
        return ['fields' => $fields, 'values' => $values];
    }

    public function prepareWhere($conditions = [], $db = null, $prefix = null)
    {
        $where  = '';
        $prefix = is_string($prefix) ? $prefix.'.' : '';
        $and    = ' and ';
        foreach ($conditions ?: [] as $k => $v) {
            $where .= $prefix.$k.'='.$this->escape($v, $db).$and;
        }
        // remove string ' and ' at the end of $where string
        if (!empty($where)) {
            $where = ' where '.preg_replace('/(.*)(\s)+'.trim($and).'(\s)+$/', '$1', $where);
        }
        return $where;
    }

    public function escape($v, $db = null)
    {
        return is_object($db) ? $db->escape($v) : (strpos($v, ' ') !== false || is_string($v) ? "'".$v."'" : $v);
    }

    private function buildSQL2($table = '', $db = null)
    {
        $paramsUpdate = [
            'first_name' => 'jhp',
            'last_name'  => 'phich',
            'qty'        => 10,
            'updated_at' => date('Ymd H:i:s')
        ];
        $paramsInsert = [
            'company_code' => 'c00001',
            'store_code'   => '003',
            'order_id'     => '0123456789',
            'first_name'   => 'Jhp',
            'last_name'    => 'Phich',
            'qty'          => 1,
            'updated_at'   => date('Ymd H:i:s')
        ];
        $conditions = [
            'company_code' => 'c00001',
            'store_code'   => '003',
            'order_id'     => '0123456789'
        ];

        $upsert = 'upsert';
        $fieldsUpdate = [];
        $valuesUpdate = [];
        foreach ($paramsUpdate ?: [] as $k => $v) {
            $fieldsUpdate[] = $k;
            $valuesUpdate[] = $this->escape($v, $db);
        }
        $fieldsInsert = [];
        $valuesInsert = [];
        foreach ($paramsInsert ?: [] as $k => $v) {
            $fieldsInsert[] = $k;
            $valuesInsert[] = $this->escape($v, $db);
        }
        $where = $this->prepareWhere($conditions, $this->db);
        $upsertWhere = $this->prepareWhere($conditions, $this->db, $upsert);

        return "with $upsert as (
			update $table set (".implode(', ', $fieldsUpdate).") = (".implode(', ', $valuesUpdate).")
			$where 
			returning *
		)
		insert into $table (".implode(', ', $fieldsInsert).") 
		select ".implode(', ', $valuesInsert)." 
		where not exists (select 1 from $upsert $upsertWhere)";
    }

    public function insertTestData($RowsInserted = 100)
    {
        $maxRows = 10000;
        $blocks = 1;
        if ($RowsInserted > $maxRows) {
            $blocks = ceil($RowsInserted/$maxRows);
        }
        for ($i = 1; $i <= $blocks; $i++) {
            $total = $maxRows;
            if ($i === $blocks) {
                $total = $RowsInserted - ($blocks - 1)*$maxRows;
            }
            /* Prepare some fake data (10000 rows, 40,000 values total) */
            $rows = $this->generateRows(0, $total);
            $columns = ['handle', 'name', 'bio', 'created_at'];
            $this->insert_rows('demos', $columns, $rows);
        }
    }

    private function generateRows($start = 0, $total = 10000)
    {
        return array_fill($start, $total, ['34239', '102438', "Test Message!", date('Y-m-d H:i:s')]);
    }

    /**
     * A method to facilitate easy bulk inserts into a given table.
     * @param string $table_name
     * @param array $column_names A basic array containing the column names of the data we'll be inserting
     * @param array $rows A two dimensional array of rows to insert into the database.
     * @param bool $escape Whether or not to escape data that will be inserted. Default = true.
     */
    public function insert_rows($table_name, $column_names, $rows, $escape = true)
    {
        /* Build a list of column names */
        array_walk($column_names, [$this, 'prepare_column_name']);
        $columns = implode(',', $column_names);

        /* Escape each value of the array for insertion into the SQL string */
        if ($escape) {
            array_walk_recursive($rows, [$this, 'escape_value']);
        }

        /* Collapse each rows of values into a single string */
        $length = count($rows);
        for ($i = 0; $i < $length; $i++) {
            $rows[$i] = implode(',', $rows[$i]);
        }

        /* Collapse all the rows into something that looks like
         *  (r1_val_1, r1_val_2, ..., r1_val_n),
         *  (r2_val_1, r2_val_2, ..., r2_val_n),
         *  ...
         *  (rx_val_1, rx_val_2, ..., rx_val_n)
         * Stored in $values
         */
        $values = "(".implode('), (', $rows).")";

        $sql = "INSERT INTO $table_name ($columns) VALUES $values";

        return $this->db->simple_query($sql);
    }

    private  function escape_value($value, $key = null, $db = null)
    {
        return $this->escape($value, $db);
    }

    private function prepare_column_name(&$name)
    {
        $name = "$name";
    }

    public function updateOrInsertMany($data = [], $constraints = [], $conditions = [], $batch = 100, $prefix = '')
    {
        if (empty($data) || empty($constraints) || $batch <= 0) {
            return false;
        }

        $this->load->database();

        $table   = "orders";
        $chunks  = array_chunk($data, $batch);
        $columns = array_keys($data[0]);
        $set   = [];
        $where = '';
        $affected_rows = 0;

        // check UPDATE clause & columns
        foreach ($columns as $k => $column) {
            $columns[$k] = $prefix.$column;
            $set[] = $prefix.$column.' = excluded.'.$prefix.$column;
        }

        // check WHERE clause
        if (is_array($conditions)) {
            $whereArray = [];
            foreach ($conditions as $column => $value) {
                $whereArray[] = $table.".".$column.(is_array($value) ? " ".$value[0]." ".($value[1] ? $this->db->escape($value[1]) : "") : " = ".$this->db->escape($value));
            }

            if (!empty($whereArray)) {
                $where = " WHERE ".implode(' AND ', $whereArray);
            }
        } elseif (is_string($conditions) && $conditions) {
            $where = " WHERE ".$conditions;
        }

        foreach ($chunks as $chunk) {
            // check values for insertion
            $values = $this->_prepareValuesForInsert($chunk);

            $sql  = "INSERT INTO $table (".implode(',', $columns).")";
            $sql .= " VALUES ".implode(', ', $values);
            $sql .= " ON CONFLICT (".implode(',', $constraints).")";
            $sql .= " DO UPDATE SET ".implode(', ', $set);
            $sql .= $where;

            $this->db->query($sql);

            $affected_rows += $this->db->affected_rows();
        }

        return $affected_rows;
    }

    /**
     * Prepare values for insertion
     *
     * @param  mixed $params
     *
     * @return array
     */
    private function _prepareValuesForInsert($params = [])
    {
        // $values = [];
        // // check values for insertion
        // foreach ($params as $row) {
        //     $valueArray = [];
        //     foreach ($row as $value) {
        //         $valueArray[] = $this->db->escape($value);
        //     }
        //     $values[] = '('.implode(',', $valueArray).')';
        // }
        // return $values;

        return array_reduce($params, function ($carry, $item) {
            array_walk($item, function (&$value, $key) {
                $value = $this->db->escape($value);
            });
            $carry[] = '('.implode(',', $item).')';
            return $carry;
        });
    }
}
