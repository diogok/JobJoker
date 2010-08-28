<?php
require_once 'PHPUnit/Framework.php';
require_once 'restserver/RestClient.class.php';

class JobjokerTest extends PHPUnit_Framework_TestCase {

    public $api;

    function setUp() {
        include "config.php";
        $this->api = $api;
    }
    
    function testWorkerListings() {
        $workers = json_decode(RestClient::get($this->api."/workers")->getResponse());
        $this->assertTrue(count($workers->data) >= 1);
        $workers = json_decode(RestClient::get($this->api."/workers/TestWorker")->getResponse());
        $this->assertEquals($workers->data[0]->name , "TestWorker");
    }

    function testCreateGetAndDeleteJob() {
        $response = RestClient::post($this->api."/jobs"
            ,'{"worker":"TestWorker","parameters":null}',null,null,"application/json")
            ->getResponse();
        $jobs = json_decode($response);
        $this->assertTrue($jobs->success);
        $this->assertEquals("TestWorker",$jobs->data[0]->worker);

        $id = $jobs->data[0]->id;
        $resp = RestClient::delete($this->api."/jobs/".$id)->getResponse();
        $del = json_decode($resp);
        $this->assertTrue($del->success);
    }

    function testJobLog() {
        $response = RestClient::post($this->api."/jobs"
            ,'{"worker":"TestWorker","parameters":null}',null,null,"application/json")
            ->getResponse();
        $id = json_decode($response)->data[0]->id;

        RestClient::post($this->api."/jobs/".$id."/log","foo",null,null,"text/plain");
        RestClient::post($this->api."/jobs/".$id."/log","bar",null,null,"text/plain");

        $response = RestClient::get($this->api."/jobs/".$id."/log")->getResponse();
        $logs = json_decode($response);

        $this->assertEquals("foo",$logs->data[0]->message);
        $this->assertEquals("bar",$logs->data[1]->message);

        RestClient::delete($this->api."/jobs/".$id);
    }
    
    function testJobResponse() {
        $response = RestClient::post($this->api."/jobs"
            ,'{"worker":"TestWorker","parameters":null}',null,null,"application/json")
            ->getResponse();
        $id = json_decode($response)->data[0]->id;

        RestClient::post($this->api."/jobs/".$id."/response","foo",null,null,"text/plain");
        RestClient::post($this->api."/jobs/".$id."/response","bar",null,null,"text/plain");

        $response = RestClient::get($this->api."/jobs/".$id."/response")->getResponse();
        $logs = json_decode($response);

        $this->assertEquals("foo",$logs->data[0]->message);
        $this->assertEquals("bar",$logs->data[1]->message);

        RestClient::delete($this->api."/jobs/".$id);
    }

    function testJobPid() {
        $response = RestClient::post($this->api."/jobs"
            ,'{"worker":"TestWorker","parameters":null}',null,null,"application/json")
            ->getResponse();
        $id = json_decode($response)->data[0]->id;

        RestClient::put($this->api."/jobs/".$id."/pid","123",null,null,"text/plain");

        $response = RestClient::get($this->api."/jobs/".$id)->getResponse();
        $logs = json_decode($response);

        $this->assertEquals("123",$logs->data[0]->pid);

        RestClient::delete($this->api."/jobs/".$id);
    }

    function testJobStatus() {
        $response = RestClient::post($this->api."/jobs"
            ,'{"worker":"TestWorker","parameters":null}',null,null,"application/json")
            ->getResponse();
        $id = json_decode($response)->data[0]->id;

        RestClient::put($this->api."/jobs/".$id."/status","done",null,null,"text/plain");

        $response = RestClient::get($this->api."/jobs/".$id)->getResponse();
        $logs = json_decode($response);

        $this->assertEquals("done",$logs->data[0]->status);

        RestClient::delete($this->api."/jobs/".$id);
    }

    function testJobrun() {
        $response = RestClient::post($this->api."/jobs"
            ,'{"worker":"TestWorker","parameters":{"count":1,"sleep":3}}',null,null,"application/json")
            ->getResponse();
        $id = json_decode($response)->data[0]->id;

        RestClient::put($this->api."/jobs/".$id."/status","start",null,null,"text/plain")->getResponse();
        exec("sleep 3");

        $resp = RestClient::get($this->api."/jobs/".$id)->getResponse();
        $job = json_decode($resp);
        $this->assertEquals("active",$job->data[0]->status);
        
        exec("sleep 5");

        $resp = RestClient::get($this->api."/jobs/".$id)->getResponse();
        $job = json_decode($resp);
        $this->assertEquals("done",$job->data[0]->status);

        $resp = RestClient::get($this->api."/jobs/".$id."/log")->getResponse();
        $job = json_decode($resp);
        $this->assertEquals("log 0",$job->data[1]->message);

        $resp = RestClient::get($this->api."/jobs/".$id."/response")->getResponse();
        $job = json_decode($resp);
        $this->assertEquals("response 0",$job->data[0]->message);

        RestClient::delete($this->api."/jobs/".$id);
    }

