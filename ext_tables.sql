#
# Table structure for table 'tx_maps2_domain_model_poicollection'
#
CREATE TABLE tx_maps2_domain_model_poicollection
(
	configuration_map        text,
	latitude                 numeric(11, 6) DEFAULT '0.000000' NOT NULL,
	longitude                numeric(11, 6) DEFAULT '0.000000' NOT NULL,
);
