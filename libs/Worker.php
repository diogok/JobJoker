<?php

include_once 'restserver/RestClient.class.php';

define("DONE",'done');
define("ERROR",'error');
define("ACTIVE",'active');
define("STOP",'stop');
define("IDLE",'idle');

abstract class Worker {
    private $parameters ;
    private $active = true;

    public function __construct()  {
    }

    public function setParameters($parameters){
        $this->parameters = $parameters ;
        return $this;
    }

    public function getParameter($parameter) {
        return $this->parameters->$parameter;
    }

    public function start() {
        $this->log('STARTED');
        $this->active = true;
        $this->setStartTime(time());
        try {
            $this->setStatus('active');
            $this->run();
            $this->setStatus('done');
        } catch (Exception $e) {
            $this->setStatus('error');
            $this->log("EXCEPTION");
            $this->log($e->getFile()." at ".$e->getLine());
            $this->log($e->getMessage());
            $this->log($e->getTraceAsString());
        }
        $this->active = false;
        $this->setStopTime(time());
        $this->log('ENDED');
    }

    public function stop() {
        $this->active = false;
        $this->log('STOPED');
        $this->setStatus('stop');
        $this->setStopTime(time());
    }

    public function isActive() {
        return $this->active ;
    }

    private function setStatus($status) {
        $status_url = $this->getParameter("_api")."/jobs/".$this->getParameter("_id")."/status";
        switch($status) {
            case ACTIVE:
                RestClient::put($status_url,$status,null,null,"text/plain");
                break;
            case DONE:
                RestClient::put($status_url,$status,null,null,"text/plain");
                break;
            case STOP:
                RestClient::put($status_url,$status,null,null,"text/plain");
                break;
            case IDLE:
                RestClient::put($status_url,$status,null,null,"text/plain");
                break;
            case ERROR:
                RestClient::put($status_url,$status,null,null,"text/plain");
                break;
            default:
                break;
        }
    }

    private function setStartTime($time) {
        $time_url = $this->getParameter("_api")."/".$this->getParameter("_id")."/starttime";
//        RestClient::put($time_url,$time,null,null,"text/plain");
        return $this;
    }

    private function setStopTime($time) {
        $time_url = $this->getParameter("_api")."/".$this->getParameter("_id")."/stoptime";
//        RestClient::put($time_url,$time,null,null,"text/plain");
        return $this;
    }

    public function log($message) {
        if(is_string($message)) {
            $log_url = $this->getParameter("_api")."/jobs/".$this->getParameter("_id")."/log";
            RestClient::post($log_url,$message,null,null,"text/plain");
        }
        return $this;
    }

    public function response($message) {
        if(is_string($message)) {
            $resp_url = $this->getParameter("_api")."/jobs/".$this->getParameter("_id")."/response";
            RestClient::post($resp_url,$message,null,null,"text/plain");
        }
        return $this;
    }

    public abstract function run() ;
    public abstract function getInformation();

}

?>
