#
# Table structure for table 'tx_eventsubmission_domain_model_job'
#
CREATE TABLE tx_eventsubmission_domain_model_job
(
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    uuid                    varchar(100)     DEFAULT '' NOT NULL,
    email                   varchar(255)     DEFAULT '' NOT NULL,
    request_date_time       bigint(20) unsigned DEFAULT 0  NOT NULL,
    payload                 text             DEFAULT NULL,
    response_code           int(1) unsigned  DEFAULT 0  NOT NULL,
    is_api_error            int(1) unsigned  DEFAULT 0  NOT NULL,
    job_triggered_date_time bigint(20) unsigned DEFAULT 0  NOT NULL,
    is_done                 int(1) unsigned  DEFAULT 0  NOT NULL,
    internal_log_message    varchar(255)     DEFAULT '' NOT NULL,
    is_internal_error       int(1) unsigned  DEFAULT NULL,
    approved                int(1) unsigned  DEFAULT 0  NOT NULL,
    event                   int(11) unsigned DEFAULT 0,
    status                  int(4) unsigned DEFAULT 0,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY status (status)
);
