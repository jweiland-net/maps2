#
# Table structure for table 'tx_maps2_domain_model_poicollection'
#
CREATE TABLE tx_maps2_domain_model_poicollection (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	collection_type varchar(255) DEFAULT '' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	configuration_map varchar(255) DEFAULT '' NOT NULL,
	latitude double(11,6) DEFAULT '0.000000' NOT NULL,
	longitude double(11,6) DEFAULT '0.000000' NOT NULL,
	radius int(11) unsigned DEFAULT '0' NOT NULL,
	pois int(11) unsigned DEFAULT '0' NOT NULL,
	stroke_color varchar(7) DEFAULT '' NOT NULL,
	stroke_opacity varchar(5) DEFAULT '' NOT NULL,
	stroke_weight varchar(5) DEFAULT '' NOT NULL,
	fill_color varchar(7) DEFAULT '' NOT NULL,
	fill_opacity varchar(5) DEFAULT '' NOT NULL,
	address varchar(255) DEFAULT '' NOT NULL,
	info_window_content text,
	marker_icons int(11) unsigned DEFAULT '0' NOT NULL,
	marker_icon_width int(11) unsigned DEFAULT '0' NOT NULL,
	marker_icon_height int(11) unsigned DEFAULT '0' NOT NULL,
	marker_icon_anchor_pos_x int(11) unsigned DEFAULT '0' NOT NULL,
	marker_icon_anchor_pos_y int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tx_maps2_domain_model_poi'
#
CREATE TABLE tx_maps2_domain_model_poi (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	poicollection int(11) unsigned DEFAULT '0' NOT NULL,

	latitude double(11,6) DEFAULT '0.000000' NOT NULL,
	longitude double(11,6) DEFAULT '0.000000' NOT NULL,
	pos_index int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	sorting int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tt_address'
#
CREATE TABLE tt_address (
	tx_maps2_uid int(11) unsigned DEFAULT '0' NOT NULL
);

#
# Extend table structure of table 'sys_category'
#
CREATE TABLE sys_category (
	maps2_marker_icons int(11) unsigned DEFAULT '0' NOT NULL,
	maps2_marker_icon_width int(11) unsigned DEFAULT '0' NOT NULL,
	maps2_marker_icon_height int(11) unsigned DEFAULT '0' NOT NULL,
	maps2_marker_icon_anchor_pos_x int(11) unsigned DEFAULT '0' NOT NULL,
	maps2_marker_icon_anchor_pos_y int(11) unsigned DEFAULT '0' NOT NULL
);
