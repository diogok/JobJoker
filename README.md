# JobJoker

JobJoker is a tool for managing and running jobs/proccess on PHP, keeping its state, log and response. 

<img src="http://github.com/diogok/jobjoker/raw/master/libs/imgs/jj-0.png" alt='JobJoker - Job queue in PHP' title="JobJoker - Job queue in PHP" />

It provides a (almost) restful api for managing the jobs, and a friendly interface for visual management.

JobJoker lets you write proccess in PHP, run those proccess, stop them, get the log and responses at real time using a very simple API/UI.

Let me know if something breaks...

## Core Concepts

A **Worker** is a php file that implements the job to be done. It is just a plain PHP class, that implements a <tt>run</tt> method, it can import libraries, access webservices, access a database... and do anything that a PHP script may do. A worker have function special for <tt>log</tt> and <tt>response</tt>.

A **Job** is like an instance of a worker, it is the proccess that will run the worker. A job may contain any numbers of **parameters** that will be passed for the worker.

The **Job** may be in the following states:

    **idle** when it was just created
    **active** when it is running
    **stop** if it was force to stop
    **error** if something went wrong
    **done** if it has finnished without problems

The **Job** may go from <tt>idle</tt> to <tt>active</tt> and from <tt>active</tt> to anything but <tt>idle</tt>. A job may be deleted if it is not <tt>active</tt>.

You can <tt>start</tt>, <tt>stop</tt> or <tt>kill</tt> a job.

## Installation and configuration

Just put it in a folder under your webserver folder, make sure it support URLRewrite on .htaccess.

You have a few options on config.php:

    $api -> Is the full URL for accessing this api 
    $php_command -> The command to run php
    $kill_command -> The command to kill a process

    $mysql -> Set to true to use a mysql db (better performance)
    $mysql_host -> MySql host
    $mysql_database -> MySql database
    $mysql_user -> MySql username
    $mysql_password -> MySql password

    $auth -> Set true to require authentication 
    $user -> The authentication user
    $password -> The authentication password

If you intend to use MySql, you must create the schema on <tt>db/schema.sql</tt>.

Access the URL and make some tests by yourself.

## User Interface (admin)

By accessing the API you will be redirect to a basic admin interface (libs/ui.html), from there you have full control over the Jobjoker jobs queue and workers.

On the tab **Manage Jobs** you have the list of available jobs and their status. By selecting you can perform the actions on the bottom buttons: [screenshot](http://github.com/diogok/jobjoker/raw/master/libs/imgs/jj-1.png) 

    Reload: reload the list of jobs
    Start: starts a job that is idle
    Stop: send the stop signal to an active job (may take a little time to actually stop)
    Kill: abruptely kills a job (at any state), use carefully after trying stop
    Delete: removes the job (that may no be active)
    Log: open a window with the current log of the job
    Response: open a window with the current response of the job

On the tab **Create a Job** you have the list of available jobs and their status. you can create a job, by choosing its worker and defining (or not) its parameters as a JSON formatted string. [screenshot](http://github.com/diogok/jobjoker/raw/master/libs/imgs/jj-1.png)

On the tab **Manage Workers** you have the list of available jobs and their status. you have a list of available workers, by selecting a worker you have a few option on them: [screenshot](http://github.com/diogok/jobjoker/raw/master/libs/imgs/jj-1.png)

    Reload: reload the list of workers
    Delete: delete the worker
    Code: view the source code of the worker, and allows to edit and save it

On the tab **Create a Worker** you have the list of available jobs and their status. you can create a new Worker, by choosing its name and pasting it code. [screenshot](http://github.com/diogok/jobjoker/raw/master/libs/imgs/jj-1.png)

## API

The API uses Json like <tt>{"message":"some message","success":true,"data":[{"foo":"bar"}]}</tt> if not specified other.

    GET / Will lead you to admin interface
    GET /workers Returns the list of workers
    GET /workers/WorkerName Returns the data of given WorkerName
    GET /workers/WorkerName/code Return the text/plain  php code of the Worker
    PUT /workers/WorkerName Will create/update WorkerName with php code content sent as text/plain (just send the php code as the request body)
    DELETE /workers/WorkerName Will delete WorkerName

    GET /jobs Will return all jobs
    GET /jobs/job-id Will return data about this job
    GET /jobs/job-id/log Will return current log of this job
    GET /jobs/job-id/status Will return the current status of the job
    GET /jobs/job-id/respose Will return current response of the job

    POST /jobs Send a json like {"worker":"WorkerName","parameters":{"foo":"bar"}} to create a job, it will return the job with the id
    DELETE /jobs/job-id Will delete this job
    POST /jobs/job-id/log Will append sent text/plain to log (send just the text to append, no json)
    POST /jobs/job-id/response Will append sent text/plain to response (send just the text to append, no json)

    PUT /jobs/job-id/status Send the text to set the status of the job. If "start" is sent, job will start. If "stop", job will try to sto. If "kill", job will be forced to stop.

## How it works (a few implementation details)

There is a SQLite database (or a mysql) and there is an kinda-rest API.

The **Worker**s are the files on the <tt>workers_files</tt>, they extend the Worker class, on libs/Worker.php.

The Worker class implements the methods to performing logging/responsing using the api, and makes the parameters available for the **Worker** implementation. It also keep the status of the **Job** and perform little error handling.

A **Job** is an entry on the database, with certain paramaters, a PID (if it started), timestamps, logs and response. Once a **Job** is aked to start by the API, it runs the run.php script in background (using exec) and give it the **Job** ID.

The run.php will ask back the api for the job info, include the needed **Worker** class, give it its parameters and call the Worker start method (that will call the class run method).

The **Job** will run until the run method returns, or if it is stoped or killed. 

If the API is asked to stop a job it will send a kill signal (exec kill), this signal will be traped by the Worker, witch will set the flag to stop whenever possible, gracefully. The class must check <tt>isActive</tt> from time to time to properly exit if a stop was attempted.

If the API is asked to kill a job it will try to kill it proccess (exec kill -9).

The <tt>api</tt> start at index.php, it uses the restserver for url routing and just PDO for database. To get around, simply follow the addMap methods, it points a method (get/put/post) to a url regex and the class::method that will handle that. Controllers are on the controllers folder, Views on the views folder, everything else should be in libs.

The WorkerController class implements the logic to list the available workers, to create and delete them.

The JobReaderController implements the listings of jobs, its statuses, logs and response.

The JobWriterController implements the logic to interact with the Jobs, creating, updating, logging, responsing, start/stop/kill and delete. 

There are tests for the API on libs/JobjokerTest.php

## TODO

- support windows?
- support nginx URLRewrite
- rewrite this doc
- write unit tests (now have only tests for the api)
- implement the use of o getInformation of the workers
- implement parameters validation
- implement job timeout?
- implement job scheduler?
- suport better authentication
- improve API mime type consistency accross the api
- refactor (Those damn ugly controllers, sqls...)
- what else?
