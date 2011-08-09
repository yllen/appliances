<?php
/*
 * @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: DOMBRE Julien
// Purpose of file:
// ----------------------------------------------------------------------

define("PLUGIN_APPLIANCES_RELATION_LOCATION",1);

function plugin_appliances_postinit() {
   global $CFG_GLPI, $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['plugin_uninstall_after']['appliances'] = array();
   $PLUGIN_HOOKS['item_purge']['appliances'] = array();

   foreach (PluginAppliancesAppliance::getTypes(true) as $type) {
      $PLUGIN_HOOKS['plugin_uninstall_after']['appliances'][$type]
         = array('PluginAppliancesAppliance_Item','cleanForItem');

      $PLUGIN_HOOKS['item_purge']['appliances'][$type]
         = array('PluginAppliancesAppliance_Item','cleanForItem');
   }
}

function plugin_appliances_registerMethods() {
   global $WEBSERVICES_METHOD;

   // Not authenticated method
   $WEBSERVICES_METHOD['appliances.testAppliances']
                                    = array('PluginAppliancesAppliance','methodTestAppliance');
   // Authenticated method
   $WEBSERVICES_METHOD['appliances.listAppliances']
                                    = array('PluginAppliancesAppliance','methodListAppliances');
   $WEBSERVICES_METHOD['appliances.addAppliance']
                                    = array('PluginAppliancesAppliance','methodAddAppliance');
   $WEBSERVICES_METHOD['appliances.deleteAppliance']
                                    = array('PluginAppliancesAppliance','methodDeleteAppliance');
   $WEBSERVICES_METHOD['appliances.updateAppliance']
                                    = array('PluginAppliancesAppliance','methodUpdateAppliance');
   $WEBSERVICES_METHOD['appliances.getAppliance']
                                    = array('PluginAppliancesAppliance','methodGetAppliance');
}


function plugin_appliances_AssignToTicket($types) {
   global $LANG;

   if (plugin_appliances_haveRight("open_ticket","1")) {
      $types['PluginAppliancesAppliance'] = $LANG['plugin_appliances']['title'][1];
   }
   return $types;
}


function plugin_appliances_install() {
   global $DB;

   if (TableExists("glpi_plugin_applicatifs_profiles")) {
      if (FieldExists("glpi_plugin_applicatifs_profiles","create_applicatifs")) { // version <1.3
         $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.3.sql");
      }
   }

   if (TableExists("glpi_plugin_applicatifs")) {
      if (!FieldExists("glpi_plugin_applicatifs","recursive")) { // version 1.3
         $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.4.sql");
      }
      if (!FieldExists("glpi_plugin_applicatifs","FK_groups")) { // version 1.4
         $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.5.0.sql");
      }
      if (!FieldExists("glpi_plugin_applicatifs","helpdesk_visible")) { // version 1.5.0
         $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.5.1.sql");
      }
      if (FieldExists("glpi_plugin_applicatifs","state")) { // empty 1.5.0 not in update 1.5.0
         $DB->query("ALTER TABLE `glpi_plugin_applicatifs` DROP `state`");
      }
      if (isIndex("glpi_plugin_applicatifs_optvalues_machines", "optvalue_ID")) { // in empty 1.5.0 not in update 1.5.0
         $DB->query("ALTER TABLE `glpi_plugin_applicatifs_optvalues_machines`
                     DROP KEY `optvalue_ID`");
      }
      $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.6.0.sql");

      Plugin::migrateItemType(array(1200 => 'PluginAppliancesAppliance'),
                              array("glpi_bookmarks", "glpi_bookmarks_users",
                                    "glpi_displaypreferences", "glpi_documents_items",
                                    "glpi_infocoms", "glpi_logs", "glpi_tickets"),
                              array("glpi_plugin_appliances_appliances_items",
                                    "glpi_plugin_appliances_optvalues_items"));

      Plugin::migrateItemType(array(4450 => "PluginRacksRack"),
                              array("glpi_plugin_appliances_appliances_items"));
   }

   if (!TableExists("glpi_plugin_appliances_appliances")) { // not installed
      $DB->runFile(GLPI_ROOT . '/plugins/appliances/sql/empty-1.6.1.sql');

   } else if (FieldExists('glpi_plugin_appliances_appliances', 'notes')) {
      $DB->runFile(GLPI_ROOT . '/plugins/appliances/sql/update-1.6.1.sql');
   }
   // required cause autoload don't work for unactive plugin'
   include_once(GLPI_ROOT."/plugins/appliances/inc/profile.class.php");

   PluginAppliancesProfile::createAdminAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}


function plugin_appliances_uninstall() {
   global $DB;

   $tables = array('glpi_plugin_appliances_appliances',
                   'glpi_plugin_appliances_appliances_items',
                   'glpi_plugin_appliances_appliancetypes',
                   'glpi_plugin_appliances_environments',
                   'glpi_plugin_appliances_profiles',
                   'glpi_plugin_appliances_relations',
                   'glpi_plugin_appliances_optvalues',
                   'glpi_plugin_appliances_optvalues_items');

   foreach($tables as $table) {
      $DB->query("DROP TABLE `$table`");
   }

   $query = "DELETE
             FROM `glpi_displaypreferences`
             WHERE (`itemtype` IN ('PluginAppliancesAppliance','PluginAppliancesApplianceType',
                                     'PluginAppliancesEnvironment', 1200))";
   $DB->query($query);

   $query = "DELETE
             FROM `glpi_documents_items`
             WHERE `itemtype` = 'PluginAppliancesAppliance'";
   $DB->query($query);

   $query = "DELETE
             FROM `glpi_bookmarks`
             WHERE (`itemtype` = 'PluginAppliancesAppliance'
                    AND `itemtype` = 'PluginAppliancesApplianceType'
                    AND `itemtype` = 'PluginAppliancesEnvironment')";
   $DB->query($query);

   $query = "DELETE
             FROM `glpi_logs`
             WHERE `itemtype` = 'PluginAppliancesAppliance'";
   $DB->query($query);

   if (class_exists('PluginDatainjectionModel')) {
      $temp = new PluginDatainjectionModel();
      $temp->deleteByCriteria(array('itemtype'=>'PluginAppliancesAppliance'));
   }

   return true;
}


/**
 * Define Dropdown tables to be manage in GLPI :
**/
function plugin_appliances_getDropdown(){
   global $LANG;

   return array('PluginAppliancesApplianceType'  => $LANG['plugin_appliances']['setup'][2],
                'PluginAppliancesEnvironment'    => $LANG['plugin_appliances'][3]);
}


