<?php
define('APP_PATH_CLI', 'application/');

process($argc, $argv);

function process($argc, $argv)
{
    if ($argc < 3 && $argc > 4) {
        echo "\nError: Only allowed three or four arguments.\n";
        return;
    }

    $path = '';
    if ($argc === 3) {
        $command   = $argv[1]. ' '.$argv[2];
        $pattern   = $argv[1];
        $name      = $argv[2];
        $pathSplit = explode('/', $name);
    } else {
        $command   = $argv[1]. ' '.$argv[2]. ' '.$argv[3];
        $pattern   = $argv[1].' '.$argv[2];
        $name      = $argv[3];
        $pathSplit = explode('/', $name);
    }

    if (empty($name)) {
        echo "\nError: missing name.\n";
        return;
    }

    if (count($pathSplit) > 1) {
        $name = array_pop($pathSplit);
        $path = '/'.implode('/', $pathSplit);
    }

    if (!check_command($pattern)) {
        echo "\nError: This command [$command] not found.\n";
        return;
    }

    $type = commands($pattern);
    $path = APP_PATH_CLI.plural($type).$path;

    make_file($type, $name, $path);
}

function check_command($pattern)
{
    return array_key_exists($pattern, commands());
}

function make_file($type, $name, $path)
{
    if (!function_exists($type)) {
        echo "\nError: This command [$command] not found.\n";
        return;
    }

    if (!is_dir($path)) {
        mkdir($path);
    }

    $content = $type($name);
    $file    = $path.'/'.$name.'.php';

    $fp      = fopen($file, 'w');
    fwrite($fp, $content);
    fclose($fp);

    echo "\n Created a new file: $file\n";
}

function plural($name = '')
{
    return is_string($name) && $name ? $name.'s' : '';
}

function commands($command = null)
{
    $commands = [
        'g c'             => 'controller',
        'make:controller' => 'controller',
        'g m'             => 'model',
        'make:model'      => 'model'
    ];
    return $command ? $commands[$command] : $commands;
}

/**
 * Create the controller file
 *
 * Command:
 *      php artisan g c path_to_controller_file
 *      php artisan make:controller path_to_controller_file
 *
 *      php artisan g c test/Demo
 *      php artisan make:controller test/Demo
 *
 * @return string
 */
function controller($name = 'ClassName')
{
    return "
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class $name extends CI_Controller
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
        //
    }
}
";
}

/**
 * Create the model file
 *
 * Command:
 *      php artisan g m path_to_controller_file
 *      php artisan make:model path_to_controller_file
 *
 *      php artisan g m test/Demo
 *      php artisan make:model test/Demo
 *
 * @return string
 */
function model($name = 'ClassName')
{
    return "
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class $name extends CI_Model
{
    //
}
";
}
