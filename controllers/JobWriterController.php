<?php

class JobWriterController implements RestController {

    public function execute(RestServer $rest) {
        $rest->getResponse()->setResponse("Hello, world!");
        return $rest;
    }

    public function jobs(RestServer $rest) {
        $db   = $rest->getParameter("db");
        $view = new View;
        $jobs = array();
        
        $req = json_decode($rest->getRequest()->getBody());

        $sql = "insert into job (id,worker,status,parameters) values (?,?,?,?)";
        $insert = $db->prepare($sql);
        if(!$insert) {
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
            return $view;
        }

        $id = sha1($req->worker.":".microtime(true));
        $worker = $req->worker;
        $status = 'idle';
        $parameters = json_encode($req->parameters);

        $ok = $insert->execute(array($id,$worker,$status,$parameters));
        if(!$ok) {
            $view->success = false;
            $view->message = implode("\n",$insert->errorinfo());
        } else {
            $jobs = $db->query("select * from job where id = '".$id."'")->fetchAll(PDO::FETCH_ASSOC);
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
            return $view;
        }
        $job = $stmnt->fetchObject();
        if($job->status == "active") {
            $view->success = false;
            $view->message = "Can't delete a running job'";
            return $view;
        }

        $stmnt = $db->prepare("DELETE FROM job WHERE id = ?");
        $query = $stmnt->execute(array($id));
        if(!$query) { 
            var_dump($stmnt->errorInfo());
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
        } else {
            $jobs = $stmnt->fetchAll();

            $stmnt = $db->prepare("DELETE FROM log WHERE job_id = ?");
            $query = $stmnt->execute(array($id));
            $stmnt = $db->prepare("DELETE FROM response WHERE job_id = ?");
            $query = $stmnt->execute(array($id));
        }

        $rest->setParameter("data",$jobs);
        return $view;
    }

    public function jobLog(RestServer $rest) {
        $db   = $rest->getParameter("db");
        $view = new View;

        $stmnt = $db->prepare("insert into log (id, job_id, time, message) values (?,?,?,?)");
        $id =  sha1($job_id.":".microtime(true));
        $job_id = $rest->getRequest() ->getURI(2);
        $time = microtime(true);
        $message = $rest->getRequest()->getBody();
        $query = $stmnt->execute(array($id,$job_id,$time,$message));
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
            return $view;
        }

        $jobs = array();
        $stmnt = $db->prepare("SELECT message FROM log WHERE id = ?");
        $query = $stmnt->execute(array($id));
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
        } else {
            $jobs = $stmnt->fetchAll();
        }
        $rest->setParameter("data",$jobs);
        return $view;
    }

    public function jobResponse(RestServer $rest) {
        $db   = $rest->getParameter("db");
        $view = new View;

        $stmnt = $db->prepare("insert into response (id, job_id, time, message) values (?,?,?,?)");
        $id =  sha1($job_id.":".microtime(true));
        $time = microtime(true);
        $job_id = $rest->getRequest() ->getURI(2);
        $message = $rest->getRequest()->getBody();
        $query = $stmnt->execute(array($id,$job_id,$time,$message));
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
            return $view;
        }

        $jobs = array();
        $stmnt = $db->prepare("SELECT message FROM response WHERE id = ?");
        $query = $stmnt->execute(array($id));
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
        } else {
            $jobs = $stmnt->fetchAll();
        }
        $rest->setParameter("data",$jobs);
        return $view;
    }
    
    public function jobPid(RestServer $rest) {
        $db   = $rest->getParameter("db");
        $view = new View;
        $pid  = $rest->getRequest()->getBody();
        $id = $rest->getRequest() ->getURI(2);

        $stmnt = $db->prepare("update job set pid = ? where id = ?");
        $query = $stmnt->execute(array($pid,$id));
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
            return $view;
        }

        $jobs = array();
        $stmnt = $db->prepare("SELECT * FROM job WHERE id = ?");
        $query = $stmnt->execute(array($id));
        if(!$query) { 
            $view->success = false;
            $view->message = implode("\n",$db->errorinfo());
        } else {
            $jobs = $stmnt->fetchAll();
        }
        $rest->setParameter("data",$jobs);
        return $view;
    }

    public function jobStatus(RestServer $rest) {
        $db   = $rest->getParameter("db");
        $view = new View;
        $php  = $rest->getParameter("php_command");
        $kill  = $rest->getParameter("kill_command");

        $status = $rest->getRequest()->getBody();
        $id = $rest->getRequest()->getURI(2);

        $stmt = $db->prepare("SELECT * FROM job WHERE id = ?");
        $ok = $stmt->execute(array($id));
        if(!$ok) {
            $view->success = false;
            $view->message = implode("\n",$stmt->errorinfo());
            return $view;
        }
        $job = $stmt->fetchObject();

        if($job->status == "done" || $job->status == "error") {
            $view->success =false;
            $view->message ="Job has finished";
            return $view;
        }

        if($status == "start" && $job->status == "idle") {
            exec($php." run.php ".$id." > /dev/null &");
            $stmt = $db->prepare("UPDATE job SET starttime = ? where id = ?");
            $ok = $stmt->execute(array(microtime(true),$id));
        } else if($status == "stop" && $job->status == "active") {
            exec($kill." ".$job->pid);
        } else if($status == "kill") {
            exec($kill." -9 ".$job->pid);
            $stmt = $db->prepare("UPDATE job SET stoptime = ? where id = ?");
            $ok = $stmt->execute(array(microtime(true),$id));
            $stmt = $db->prepare("UPDATE job SET status = 'stop' where id = ?");
            $ok = $stmt->execute(array($id));
        } else if($status == "active") {
            $stmt = $db->prepare("UPDATE job SET status = ? where id = ?");
            $ok = $stmt->execute(array($status,$id));
            if(!$ok) {
                $view->success = false;
                $view->message = implode("\n",$stmt->errorinfo());
            }
        } else if($status == "done" || $status == "error") {
            $stmt = $db->prepare("UPDATE job SET status = ? where id = ?");
            $ok = $stmt->execute(array($status,$id));
            if(!$ok) {
                $view->success = false;
                $view->message = implode("\n",$stmt->errorinfo());
            }
            $stmt = $db->prepare("UPDATE job SET stoptime = ? where id = ?");
            $ok = $stmt->execute(array(microtime(true),$id));
        }

        $stmt = $db->prepare("SELECT * FROM job WHERE id = ?");
        $ok   = $stmt->execute(array($id));
        $job  = $stmt->fetchObject();
        $rest->setParameter("data",array($job));
        return $view;
    }

}
?>
