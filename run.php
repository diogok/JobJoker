<?php
declare(ticks = 1);

if(!isset($argv[1])) {
    echo "Must pass a job id\n";
    exit;
}

include_once 'config.php';
include_once 'libs/restserver/RestClient.class.php';

$request = RestClient::get($api."/jobs/".$argv[1]);
$data = json_decode($request->getResponse());
if(count($data->data) < 1) {
    echo "Bad Job description\n";
    exit;
}

RestClient::put($api."/jobs/".$argv[1]."/pid",getmypid(),null,null,"text/plain");

$work = $data->data[0];

$class = $work->worker ;
$parameters = json_decode($work->parameters);
$parameters->_id = $work->id;
$parameters->_api = $api;

include "workers_files/".$class.".php";

$worker = new $class ;
$worker->setParameters($parameters);

if(function_exists("pcntl_signal")) {
    pcntl_signal(SIGTERM, array($worker,'stop'));
}

$worker->start();

?>
