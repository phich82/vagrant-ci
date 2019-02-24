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

        $s = microtime(true); // return seconds
        $out = [];
        for ($i=1; $i < 1000; $i++) {
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

        var_dump('Executed: '.$spend, $out, $this->db->error());

        $this->load->view('welcome_message');
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
}