    function testJobrunAndStop() {
        $response = RestClient::post($this->api."/jobs"
            ,'{"worker":"TestWorker","parameters":{"count":50,"sleep":3}}',null,null,"application/json")
            ->getResponse();
        $id = json_decode($response)->data[0]->id;

        RestClient::put($this->api."/jobs/".$id."/status","start",null,null,"text/plain")->getResponse();
        exec("sleep 3");

        $resp = RestClient::get($this->api."/jobs/".$id)->getResponse();
        $job = json_decode($resp);
        $this->assertEquals("active",$job->data[0]->status);

        $r = RestClient::delete($this->api."/jobs/".$id);
        $resp = json_decode($r->getResponse());
        $this->assertFalse($resp->success);

        RestClient::put($this->api."/jobs/".$id."/status","stop",null,null,"text/plain")->getResponse();

        exec("sleep 12");

        $resp = RestClient::get($this->api."/jobs/".$id)->getResponse();
        $job = json_decode($resp);
        $this->assertEquals("done",$job->data[0]->status);

        $resp = RestClient::get($this->api."/jobs/".$id."/log")->getResponse();
        $job = json_decode($resp);
        $this->assertEquals("log 0",$job->data[1]->message);

        $resp = RestClient::get($this->api."/jobs/".$id."/response")->getResponse();
        $job = json_decode($resp);
        $this->assertEquals("response 0",$job->data[0]->message);

        $r = RestClient::delete($this->api."/jobs/".$id);
        $resp = json_decode($r->getResponse());
        $this->assertTrue($resp->success);
    }

    function testJobrunAndKill() {
        $response = RestClient::post($this->api."/jobs"
            ,'{"worker":"TestWorker","parameters":{"count":50,"sleep":3}}',null,null,"application/json")
            ->getResponse();
        $id = json_decode($response)->data[0]->id;

        RestClient::put($this->api."/jobs/".$id."/status","start",null,null,"text/plain")->getResponse();
        exec("sleep 3");

        RestClient::put($this->api."/jobs/".$id."/status","kill",null,null,"text/plain")->getResponse();

        exec("sleep 1");

        $resp = RestClient::get($this->api."/jobs/".$id)->getResponse();
        $job = json_decode($resp);
        $this->assertEquals("stop",$job->data[0]->status);

        $r = RestClient::delete($this->api."/jobs/".$id);
        $resp = json_decode($r->getResponse());
        $this->assertTrue($resp->success);
    }

    function testWorkerCreation() {
        $worker = '<?php include "libs/Worker.php"; class MyWorker extends Worker 
                    { public function getInformation() {return "";} public function run() {$this->log("MyWorker");} }';

        RestClient::put($this->api."/workers/MyWorker",$worker,null,null,"application/php");

        $r0 = RestClient::get($this->api."/workers/MyWorker")->getResponse();
        $w = json_decode($r0);
        $this->assertEquals("MyWorker",$w->data[0]->name);

        $response = RestClient::post($this->api."/jobs"
            ,'{"worker":"MyWorker","parameters":null}',null,null,"application/json")
            ->getResponse();
        $id = json_decode($response)->data[0]->id;
        RestClient::put($this->api."/jobs/".$id."/status","start",null,null,"text/plain")->getResponse();
        exec("sleep 1");
        $resp = RestClient::get($this->api."/jobs/".$id)->getResponse();
        $job = json_decode($resp);
        $this->assertEquals("done",$job->data[0]->status);

        $r = RestClient::delete($this->api."/jobs/".$id);
        $resp = json_decode($r->getResponse());
        $this->assertTrue($resp->success);

        RestClient::delete($this->api."/workers/MyWorker");
        $r0 = RestClient::get($this->api."/workers/MyWorker")->getResponse();
        $w = json_decode($r0);
        $this->assertEquals(0,count($w->data));
    }
}

?>
