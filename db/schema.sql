CREATE TABLE IF NOT EXISTS job (
    id varchar(220),
    pid int(10),
    worker varchar(200),
    starttime long,
    stoptime long,
    status varchar(220),
    parameters text,
    primary key (id)
); 

CREATE TABLE IF NOT EXISTS response (
    id varchar(220),
    job_id varchar(220),
    time long,
    message text,
    primary key(id)
);

CREATE TABLE IF NOT EXISTS log (
    id varchar(220),
    job_id varchar(220),
    time long,
    message text,
    primary key(id)
);
