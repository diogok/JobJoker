<?php

include_once 'libs/Worker.php';

class TestWorker extends Worker {

    public function getInformation() {
        return "This is a sample job";
    }

    public function run()  {
        $i = 0;
        while($this->isActive() && $i < $this->getParameter('count')) {
            $this->log("log ".$i);
            $this->response("response ".$i);
            exec("sleep ".$this->getParameter("sleep"));
            $i++;
        }
    }

}

?>
