RENAME TABLE `glpi_plugin_applicatifs` to `glpi_plugin_appliances_appliances`;

ALTER TABLE `glpi_plugin_appliances_appliances` 
   DROP KEY `FK_entities`,
   DROP KEY `deleted`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `recursive` `is_recursive` tinyint(1) NOT NULL default '0',
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   CHANGE `type` `appliancetypes_id` int(11) NOT NULL default '0',
   CHANGE `comments` `comment` text,
   CHANGE `location` `locations_id` int(11) NOT NULL  default '0',
   CHANGE `environment` `environments_id` int(11) NOT NULL default '0',
   CHANGE `FK_users` `users_id` int(11) NOT NULL  default '0',
   CHANGE `FK_groups` `groups_id` int(11) NOT NULL  default '0',
   CHANGE `relationtype` `relationtypes_id` int(11) NOT NULL default '0',
   CHANGE `helpdesk_visible` `is_helpdesk_visible` tinyint(1) NOT NULL default '1',
   ADD `externalid` varchar(255) NULL,
   ADD KEY `entities_id` (`entities_id`),
   ADD KEY `is_deleted` (`is_deleted`),
   ADD UNIQUE `unicity` (`externalid`);


RENAME TABLE `glpi_plugin_applicatifs_device` to `glpi_plugin_appliances_appliances_items`;

ALTER TABLE `glpi_plugin_appliances_appliances_items`
   DROP KEY `FK_applicatif`,
   DROP KEY `FK_applicatif_2`,
   DROP KEY `FK_device`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_applicatif` `appliances_id` int(11) NOT NULL default '0',
   CHANGE `FK_device` `items_id` int(11) NOT NULL default '0',
   CHANGE `device_type` `itemtype` int(11) NOT NULL default '0',
   ADD UNIQUE `appliances_items_type` (`appliances_id`,`items_id`,`itemtype`),
   ADD KEY `appliances_id` (`appliances_id`),
   ADD KEY `type_items` (`itemtype`,`items_id`);


RENAME TABLE `glpi_dropdown_plugin_applicatifs_type` to `glpi_plugin_appliance_appliancetypes`;

ALTER TABLE `glpi_plugin_appliance_appliancetypes`
   DROP KEY `FK_entities`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `comments` `comment` text,
   ADD `externalid` varchar(255) NULL,
   ADD KEY `entities_id` (`entities_id`),
   ADD UNIQUE (`externalid`);


RENAME TABLE `glpi_dropdown_plugin_applicatifs_environment` to `glpi_plugin_appliances_environments`;

ALTER TABLE `glpi_plugin_appliances_environments`
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `comments` `comment` text;


RENAME TABLE `glpi_plugin_applicatifs_profiles` to `glpi_plugin_appliances_profiles`;

ALTER TABLE `glpi_plugin_appliances_profiles`
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `applicatifs` `appliance` char(1) default NULL;


RENAME TABLE `glpi_dropdown_plugin_applicatifs_relationtype` to `glpi_plugin_appliances_relationtypes`;

ALTER TABLE `glpi_plugin_appliances_relationtypes`
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `comments` `comment` text,
   ADD `is_recursive` tinyint(1) NOT NULL default '0';


RENAME TABLE `glpi_plugin_applicatifs_relation` to `glpi_plugin_appliances_relations`;

ALTER TABLE `glpi_plugin_appliances_relations`
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_applicatifs_device` `appliances_items_id` int(11) NOT NULL default '0',
   CHANGE `FK_relation` `relations_id` int(11) NOT NULL default '0',
   ADD KEY `appliances_items_id` (`appliances_items_id`),
   ADD KEY `relations_id` (`relations_id`);


RENAME TABLE `glpi_plugin_applicatifs_optvalues` to `glpi_plugin_appliances_optvalues`;

ALTER TABLE `glpi_plugin_appliances_optvalues`
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `applicatif_ID` `appliances_id` int(11) NOT NULL default '0',
   CHANGE `champ` `champ` varchar(255),
   CHANGE `ttype` `ttype` varchar(255),
   CHANGE `ddefault` `ddefault` varchar(255),
   ADD KEY `appliances_id` (`appliances_id`);

RENAME TABLE `glpi_plugin_applicatifs_optvalues_machines` to `glpi_plugin_appliances_optvalues_items`;

ALTER TABLE `glpi_plugin_appliances_optvalues_items`
   ADD `itemtype` int(11) NOT NULL default '0',
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `optvalue_ID` `optvalues_id` int(11) NOT NULL default '0',
   CHANGE `machine_ID` `items_id` int(11) NOT NULL default '0',
   CHANGE `vvalue` `vvalue` varchar(255),
   ADD KEY `item` (`itemtype`,`items_id`),
   ADD UNIQUE KEY `unicity` (`optvalues_id`,`itemtype`,`items_id`);
   