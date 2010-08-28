<?php

class WorkerController implements RestController {

    public function execute(RestServer $rest) {
        $rest->getResponse()->setResponse("Hello, world!");
        return $rest;
    }

    public function worker(RestServer $rest) {
        $workers = array();
        $class =$rest->getRequest() ->getURI(2);
        if(file_exists("workers_files/".$class.".php")) {
            $worker = new StdClass;
            $worker->name = $class ;
            $workers[] = $worker;
        }
        $rest->setParameter("data",$workers);
        return new View;
    }

    public function codeWorker(RestServer $rest) {
        $rest->getResponse()->setREsponse(@file_get_contents("workers_files/".$rest->getREquest()->GetURI(2).".php"));
        return $rest;
    }

    public function workers(RestServer $rest) {
        $workers = array();
        foreach(new DirectoryIterator('workers_files') as $file) {
            if(strpos($file->getFileName(),".") == 0) continue;
            $class = str_replace(".php","",$file->getFilename());
            $worker = new StdClass;
            $worker->name = $class ;
            $workers[] = $worker;
        }
        $rest->setParameter("data",$workers);
        return new View;
    }

    public function putWorker(RestServer $rest) {
        file_put_contents("workers_files/".$rest->getRequesT()->getURI(2).".php"
            ,$rest->getRequest()->getInput());
        return $this->worker($rest);
    }

    public function deleteWorker(RestServer $rest) {
        unlink("workers_files/".$rest->getRequesT()->getURI(2).".php");
        return $this->worker($rest);
    }

}
?>
