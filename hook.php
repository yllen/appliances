<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Appliances plugin for GLPI.

 Appliances is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Appliances is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Appliances. If not, see <http://www.gnu.org/licenses/>.

 @package   appliances
 @author    Xavier CAILLAUD, Remi Collet, Nelly Mahu-Lasson
 @copyright Copyright (c) 2009-2020 Appliances plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/appliances
 @since     version 2.0
 --------------------------------------------------------------------------
 */

define("PLUGIN_APPLIANCES_RELATION_LOCATION", 1);

function plugin_appliances_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['plugin_uninstall_after']['appliances'] = [];

   foreach (PluginAppliancesAppliance::getTypes(true) as $type) {
      $PLUGIN_HOOKS['plugin_uninstall_after']['appliances'][$type]
                                    = ['PluginAppliancesAppliance','cleanForItem'];
   }
}


function plugin_appliances_registerMethods() {
   global $WEBSERVICES_METHOD;

   // Not authenticated method
   $WEBSERVICES_METHOD['appliances.testAppliances']  = ['PluginAppliancesAppliance',
                                                        'methodTestAppliance'];
   // Authenticated method
   $WEBSERVICES_METHOD['appliances.listAppliances']  = ['PluginAppliancesAppliance',
                                                        'methodListAppliances'];

   $WEBSERVICES_METHOD['appliances.addAppliance']    = ['PluginAppliancesAppliance',
                                                        'methodAddAppliance'];

   $WEBSERVICES_METHOD['appliances.deleteAppliance'] = ['PluginAppliancesAppliance',
                                                        'methodDeleteAppliance'];

   $WEBSERVICES_METHOD['appliances.updateAppliance'] = ['PluginAppliancesAppliance',
                                                        'methodUpdateAppliance'];

   $WEBSERVICES_METHOD['appliances.getAppliance']    = ['PluginAppliancesAppliance',
                                                        'methodGetAppliance'];
}