/**
 * Define dropdown relations
**/
function plugin_appliances_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("appliances")) {
      return array('glpi_plugin_appliances_appliancetypes'
                                     => array('glpi_plugin_appliances_appliances'
                                              => 'plugin_appliances_appliancetypes_id'),
                   'glpi_plugin_appliances_environments'
                                     => array('glpi_plugin_appliances_appliances'
                                              => 'plugin_appliances_environments_id'),
                   'glpi_entities'   => array('glpi_plugin_appliances_appliances'     => 'entities_id',
                                              'glpi_plugin_appliances_appliancetypes' => 'entities_id'),
                   'glpi_plugin_appliances_appliances'
                                     => array('glpi_plugin_appliances_appliances_items'
                                              => 'plugin_appliances_appliances_id'),
                   '_virtual_device' => array('glpi_plugin_appliances_appliances_items'
                                              => array('items_id', 'itemtype')));
   }
   return array();
}


////// SEARCH FUNCTIONS ///////(){

/**
 * Define search option for types of the plugins
**/
function plugin_appliances_getAddSearchOptions($itemtype) {
   global $LANG;

   $sopt = array();
   if (plugin_appliances_haveRight("appliance","r")) {
      if (in_array($itemtype, PluginAppliancesAppliance::getTypes(true))) {
         $sopt[1210]['table']          = 'glpi_plugin_appliances_appliances';
         $sopt[1210]['field']          = 'name';
         $sopt[1210]['massiveaction']  = false;
         $sopt[1210]['name']           = $LANG['plugin_appliances']['title'][1]." - ".
                                         $LANG['common'][16];
         $sopt[1210]['forcegroupby']   = true;
         $sopt[1210]['datatype']       = 'itemlink';
         $sopt[1210]['itemlink_type']  = 'PluginAppliancesAppliance';

         $sopt[1211]['table']         = 'glpi_plugin_appliances_appliancetypes';
         $sopt[1211]['field']         = 'name';
         $sopt[1211]['massiveaction'] = false;
         $sopt[1211]['name']          = $LANG['plugin_appliances']['title'][1]." - ".
                                        $LANG['common'][17];
         $sopt[1211]['forcegroupby']  =  true;
      }
   }
   return $sopt;
}


