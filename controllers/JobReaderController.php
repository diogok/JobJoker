<?php

class JobReaderController implements RestController {

    public function execute(RestServer $rest) {
        $rest->getResponse()->setResponse("Hello, world!");
        return $rest;
    }

    public function jobs(RestServer $rest) {
        $db   = $rest->getParameter("db");
        $view = new View;
        $jobs = array();
        $query = $db->query("SELECT * FROM job ORDER BY starttime DESC");
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
        } else {
            $jobs = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach($jobs as $k=>$job){
            //$jobs[$k]['starttime'] = str_replace(".","", ((float)$job['starttime']) / 1000);
            $jobs[$k]['starttime'] = date("m/d/Y h:i:s A",(int) $job['starttime']);
            $jobs[$k]['stoptime'] = date("m/d/Y h:i:s A",(int) $job['stoptime']);
        }
        $rest->setParameter("data",$jobs);
        return $view;
    }

    public function job(RestServer $rest) {
        $db   = $rest->getParameter("db");
        $view = new View;
        $jobs = array();
        $id   = $rest->getRequest() ->getURI(2);
        $stmnt = $db->prepare("SELECT * FROM job WHERE id = ?");
        $query = $stmnt->execute(array($id));
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
        } else {
            $jobs = $stmnt->fetchAll(PDO::FETCH_ASSOC);
        }
        $rest->setParameter("data",$jobs);
        return $view;
    }

    public function jobStatus(RestServer $rest) {
        $db   = $rest->getParameter("db");
        $view = new View;
        $jobs = array();
        $id   = $rest->getRequest() ->getURI(2);
        $stmnt = $db->prepare("SELECT status  FROM job WHERE id = ?");
        $query = $stmnt->execute(array($id));
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
        } else {
            $jobs = $stmnt->fetchAll(PDO::FETCH_ASSOC);
        }
        $rest->setParameter("data",$jobs);
        return $view;
    }

    public function jobLog(RestServer $rest) {
        $db   = $rest->getParameter("db");
        $view = new View;
        $jobs = array();
        $id   = $rest->getRequest() ->getURI(2);
        $stmnt = $db->prepare("SELECT message FROM log WHERE job_id = ? order by time ASC ");
        $query = $stmnt->execute(array($id));
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
        } else {
            $jobs = $stmnt->fetchAll(PDO::FETCH_ASSOC);
        }
        $rest->setParameter("data",$jobs);
        return $view;
    }

    public function jobResponse(RestServer $rest) {
        $db   = $rest->getParameter("db");
        $jobs = array();
        $id   = $rest->getRequest() ->getURI(2);
        $stmnt = $db->prepare("SELECT message FROM response WHERE job_id = ? order by time ASC");
        $query = $stmnt->execute(array($id));
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
        } else {
            $jobs = $stmnt->fetchAll(PDO::FETCH_ASSOC);
        }
        $rest->setParameter("data",$jobs);
        return new View;
    }

}
?>
