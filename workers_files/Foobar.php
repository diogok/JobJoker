<?php

include_once 'libs/Worker.php';

class Foobar extends Worker {

    public function getInformation() {
        return "This is a sample job";
    }

    public function run()  {
        $this->log("foobar");
        $this->response("foobar");
    }

}

?>