function plugin_appliances_addLeftJoin($type, $ref_table, $new_table, $linkfield,
                                       &$already_link_tables) {

   switch ($new_table) {
      case "glpi_plugin_appliances_appliances_items" :
         return " LEFT JOIN `$new_table`
                     ON (`$ref_table`.`id` = `$new_table`.`plugin_appliances_appliances_id`) ";

      case "glpi_plugin_appliances_appliances" : // From items
         return " LEFT JOIN `glpi_plugin_appliances_appliances_items`
                     ON (`$ref_table`.`id` = `glpi_plugin_appliances_appliances_items`.`items_id`
                         AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$type')
                  LEFT JOIN `glpi_plugin_appliances_appliances`
                     ON (`glpi_plugin_appliances_appliances`.`id`
                         = `glpi_plugin_appliances_appliances_items`.`plugin_appliances_appliances_id`) ";

      case "glpi_plugin_appliances_appliancetypes" : // From items
         $out = Search::addLeftJoin($type,$ref_table,$already_link_tables,
                            "glpi_plugin_appliances_appliances",$linkfield);
         $out .= " LEFT JOIN `glpi_plugin_appliances_appliancetypes`
                     ON (`glpi_plugin_appliances_appliancetypes`.`id`
                         = `glpi_plugin_appliances_appliances`.`plugin_appliances_appliancetypes_id`) ";
         return $out;
   }
   return "";
}


function plugin_appliances_forceGroupBy($type) {

   switch ($type) {
      case 'PluginAppliancesAppliance' :
         return true;
   }
   return false;
}


function plugin_appliances_giveItem($type, $ID, $data, $num) {
   global $DB, $CFG_GLPI, $LANG;

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_appliances_appliances_items.items_id" :
         $appliances_id=$data['id'];
         $query_device = "SELECT DISTINCT `itemtype`
                          FROM `glpi_plugin_appliances_appliances_items`
                          WHERE `plugin_appliances_appliances_id` = '$appliances_id'
                          ORDER BY `itemtype`";
         $result_device = $DB->query($query_device);
         $number_device = $DB->numrows($result_device);
         $out = '';
         if ($number_device > 0) {
            for ($y=0 ; $y < $number_device ; $y++) {
               $column = "name";
               if ($type == 'Ticket') {
                  $column = "id";
               }
               $type = $DB->result($result_device, $y, "itemtype");
               if (!class_exists($type)) {
                     continue;
               }
               $item = new $type();
               $table = $item->getTable();
               if (!empty($table)) {
                  $query = "SELECT `$table`.`id`
                            FROM `glpi_plugin_appliances_appliances_items`, `$table`
                            LEFT JOIN `glpi_entities`
                              ON (`glpi_entities`.`id` = `$table`.`entities_id`)
                            WHERE `$table`.`id` = `glpi_plugin_appliances_appliances_items`.`items_id`
                                 AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$type'
                                 AND `glpi_plugin_appliances_appliances_items`.`plugin_appliances_appliances_id`
                                      = '$appliances_id'".
                                 getEntitiesRestrictRequest(" AND ", $table, '', '',
                                                            $item->maybeRecursive());

                  if ($item->maybeTemplate()) {
                     $query .= " AND `".$table."`.`is_template` = '0'";
                  }
                  $query .= " ORDER BY `glpi_entities`.`completename`,
                             `$table`.`$column`";

                  if ($result_linked = $DB->query($query)) {
                     if ($DB->numrows($result_linked)) {
                        while ($data=$DB->fetch_assoc($result_linked)) {
                           if ($item->getFromDB($data['id'])) {
                              $out .= $item->getTypeName()." - ".$item->getLink()."<br>";
                           }
                        }
                     }
                  }
               }
            }
         }
         return $out;
   }
   return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_appliances_MassiveActions($type) {
   global $LANG;

   switch ($type) {
      case 'PluginAppliancesAppliance' :
         return array('plugin_appliances_install'    => $LANG['plugin_appliances']['setup'][9],
                      'plugin_appliances_desinstall' => $LANG['plugin_appliances']['setup'][10],
                      'plugin_appliances_transfert'  => $LANG['buttons'][48]);

      default :
         if (in_array($type, PluginAppliancesAppliance::getTypes(true))) {
            return array("plugin_appliances_add_item" => $LANG['plugin_appliances']['setup'][13]);
         }
   }
   return array();
}


function plugin_appliances_MassiveActionsDisplay($options) {
   global $LANG;

   switch ($options['itemtype']) {
      case 'PluginAppliancesAppliance' :
         switch ($options['action']) {
            // No case for add_document : use GLPI core one
            case "plugin_appliances_install" :
               Dropdown::showAllItems("item_item",0,0,-1,PluginAppliancesAppliance::getTypes());
               echo "<input type='submit' name='massiveaction' class='submit' ".
                     "value='".$LANG['buttons'][2]."'>";
               break;

            case "plugin_appliances_desinstall" :
               Dropdown::showAllItems("item_item",0,0,-1,PluginAppliancesAppliance::getTypes());
               echo "<input type='submit' name='massiveaction' class='submit' ".
                     "value='".$LANG['buttons'][2]."'>";
               break;

            case "plugin_appliances_transfert" :
               Dropdown::show('Entity');
               echo "&nbsp;<input type='submit' name='massiveaction' class='submit' ".
                     "value='".$LANG['buttons'][2]."'>";
               break;
         }
         break;

      default :
         if (in_array($options['itemtype'], PluginAppliancesAppliance::getTypes(true))) {
            PluginAppliancesAppliance::dropdown();
            echo "<input type='submit' name='massiveaction' class='submit\' ".
                  "value='".$LANG['buttons'][2]."'>";
         }
   }
   return "";
}


function plugin_appliances_MassiveActionsProcess($data) {
   global $LANG,$DB;

   switch ($data['action']) {
      case "plugin_appliances_add_item" :
         if (in_array($data['itemtype'],PluginAppliancesAppliance::getTypes())) {
            $PluginItem = new PluginAppliancesAppliance_Item();
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $input = array('plugin_appliances_appliances_id'
                                             => $data['plugin_appliances_appliances_id'],
                                 'items_id'  => $key,
                                 'itemtype'  => $data['itemtype']);
                  if ($PluginItem->can(-1,'w',$input)) {
                     $PluginItem->add($input);
                  }
               }
            }
         }
         break;

      case "plugin_appliances_install" :
         if (in_array($data['itemtype'],PluginAppliancesAppliance::getTypes())) {
            $PluginItem = new PluginAppliancesAppliance_Item();
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $input = array('plugin_appliances_appliances_id' => $key,
                                 'items_id'                        => $data["item_item"],
                                 'itemtype'                        => $data['itemtype']);
                  if ($PluginItem->can(-1,'w',$input)) {
                     $newid = $PluginItem->add($input);
                  }
               }
            }
         }
         break;

      case "plugin_appliances_desinstall" :
         if (in_array($data['itemtype'],PluginAppliancesAppliance::getTypes())) {
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $query = "DELETE
                            FROM `glpi_plugin_appliances_appliances_items`
                            WHERE `itemtype` = '".$data['itemtype']."'
                                  AND `items_id` = '".$data['item_item']."'
                                  AND `plugin_appliances_appliances_id` = '$key'";
                  $DB->query($query);
               }
            }
         }
         break;

      case "plugin_appliances_transfert" :
         if ($data['itemtype'] == 'PluginAppliancesAppliance') {
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $appliance = new PluginAppliancesAppliance;
                  $appliance->getFromDB($key);

                  $type = PluginAppliancesApplianceType::transfer($appliance->fields["plugin_appliances_appliancetypes_id"],
                                                                  $data['entities_id']);
                  $values["id"]                                  = $key;
                  $values["plugin_appliances_appliancetypes_id"] = $type;
                  $values["entities_id"]                         = $data['entities_id'];
                  $appliance->update($values);
               }
            }
         }
         break;
   }
}