/**
 * @return bool
**/
function plugin_appliances_install() {
   global $DB;

   $migration = new Migration(310);

   if ($DB->tableExists("glpi_plugin_applicatifs_profiles")) {
      if ($DB->fieldExists("glpi_plugin_applicatifs_profiles","create_applicatifs")) { // version <1.3
         $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.3.sql");
      }
   }

   if ($DB->tableExists("glpi_plugin_applicatifs")) {
      if (!$DB->fieldExists("glpi_plugin_applicatifs","recursive")) { // version 1.3
         $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.4.sql");
      }
      if (!$DB->fieldExists("glpi_plugin_applicatifs","FK_groups")) { // version 1.4
         $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.5.0.sql");
      }
      if (!$DB->fieldExists("glpi_plugin_applicatifs","helpdesk_visible")) { // version 1.5.0
         $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.5.1.sql");
      }

      // empty 1.5.0 not in update 1.5.0
      $migration->dropField('glpi_plugin_applicatifs', 'state');
      $migration->dropKey('glpi_plugin_applicatifs_optvalues_machines', 'optvalue_ID');

      $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.6.0.sql");

      Plugin::migrateItemType([1200 => 'PluginAppliancesAppliance'],
                              ["glpi_savedsearches", "glpi_savedsearches_users",
                               "glpi_displaypreferences", "glpi_documents_items", "glpi_infocoms",
                               "glpi_logs", "glpi_items_tickets"],
                              ["glpi_plugin_appliances_appliances_items",
                               "glpi_plugin_appliances_optvalues_items"]);

      Plugin::migrateItemType([4450 => "PluginRacksRack"],
                              ["glpi_plugin_appliances_appliances_items"]);
   }

   if ($DB->tableExists("glpi_plugin_appliances_appliances")) {
      include_once(GLPI_ROOT."/plugins/appliances/inc/appliance.class.php");
      PluginAppliancesAppliance::updateSchema($migration);
   } else { // not installed
      // only install for specifics fields
      $DB->runFile(GLPI_ROOT . '/plugins/appliances/sql/empty-3.0.0.sql');
   }


   // Migration to core
   $migration->displayWarning(__("You are about to launch migration of Appliances plugin data into GLPI core tables",
                              'appliances'));

   if ($DB->tableExists('glpi_plugin_appliances_appliances')
       && !$DB->fieldExists('glpi_plugin_appliances_appliances', 'is_helpdesk_visible')
       && (countElementsInTable('glpi_plugin_appliances_appliances', ['is_helpdesk_visible' => 1]) > 0)) {
      $migration->displayWarning(__("You can't migrate to core because one of your used fields is missing in core. Wait version 9.5.2",
                                 'appliances'));
      exit;
   }

   if ($DB->tableExists("glpi_appliances")
       && $DB->tableExists('glpi_plugin_appliances_appliances')) {
      // migration rights
      foreach ($DB->request(['FROM'   => 'glpi_profilerights',
                             'WHERE'  => ['name' => 'plugin_appliances']]) AS $right) {
         $queryright = "UPDATE `glpi_profilerights`
                        SET `rights` = '".$right['rights']."'
                        WHERE `profiles_id` = '".$right['profiles_id']."'";
         $DB->query($queryright);
      }
      $delqueryright = "DELETE FROM `glpi_profilerights`
                        WHERE `name` LIKE 'plugin_appliances%' ";
      $DB->query($delqueryright);

      // migration pref
      foreach ($DB->request(['FROM'   => 'glpi_displaypreferences',
                             'WHERE'  => ['itemtype' => 'PluginAppliancesAppliance',
                                          'users_id' => ['>', 0]]]) AS $pref) {
         $querypref = "INSERT INTO `glpi_displaypreferences`
                              (`num`, `rank`, `users_id`)
                       VALUES ('".$pref['num']."', '".$pref['rank']."', '".$pref['users_id']."')";
         $DB->query($querypref);
      }
      // default value
      $querydelpref = "DELETE FROM `glpi_displaypreferences`
                       WHERE `itemtype` = 'PluginAppliancesAppliance'";
      $DB->query($querydelpref);

      // migration ref in core
      $tables = ['glpi_contracts_items', 'glpi_items_tickets', 'glpi_savedsearches',
                 'glpi_items_problems', 'glpi_documents_items', 'glpi_logs', 'glpi_infocoms',
                 'glpi_notepads'];
      foreach ($tables as $table) {
         if (countElementsInTable($table, ['itemtype' => 'appliance']) == 0) {
            $query = "UPDATE `".$table."`
                      SET `itemtype` = 'Appliance'
                      WHERE `itemtype` = 'PluginAppliancesAppliance' ";
            $DB->query($query);
         } else {
            $migration->displayWarning("table ". $table." can't be migrate because appliance type already exist for the same item");
         }
      }

      // migration appliances
      if (countElementsInTable('glpi_appliances') > 0) {
         $migration->displayWarning("you can't migrate because glpi_appliances is not empty");
      } else {
         if ($result = $DB->request(['FROM' => 'glpi_plugin_appliances_appliances'])) {
            if (count($result) > 0) {
               foreach ($result as $id => $data) {
                  $fieldsname = "`id`, `entities_id`, `is_recursive`,  `name`, `is_deleted`,
                                  `appliancetypes_id`, `comment`, `locations_id`,
                                  `applianceenvironments_id`, `users_id`, `users_id_tech`,
                                  `groups_id`, `groups_id_tech`, `date_mod`, `states_id`,
                                  `serial`, `otherserial`";
                  $fieldsval  = "'".$data['id']."', '".$data['entities_id']."',
                                 '".$data['is_recursive']."', '".addslashes($data['name'])."',
                                 '".$data['is_deleted']."',
                                 '".$data['plugin_appliances_appliancetypes_id']."',
                                 '".addslashes($data['comment'])."', '".$data['locations_id']."',
                                 '".$data['plugin_appliances_environments_id']."',
                                 '".$data['users_id']."', '".$data['users_id_tech']."',
                                 '".$data['groups_id']."', '".$data['groups_id_tech']."',
                                 '".$data['date_mod']."', '".$data['states_id']."',
                                 '".$data['serial']."','".$data['otherserial']."'";
                  if ($DB->fieldExists('glpi_plugin_appliances_appliances', 'is_helpdesk_visible')) {
                     $fieldsname .= ", `is_helpdesk_visible`";
                     $fieldsval  .= ",'".$data['is_helpdesk_visible']."'";
                  }
                  $queryap = "INSERT INTO `glpi_appliances`
                                     (".$fieldsname.")
                              VALUES (".$fieldsval.")";

                  $DB->queryOrDie($queryap, "migration appliances to core");

                  if (countElementsInTable('glpi_plugin_appliances_appliances',
                                           ['NOT' => ['externalid' => null]]) > 0) {
                     $query = "UPDATE `glpi_appliances`
                               SET `externalidentifier` = '".$data['externalid']."'
                               WHERE `id` = '".$data['id']."'";
                     $DB->query($query);
                  }
               }
            }
         }
         $migration->renameTable('glpi_plugin_appliances_appliances',
                                 'backup_glpi_plugin_appliances_appliances');
      }

      // migration appliances_items
      if (countElementsInTable('glpi_appliances_items') > 0) {
         $migration->displayWarning("you can't migrate because glpi_appliances_items is not empty");
      } else {
         if ($DB->tableExists('glpi_plugin_appliances_appliances_items')
              && (countElementsInTable('glpi_plugin_appliances_appliances_items') > 0)) {

            if ($result = $DB->request(['FROM' => 'glpi_plugin_appliances_appliances_items'])) {
               if (count($result)) {
                  foreach ($result as $id => $data) {
                     $queryai = "INSERT INTO `glpi_appliances_items`
                                        (`id`, `appliances_id`, `items_id`,  `itemtype`)
                                 VALUES ('".$data['id']."', '".$data['plugin_appliances_appliances_id']."',
                                         '".$data['items_id']."', '".$data['itemtype']."')";
                     $DB->queryOrDie($queryai, "migration appliances_items to core");
                  }
               }
            }
         }
         $migration->renameTable('glpi_plugin_appliances_appliances_items',
                                 'backup_glpi_plugin_appliances_appliances_items');
      }

      // migration types
      if (countElementsInTable('glpi_appliancetypes') > 0) {
         $migration->displayWarning("you can't migrate because glpi_appliancetypes is not empty");
      } else {
         if ($DB->tableExists('glpi_plugin_appliances_appliancetypes')) {
            if ($result = $DB->request(['FROM' => 'glpi_plugin_appliances_appliancetypes'])) {
               if (count($result)) {
                  foreach ($result as $id => $data) {
                     $queryai = "INSERT INTO `glpi_appliancetypes`
                                        (`id`, `entities_id`, `is_recursive`, `name`, `comment`)
                                 VALUES ('".$data['id']."', '".$data['entities_id']."',
                                         '".$data['is_recursive']."', '".addslashes($data['name'])."',
                                         '".addslashes($data['comment'])."')";
                     $DB->queryOrDie($queryai, "migration appliancetypes to core");

                     if (countElementsInTable('glpi_plugin_appliances_appliancetypes',
                                              ['NOT' => ['externalid' => null]]) > 0) {
                        $query = "UPDATE `glpi_appliancetypes`
                                  SET `externalidentifier` = '".$data['externalid']."'
                                  WHERE `id` = '".$data['id']."'";
                        $DB->query($query);
                     }
                  }
               }
            }
         }
         $migration->renameTable('glpi_plugin_appliances_appliancetypes',
                                 'backup_glpi_plugin_appliances_appliancetypes');
      }

      // migration relations
      if ($DB->tableExists('glpi_appliancerelations')) { // glpi 9.5.0 + 9.5.1
         $table = 'glpi_appliancerelations';
      } else if ($DB->tableExists('glpi_appliances_items_relations')) { // glpi 9.5.2
         $table = 'glpi_appliances_items_relations';
      }
      if (countElementsInTable($table) > 0) {
         $migration->displayWarning("you can't migrate because ".$table." is not empty");
      } else {
         if ($DB->tableExists('glpi_plugin_appliances_relations')
            && (countElementsInTable('glpi_plugin_appliances_relations') > 0)) {

            if ($result = $DB->request(['FROM' => 'glpi_plugin_appliances_relations'])) {
               if (count($result)) {
                  foreach ($result as $id => $data) {
                     $queryrel = "INSERT INTO `".$table."`
                                         (`id`, `appliances_items_id`, `relations_id`)
                                  VALUES ('".$data['id']."',
                                          '".$data['plugin_appliances_appliances_items_id']."',
                                          '".$data['relations_id']."')";
                     $DB->queryOrDie($queryrel, "migration appliances relations to core");
                  }
               }
            }
         }
         $migration->renameTable('glpi_plugin_appliances_relations',
                                 'backup_glpi_plugin_appliances_relations');
      }

      // migration environments
      if (countElementsInTable('glpi_applianceenvironments') > 0) {
         $migration->displayWarning("you can't migrate because glpi_appliances_environments is not empty");
      } else {
         if ($DB->tableExists('glpi_plugin_appliances_environments')
            && (countElementsInTable('glpi_plugin_appliances_environments') > 0)) {

            if ($result = $DB->request(['FROM' => 'glpi_plugin_appliances_environments'])) {
               if (count($result)) {
                  foreach ($result as $id => $data) {
                     $queryenv = "INSERT INTO `glpi_applianceenvironments`
                                         (`id`, `name`, `comment`)
                                  VALUES ('".$data['id']."', '".addslashes($data['name'])."',
                                          '".addslashes($data['comment'])."')";
                     $DB->queryOrDie($queryenv, "migration appliances environments to core");
                  }
               }
            }
         }
         $migration->renameTable('glpi_plugin_appliances_environments',
                                 'backup_glpi_plugin_appliances_environments');
      }

      // change fields name
      $migration->changeField('glpi_plugin_appliances_optvalues', 'plugin_appliances_appliances_id',
                              'appliances_id', 'integer');
   }

   $migration->executeMigration();

   return true;
}


/**
 * @return bool
**/
function plugin_appliances_uninstall() {
   global $DB;

   $dbu = new DbUtils();

   $migration = new Migration(310);

   $tables = ['glpi_plugin_appliances_optvalues',
              'glpi_plugin_appliances_optvalues_items'];

   foreach($tables as $table) {
      $migration->dropTable($table);
   }

   $itemtypes = ['Document_Item', 'DisplayPreference', 'Savedsearch', 'Log', 'Notepad', 'Item_Ticket',
                 'Contract_Item', 'Item_Problem', 'Infocom'];
   foreach ($itemtypes as $itemtype) {
      $item = new $itemtype;
      $item->deleteByCriteria(['itemtype' => 'PluginAppliancesAppliance']);
   }

   $query = "DELETE
             FROM `glpi_dropdowntranslations`
             WHERE `itemtype` IN ('PluginAppliancesApplianceType', 'PluginAppliancesEnvironment')";
   $DB->query($query);

   if ($temp = $dbu->getItemForItemtype('PluginDatainjectionModel')) {
      $temp->deleteByCriteria(['itemtype'=>'PluginAppliancesAppliance']);
   }

   $migration->executeMigration();

   return true;
}

