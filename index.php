<?php

include 'config.php';
include 'libs/restserver/RestServer.class.php';
include 'views/View.php';
include 'controllers/WorkerController.php';
include 'controllers/JobReaderController.php';
include 'controllers/JobWriterController.php';

$shouldCreateDb = !file_exists("db/jobs.db");
if($mysql) {
    $pdo = new PDO("mysql:host=".$mysql_host.";dbname=".$mysql_database,$mysql_user,$mysql_password);
} else {
    $pdo = new PDO("sqlite:db/jobs.db");
}

if($shouldCreateDb && !$mysql){
    $pdo->exec(file_get_contents("db/schema.sql"));
    if($pdo->errorCode() != "00000") {
        var_dump($pdo->errorInfo());
    }
}

if(!isset($_GET['q'])) {
    $_GET['q'] = "/";
}

$rest = new RestServer($_GET['q']) ;

if($auth) {
    $rAuth = $rest->getAuthenticator();
    $rAuth->requireAuthentication(true);
    if($user != $rAuth->getUser() ||  $password != $rAuth->getPassword()) {
        $rAuth->setAuthenticated(false);
    } else {
        $rAuth->setAuthenticated(true);
    }
}

$rest->setParameter("db",$pdo);
$rest->setParameter("php_command",$php_command);
$rest->setParameter("kill_command",$kill_command);

$rest->addMap('GET','/','View');

$rest->addMap('GET',"/workers","WorkerController::workers");
$rest->addMap('GET',"/workers/[\d\w]+","WorkerController::worker");
$rest->addMap('GET',"/workers/[\d\w]+/code","WorkerController::codeWorker");

$rest->addMap('PUT',"/workers/[\d\w]+","WorkerController::putWorker");
$rest->addMap('DELETE',"/workers/[\d\w]+","WorkerController::deleteWorker");

$rest->addMap('GET',"/jobs",'JobReaderController::jobs');
$rest->addMap('GET',"/jobs/[\d\w]+",'JobReaderController::job');
$rest->addMap('GET',"/jobs/[\d\w]+/log",'JobReaderController::jobLog');
$rest->addMap('GET',"/jobs/[\d\w]+/status",'JobReaderController::jobStatus');
$rest->addMap('GET',"/jobs/[\d\w]+/response",'JobReaderController::jobResponse');

$rest->addMap('POST',"/jobs",'JobWriterController::jobs');
$rest->addMap('DELETE',"/jobs/[\d\w]+",'JobWriterController::job');
$rest->addMap('PUT',"/jobs/[\d\w]+/pid",'JobWriterController::jobPid');
$rest->addMap('POST',"/jobs/[\d\w]+/log",'JobWriterController::jobLog');
$rest->addMap('PUT' ,"/jobs/[\d\w]+/status",'JobWriterController::jobStatus');
$rest->addMap('POST',"/jobs/[\d\w]+/response",'JobWriterController::jobResponse');

echo $rest->execute();

?>
