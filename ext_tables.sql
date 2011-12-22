
CREATE TABLE tx_t3users_log ( 
	uid int(11) NOT NULL auto_increment,
	pid int(11) NOT NULL default '0',
	tstamp datetime DEFAULT '0000-00-00 00:00:00',
	typ varchar(100) DEFAULT '' NOT NULL,
	feuser int(11) DEFAULT '0' NOT NULL,
	beuser int(11) DEFAULT '0' NOT NULL,
	recuid int(11) DEFAULT '0' NOT NULL,
	rectable varchar(255) DEFAULT '' NOT NULL,
	data mediumtext NOT NULL,

	PRIMARY KEY (uid),
#	funktioniert nicht, wenn die tabelle utf8 ist,
#	da für keys nur 1000 Bytes belegt werden können.
#	KEY idx_trt (typ,recuid,rectable),
	KEY idx_feusr (feuser)
);

CREATE TABLE fe_users (
	gender tinyint(4) DEFAULT '0' NOT NULL,
	first_name varchar(60) DEFAULT '' NOT NULL,
	last_name varchar(60) DEFAULT '' NOT NULL,
	birthday date DEFAULT '0000-00-00'
	confirmstring varchar(60) DEFAULT '' NOT NULL,
	t3usersroles int(11) DEFAULT '0' NOT NULL,
	beforelastlogin int(11) unsigned DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_t3users_roles'
#
#CREATE TABLE tx_t3users_roles (
#	uid int(11) NOT NULL auto_increment,
#	pid int(11) DEFAULT '0' NOT NULL,
#	tstamp int(11) DEFAULT '0' NOT NULL,
#	crdate int(11) DEFAULT '0' NOT NULL,
#	cruser_id int(11) DEFAULT '0' NOT NULL,
#	deleted tinyint(4) DEFAULT '0' NOT NULL,

#	name varchar(60) DEFAULT '' NOT NULL,
#	description text NOT NULL,
#	rights int(11) DEFAULT '0' NOT NULL,
#	owner int(11) DEFAULT '0' NOT NULL,

#	PRIMARY KEY (uid),
#	KEY parent (pid)
#);

#
# Table structure for table 'tx_t3users_role2owner_mm'
# uid_local used for tx_t3users_roles
#
#CREATE TABLE tx_t3users_role2owner_mm (
#	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
#	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
#	tablenames varchar(50) DEFAULT '' NOT NULL,
#	sorting int(11) unsigned DEFAULT '0' NOT NULL,
#	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
#	KEY uid_local (uid_local),
#	KEY uid_foreign (uid_foreign)
#);


#
# Table structure for table 'tx_t3users_right2role_mm'
# uid_local used for tx_t3users_rights
#
#CREATE TABLE tx_t3users_right2role_mm (
#	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
#	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
#	tablenames varchar(50) DEFAULT '' NOT NULL,
#	sorting int(11) unsigned DEFAULT '0' NOT NULL,
#	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
#	KEY uid_local (uid_local),
#	KEY uid_foreign (uid_foreign)
#);
