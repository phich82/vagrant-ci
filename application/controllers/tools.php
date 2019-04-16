<?php

/**
 * Migration tool for CodeIgniter
 */
class Tools extends CI_Controller
{

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // only be called from the command line
        if (!$this->input->is_cli_request()) {
            exit('Direct access is not allowed. This is a command line tool, so you should use the terminal');
        }

        // load the forge database class
        $this->load->dbforge();

        // initiate faker
        $this->faker = Faker\Factory::create();

        // path to the migration & seedes folders
        $this->migration_path = APPPATH."database/migrations/";
        $this->seed_path = APPPATH."database/seeds/";
    }

    public function dump()
    {
        $this->load->database();

        $s = microtime(true); // return seconds
        $this->insertTestData(1000000);
        $e = microtime(true); // return seconds
        $spend = ($e - $s);
        var_dump("Executed: ".round($spend, 2)." second(s)\n");
        //var_dump('Executed: '.round($spend, 2).' second(s)', $this->db->error());
    }

    public function insertTestData($RowsInserted = 100)
    {
        echo "\nRows will be inserted: ".number_format($RowsInserted)."\n";

        $maxRows = 10000;
        $blocks = 1;
        if ($RowsInserted > $maxRows) {
            $blocks = ceil($RowsInserted/$maxRows);
        }
        $inserted = 0;
        for ($i = 1; $i <= $blocks; $i++) {
            $total = $maxRows;
            if ($i === $blocks) {
                $total = $RowsInserted - ($blocks - 1)*$maxRows;
            }
            /* Prepare some fake data (10000 rows, 40,000 values total) */
            $rows = $this->generateRows(0, $total, $i);
            $columns = ['handle', 'name', 'bio', 'created_at'];
            $this->insert_rows('demos', $columns, $rows);
            $inserted += $total;
            echo "\nInserted: ".number_format($inserted)." rows\n";
        }
        echo "\nDone\n";
    }

    private function generateRows($start = 0, $total = 10000, $block = 1)
    {
        //return array_fill($start, $total, ['34239', '102438', "Test Message!", date('Y-m-d H:i:s')]);
        $out = [];
        for ($i = $start; $i < $total; $i++) {
            $handle = 'A'.substr(1000000000 + $i + 1 + ($block - 1)*$total, -9);
            $out[] = [$handle, '102438', "Test Message! ".$i, date('Y-m-d H:i:s')];
        }
        return $out;
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
        //array_walk($column_names, [$this, 'prepare_column_name']);
        $columns = implode(',', $column_names);

        /* Escape each value of the array for insertion into the SQL string */
        if ($escape) {
            //array_walk_recursive($rows, [$this, 'escape']);
        }

        /* Collapse each rows of values into a single string */
        $length = count($rows);
        for ($i = 0; $i < $length; $i++) {
            $rows[$i] = implode(',', $this->db->escape($rows[$i]));
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

    /**
     * Display the message to the console
     *
     * @param string $message [string]
     *
     * @return void
     */
    public function message($message)
    {
        echo $message.PHP_EOL;
    }

    /**
     * Show the details of the in-built commands
     *
     * @return void
     */
    public function help()
    {
        $result  = "The following are the available command line interface commands\n\n";
        $result .= "php index.php tools migration [file_name] [table_name] [custom]  Create new migration file. The custom argument is optional for custom template.\n";
        $result .= "php index.php tools migrate [version_number]                     Run all migrations. The version_number is optional.\n";
        $result .= "php index.php tools seeder [file_name]                           Creates a new seed file.\n";
        $result .= "php index.php tools seed [file_name]                             Run the specified seed file.\n";
        $result .= "php index.php tools seed                                         Run all seed files.\n";

        $this->message($result);
    }

    /**
     * Create a migration file
     *
     * @param string $name   [file_name]
     * @param string $table  [table_name]
     * @param bool   $custom [table_name]
     *
     * @return void
     */
    public function migration($name, $table = null, $custom = false)
    {
        $this->make_migration($name, $table, $custom);
    }

    /**
     * Run all the pending migration files
     *
     * @param string $version [version]
     *
     * @return void
     */
    public function migrate($version = null)
    {
        $this->load->library('migration');

        if ($version != null) {
            if ($this->migration->version($version) === false) {
                show_error($this->migration->error_string());
            } else {
                $this->message("Migrations run successfully.");
            }
            return;
        }

        if ($this->migration->latest() === false) {
            show_error($this->migration->error_string());
        } else {
            $this->message("Migrations run successfully.");
        }
    }

    /**
     * Create the seeder file
     *
     * @param string $name  [file_name]
     * @param string $table [table_name]
     *
     * @return void
     */
    public function seeder($name, $table = '')
    {
        $this->make_seeder($name, $table);
    }

    /**
     * Run seed
     *
     * @param string $name [filename]
     *
     * @return void
     */
    public function seed($name = null)
    {
        (new Seeder)->call($name);
    }

    /**
     * Create the migration file
     *
     * @param string $name  [name]
     * @param string $table [name]
     *
     * @return void
     */
    protected function make_migration($name, $table = null, $custom = false)
    {
        $timestamp = date('YmdHis');
        $table = !empty($table) ? $table : strtolower($name);
        $path  = $this->migration_path."{$timestamp}_{$name}.php";
        $migration = fopen($path, "w") or die("Unable to create the migration file!");
        $template  = in_array(is_string($custom) ? strtolower($custom) : $custom, [true, 'true', 1])
                        ? $this->_template_migration_custom($name, $table)
                        : $this->_template_migration($name, $table);

        fwrite($migration, $template);
        fclose($migration);

        $this->message("The migration file has successfully been created.\n$path");
    }

    /**
     * Create the seeder file
     *
     * @param string $name  [file_name]
     * @param string $table [table_name]
     *
     * @return void
     */
    protected function make_seeder($name, $table = '')
    {
        $path = $this->seed_path."$name.php";
        $seed = fopen($path, "w") or die("Unable to create seed file!");

        fwrite($seed, $this->_template_seeder($name, $table));
        fclose($seed);

        $this->message("The seeder $name has successfully been created.\n$path");
    }

    /**
     * The migration template
     *
     * @param string $name  [file_name]
     * @param string $table [table_name]
     *
     * @return string
     */
    private function _template_migration($name, $table)
    {
        return "
<?php

class Migration_$name extends CI_Migration
{
    /**
     * Create table with columns
     *
     * @return void
     */
    public function up()
    {
        \$this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ]
        ]);

        \$this->dbforge->add_key('id', true);
        \$this->dbforge->create_table('$table', true, ['ENGINE' => 'InnoDB']);
    }

    /**
     * Drop table
     *
     * @return void
     */
    public function down()
    {
        \$this->dbforge->drop_table('$table');
    }
}
        ";
    }

    /**
     * The custom migration template
     *
     * @param string $name  [file_name]
     * @param string $table [table_name]
     *
     * @return string
     */
    private function _template_migration_custom($name, $table)
    {
        return "
<?php

class Migration_$name extends My_Migration
{
    /**
     * Create table with columns
     *
     * @return void
     */
    public function up()
    {
        \$this->create_table('$table', [
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
                'primary_key' => true    // primary key
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'foreign_key' => [
                    'table' => 'users', // reference table
                    'field' => 'id'     // reference column
                ]
            ],
        ], 'INNODB');
    }

    /**
     * Drop table
     *
     * @return void
     */
    public function down()
    {
        \$this->dbforge->drop_table('$table');
    }
}
        ";
    }

    /**
     * The seeder template
     *
     * @param string $name  [file_name]
     * @param string $table [table_name]
     *
     * @return string
     */
    private function _template_seeder($name, $table)
    {
        return "
<?php

class $name extends Seeder
{

    private \$table = '$table';

    /**
     * Run seeder
     *
     * @return void
     */
    public function run()
    {
        \$this->db->truncate(\$this->table);

        //seed manually
        \$data = [
            'username' => 'admin',
            'password' => '9876543210'
        ];
        \$this->db->insert(\$this->table, \$data);

        //seed using faker
        for (\$i = 0; \$i < 10; \$i++) {
            \$data = [
                'username' => \$this->faker->unique()->userName,
                'password' => '1234567890',
            ];
            \$this->db->insert(\$this->table, \$data);
        }
    }
}
        ";
    }
}

/**
 * Seeder class
 */
class Seeder
{
    private $CI;
    protected $db;
    protected $dbforge;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->dbforge();
        $this->db = $this->CI->db;
        $this->dbforge = $this->CI->dbforge;
    }
    /**
     * Run another seeder
     *
     * @param string $seeder Seeder classname
     *
     * @return void
     */
    public function call($seeder)
    {
        $seeder = empty($seeder) ? 'DatabaseSeeder' : $seeder;
        $file   = $this->seed_path."$seeder.php";

        if (file_exists($file) && is_readable($file)) {
            include_once $file;

            (new $seeder)->run();

            echo 'Seed successfully.'.PHP_EOL;
            return;
        }
        throw new Exception("Unable read file [$file].");
    }

    /**
     * Get property
     *
     * @param string $property [property name]
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->CI->$property;
    }
}