//////////////////////////////


/**
 * Define headings added by the plugin
**/
function plugin_get_headings_appliances($item,$withtemplate) {
   global $LANG;

   $type = get_Class($item);
   if (in_array($type, PluginAppliancesAppliance::getTypes(true))) {
      if (!$withtemplate) {
         // Non template case
         return array(1 => $LANG['plugin_appliances']['title'][1]);
      }
   }
   return false;
}


/**
 * Define headings actions added by the plugin
**/
function plugin_headings_actions_appliances($item) {

   $type = get_Class($item);
   if (in_array($type,PluginAppliancesAppliance::getTypes(true))) {
      return array(1 => array('PluginAppliancesAppliance', 'showAssociated'));
   }
   return false;
}


/**
 * Define PDF informations added by the plugin
**/
function plugin_headings_actionpdf_appliances($item) {
   if (in_array(get_class($item),PluginAppliancesAppliance::getTypes(true))) {
      return array(1 => array('PluginAppliancesAppliance', 'showAssociated_PDF'));
   }
   return false;
}


/**
 * Hook : options for one type
 *
 * @param $item
 *
 * @return array of string which describe the options
**/
function plugin_appliances_prefPDF($item) {
   global $LANG;

   $tabs = array();
   switch (get_class($item)) {
      case 'PluginAppliancesAppliance' :
         $item->fields['id'] = 1; // really awfull :(
         $tabs               = $item->defineTabs();
         unset($tabs[2]); // Custom fields
         break;
   }
   return $tabs;
}


