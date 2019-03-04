<?php

require_once __DIR__.'/vendor/autoload.php';

use GO\Scheduler;

// Create a new scheduler
$scheduler = new Scheduler();

// ... configure the scheduled jobs (see below) ... => write code here

/**
 * Schedule cronjob.php to run every minute
 *
 */
//$scheduler->php(__DIR__.'/cronjob.php')->at('* * * * *')->output(__DIR__.'/cronjob.log');
$path = "C:/Users/huynhphat/Desktop/web/learning/php/CodeIgniter/vagrant-ci/";

$scheduler->php(__DIR__.'/cronjob.php')->at('* * * * *')->output($path.'/cronjob.log');

// Let the scheduler execute jobs which are due.
$scheduler->run();
