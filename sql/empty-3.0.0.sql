CREATE TABLE IF NOT EXISTS `glpi_plugin_appliances_optvalues` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_appliances_appliances_id` int(11) NOT NULL default '0',
   `vvalues` int(11) NOT NULL default '0',
   `champ` varchar(255) default NULL,
   `ttype` varchar(255) default NULL,
   `ddefault` varchar(255) default NULL,
   PRIMARY KEY (`id`,`vvalues`),
   KEY `plugin_appliances_appliances_id` (`plugin_appliances_appliances_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_appliances_optvalues_items` (
   `id` int(11) NOT NULL auto_increment, 
   `plugin_appliances_optvalues_id` int(11) NOT NULL default '0',
   `items_id` int(11) NOT NULL default '0',
   `itemtype` VARCHAR(100) NOT NULL default '',
   `vvalue` varchar(255) default NULL,
   PRIMARY KEY  (`id`),
   KEY `item` (`itemtype`,`items_id`),
   UNIQUE KEY `unicity` (`plugin_appliances_optvalues_id`,`itemtype`,`items_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

