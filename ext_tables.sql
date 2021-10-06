#
# Table structure for table 'tx_maps2_domain_model_poicollection'
#
CREATE TABLE tx_maps2_domain_model_poicollection (
	map_provider varchar(25) DEFAULT '' NOT NULL,
	collection_type varchar(255) DEFAULT '' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	configuration_map text,
	latitude double(11,6) DEFAULT '0.000000' NOT NULL,
	longitude double(11,6) DEFAULT '0.000000' NOT NULL,
	radius int(11) unsigned DEFAULT '0' NOT NULL,
	stroke_color varchar(7) DEFAULT '' NOT NULL,
	stroke_opacity varchar(5) DEFAULT '' NOT NULL,
	stroke_weight varchar(5) DEFAULT '' NOT NULL,
	fill_color varchar(7) DEFAULT '' NOT NULL,
	fill_opacity varchar(5) DEFAULT '' NOT NULL,
	address varchar(255) DEFAULT '' NOT NULL,
	info_window_content text,
	info_window_images int(11) unsigned DEFAULT '0' NOT NULL,
	marker_icons int(11) unsigned DEFAULT '0' NOT NULL,
	marker_icon_width int(11) unsigned DEFAULT '0' NOT NULL,
	marker_icon_height int(11) unsigned DEFAULT '0' NOT NULL,
	marker_icon_anchor_pos_x int(11) unsigned DEFAULT '0' NOT NULL,
	marker_icon_anchor_pos_y int(11) unsigned DEFAULT '0' NOT NULL,
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
