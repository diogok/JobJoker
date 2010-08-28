<?php

class View implements RestView {

    public $success = true;
    public $message = "";

    public function show(RestServer $rest) {
        if($rest->getRequesT()->getURI() == "/") {
            return $this->ui($rest);
        } else if($rest->getRequest()->getExtension() == "csv") {
            return $this->csv($rest);
        } else {
            return $this->json($rest);
        }
    }

    public function ui($rest) {
        $rest->getResponse()->addHeader("Location: libs/ui.html");
        return $rest;
    }

    public function json($rest) {
        if($this->success) {
            $data = $rest->getParameter("data");
        } else {
            $data = array();
        }

        $response = new StdClass ;
        $response->success = $this->success;
        $response->message = $this->message;
        $response->total   = count($data);
        $response->data    = $data;

        $rest->getResponse()->setResponse(json_encode($response));
        return $rest;
    }

    public function csv($rest) {
        if(!$this->success) {
            $rest->getResponse()->setResponse($message);
            return $rest;
        }

        $data = $rest->getParameter("data");
        if(count($data) >= 1)  {
            $columns = array();
            foreach($data[0] as $k=>$value) {
                $columns[] = '"'.$k.'"';
            }
            $rest->getResponse()->addResponse(implode(",",$columns)."\n");
            foreach($data as $record) {
                $values = array();
                foreach($record as $value) {
                    $values[] = '"'.$value.'"';
                }
                $rest->getResponse()->addResponse(implode(",",$values)."\n");
            }
        }
        return $rest;
    }
}
