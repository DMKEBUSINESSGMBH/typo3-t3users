
CREATE TABLE tx_t3users_log (
    uid int(11) NOT NULL auto_increment,
    pid int(11) NOT NULL default '0',
    tstamp datetime DEFAULT NULL,
    typ varchar(50) DEFAULT '' NOT NULL,
    feuser int(11) DEFAULT '0' NOT NULL,
    beuser int(11) DEFAULT '0' NOT NULL,
    recuid int(11) DEFAULT '0' NOT NULL,
    rectable varchar(255) DEFAULT '' NOT NULL,
    data mediumtext NOT NULL,

    PRIMARY KEY (uid),
    KEY idx_trt (typ,recuid,rectable),
    KEY idx_feusr (feuser)
);

CREATE TABLE fe_users (
    gender tinyint(4) DEFAULT '0' NOT NULL,
    first_name varchar(60) DEFAULT '' NOT NULL,
    last_name varchar(60) DEFAULT '' NOT NULL,
    birthday date DEFAULT NULL,
    confirmstring varchar(60) DEFAULT '' NOT NULL,
    confirmtimeout datetime DEFAULT NULL,
    beforelastlogin int(11) unsigned DEFAULT '0' NOT NULL
);