/**
 * Hook to generate a PDF for an Appliance
 *
 * @param $options array of PDF options
 * - item object
 * - tab_id array of ID
 * - tab array of options to be printed
 * - page boolean true for landscape
**/
function plugin_appliances_generatePDF($options) {

   $item   = $options['item'];
   $tab_id = $options['tab_id'];
   $tab    = $options['tab'];
   $page   = $options['page'];

   $pdf = new PluginPdfSimplePDF('a4', ($page ? 'landscape' : 'portrait'));
   $nb_id = count($tab_id);

   foreach ($tab_id as $key => $ID) {
      if (plugin_pdf_add_header($pdf,$ID,$item)) {
         $pdf->newPage();
      } else {
         // Object not found or no right to read
         continue;
      }

      switch (get_class($item)) {
         case 'PluginAppliancesAppliance' :
            $item->show_PDF($pdf);

            foreach($tab as $i) {
               switch($i) { // See plugin_appliance::defineTabs();
                  case 1 :
                     $item->showItem_PDF($pdf);
                     break;

                  case 6 :
                     plugin_pdf_ticket($pdf,$item);
                     break;

                  case 9 :
                     plugin_pdf_financial($pdf,$item);
                     plugin_pdf_contract ($pdf,$item);
                     break;

                  case 10 :
                     plugin_pdf_document($pdf,$item);
                     break;

                  case 11 :
                     plugin_pdf_note($pdf,$item);
                     break;

                  case 12 :
                     plugin_pdf_history($pdf,$item);
                     break;

                  default :
                     plugin_pdf_pluginhook($i,$pdf,$item);
               }
            }
            break;
      } // Switch type
   } // Each ID
   $pdf->render();
}


function plugin_datainjection_populate_appliances() {
   global $INJECTABLE_TYPES;

   $INJECTABLE_TYPES['PluginAppliancesApplianceInjection'] = 'appliances';
}
?>