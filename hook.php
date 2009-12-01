<?php
/*
 * @version $Id: hook.php 7355 2008-10-03 15:31:00Z moyo $
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copynetwork (C) 2003-2006 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org/
 ----------------------------------------------------------------------

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
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: DOMBRE Julien
// Purpose of file:
// ----------------------------------------------------------------------

define("PLUGIN_APPLIANCES_RELATION_LOCATION",1);
foreach (glob(GLPI_ROOT . '/plugins/appliances/inc/*.php') as $file) {
   include_once ($file);
}

function plugin_appliances_AssignToTicket($types) {
   global $LANG;

   if (plugin_appliances_haveRight("open_ticket","1")) {
      $types[PLUGIN_APPLIANCES_TYPE] = $LANG['plugin_appliances']['title'][1];
   }
   return $types;
}


function plugin_appliances_install() {
   global $DB;

   include_once (GLPI_ROOT."/inc/profile.class.php");

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
         $DB->query("DROP `state`");
      }
      if (isIndex("glpi_plugin_applicatifs_optvalues_machines", "optvalue_ID")) { // in empty 1.5.0 not in update 1.5.0
         $DB->query("DROP KEY `optvalue_ID`");
      }
      $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/update-1.6.0.sql");
   }
   if (!TableExists("glpi_plugin_appliances_appliances")) { // not installed
      $DB->runFile(GLPI_ROOT ."/plugins/appliances/sql/empty-1.6.0.sql");
   }

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
             WHERE (`itemtype` = '".PLUGIN_APPLIANCES_TYPE."'
                    OR `itemtype` = '".PLUGIN_APPLIANCES_APPLIANCESTYPE."'
                    OR `itemtype` = '".PLUGIN_APPLIANCES_ENVIRONMENT."')";
   $DB->query($query);

   $query = "DELETE
             FROM `glpi_documents_items`
             WHERE `itemtype` = '".PLUGIN_APPLIANCES_TYPE."'";
   $DB->query($query);

   $query = "DELETE
             FROM `glpi_bookmarks`
             WHERE (`itemtype` = '".PLUGIN_APPLIANCES_TYPE."'
                    AND `itemtype` = '".PLUGIN_APPLIANCES_APPLIANCESTYPE."'
                    AND `itemtype` = '".PLUGIN_APPLIANCES_ENVIRONMENT."')";
   $DB->query($query);

   $query = "DELETE
             FROM `glpi_logs`
             WHERE `itemtype` = '".PLUGIN_APPLIANCES_TYPE."'";
   $DB->query($query);

   if (TableExists("glpi_plugin_data_injection_models")) {
      $DB->query("DELETE
                  FROM `glpi_plugin_data_injection_models`,
                       `glpi_plugin_data_injection_mappings`,
                       `glpi_plugin_data_injection_infos`
                  USING `glpi_plugin_data_injection_models`,
                        `glpi_plugin_data_injection_mappings`,
                        `glpi_plugin_data_injection_infos`
                  WHERE `glpi_plugin_data_injection_models`.`device_type` = ".PLUGIN_APPLIANCES_TYPE."
                        AND `glpi_plugin_data_injection_mappings`.`model_id`
                              = `glpi_plugin_data_injection_models`.`ID`
                        AND `glpi_plugin_data_injection_infos`.`model_id`
                              = `glpi_plugin_data_injection_models`.`ID`");
   }
   return true;
}


// Define Dropdown tables to be manage in GLPI :
function plugin_appliances_getDropdown(){
   global $LANG;

   return array(PLUGIN_APPLIANCES_APPLIANCESTYPE =>$LANG['plugin_appliances']['setup'][2],
                PLUGIN_APPLIANCES_ENVIRONMENT    =>$LANG['plugin_appliances'][3]);
}


// Define dropdown relations
function plugin_appliances_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("appliances")) {
      return array('glpi_plugin_appliances_appliancetypes'
                     => array('glpi_plugin_appliances_appliances' => 'appliancetypes_id'),
                   'glpi_plugin_appliances_environments'
                     => array('glpi_plugin_appliances_appliances' => 'environments_id'),
                   'glpi_entities'
                     => array('glpi_plugin_appliances_appliances'      => 'entities_id',
                              'glpi_plugin_appliances_appliancetypes' => 'entities_id'),
                   'glpi_plugin_appliances_appliances'
                     => array('glpi_plugin_appliances_appliances_items' => 'appliances_id'),
                   '_virtual_device'
                     => array('glpi_plugin_appliances_appliances_items' => array('items_id',
                                                                      'itemtype')));
   }
   return array();
}


////// SEARCH FUNCTIONS ///////(){

// Define search option for types of the plugins
function plugin_appliances_getAddSearchOptions($itemtype) {
   global $LANG;

   $sopt = array();
   if (plugin_appliances_haveRight("appliance","r")) {
      if (in_array($itemtype, PluginAppliancesAppliance::getTypes())) {
         $sopt[1210]['table']         = 'glpi_plugin_appliances_appliances';
         $sopt[1210]['field']         = 'name';
         $sopt[1210]['linkfield']     = '';
         $sopt[1210]['name']          = $LANG['plugin_appliances']['title'][1]." - ".
                                        $LANG['common'][16];
         $sopt[1210]['forcegroupby']  = true;
         $sopt[1210]['datatype']      = 'itemlink';
         $sopt[1210]['itemlink_type'] = PLUGIN_APPLIANCES_TYPE;

         $sopt[1211]['table']        = 'glpi_plugin_appliances_appliancetypes';
         $sopt[1211]['field']        = 'name';
         $sopt[1211]['linkfield']    = '';
         $sopt[1211]['name']         = $LANG['plugin_appliances']['title'][1]." - ".
                                       $LANG['common'][17];
         $sopt[1211]['forcegroupby'] =  true;
      }
   }
   return $sopt;
}


function plugin_appliances_addLeftJoin($type,$ref_table,$new_table,$linkfield,
                                       &$already_link_tables) {

   switch ($new_table) {
      case "glpi_plugin_appliances_appliances_items" :
         return " LEFT JOIN `$new_table` ON (`$ref_table`.`id` = `$new_table`.`appliances_id`) ";

      case "glpi_plugin_appliances_appliances" : // From items
         return " LEFT JOIN `glpi_plugin_appliances_appliances_items`
                     ON (`$ref_table`.`id` = `glpi_plugin_appliances_appliances_items`.`items_id`
                         AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$type')
                  LEFT JOIN `glpi_plugin_appliances_appliances`
                     ON (`glpi_plugin_appliances_appliances`.`id`
                         = `glpi_plugin_appliances_appliances_items`.`appliances_id`) ";

      case "glpi_plugin_appliances_appliancetypes" : // From items
         $out = addLeftJoin($type,$ref_table,$already_link_tables,
                            "glpi_plugin_appliances_appliances",$linkfield);
         $out .= " LEFT JOIN `glpi_plugin_appliances_appliancetypes`
                     ON (`glpi_plugin_appliances_appliancetypes`.`id`
                         = `glpi_plugin_appliances_appliances`.`appliancetypes_id`) ";
         return $out;
   }
   return "";
}


function plugin_appliances_forceGroupBy($type) {

   switch ($type) {
      case PLUGIN_APPLIANCES_TYPE :
         return true;
   }
   return false;
}


function plugin_appliances_giveItem($type,$ID,$data,$num) {
   global $DB, $CFG_GLPI, $INFOFORM_PAGES, $LANG, $LINK_ID_TABLE;

   $searchopt = &getSearchOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_appliances_appliances_items.items_id" :
         $appliances_id=$data['id'];
         $query_device = "SELECT DISTINCT `itemtype`
                          FROM `glpi_plugin_appliances_appliances_items`
                          WHERE `appliances_id` = '$appliances_id'
                          ORDER BY `itemtype`";
         $result_device = $DB->query($query_device);
         $number_device = $DB->numrows($result_device);
         $out = '';
         if ($number_device > 0) {
            $ci = new CommonItem();
            for ($y=0 ; $y < $number_device ; $y++) {
               $column = "name";
               if ($type==TRACKING_TYPE) {
                  $column = "id";
               }
               $type = $DB->result($result_device, $y, "itemtype");

               if (!empty($LINK_ID_TABLE[$type])) {
                  $query = "SELECT `".$LINK_ID_TABLE[$type]."`.`id`
                            FROM `glpi_plugin_appliances_appliances_items`, `".$LINK_ID_TABLE[$type]."`
                            LEFT JOIN `glpi_entities`
                              ON (`glpi_entities`.`id` = `".$LINK_ID_TABLE[$type]."`.`entities_id`)
                            WHERE `".$LINK_ID_TABLE[$type]."`.`id`
                                     = `glpi_plugin_appliances_appliances_items`.`items_id`
                                 AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$type'
                                 AND `glpi_plugin_appliances_appliances_items`.`appliances_id` = '$appliances_id'".
                                 getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',
                                                            isset($CFG_GLPI["recursive_type"][$type]));

                  if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])) {
                     $query .= " AND `".$LINK_ID_TABLE[$type]."`.`is_template` = '0'";
                  }
                  $query .= " ORDER BY `glpi_entities`.`completename`,
                             `".$LINK_ID_TABLE[$type]."`.`$column`";

                  if ($result_linked = $DB->query($query)) {
                     $ci->setType($type,true);
                     if ($DB->numrows($result_linked)) {
                        while ($data=$DB->fetch_assoc($result_linked)) {
                           if ($ci->obj->getFromDB($data['id'])) {
                              $out .= $ci->getType()." - ".$ci->obj->getLink()."<br>";
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
      case PLUGIN_APPLIANCES_TYPE :
         return array('plugin_appliances_install'    => $LANG['plugin_appliances']['setup'][9],
                      'plugin_appliances_desinstall' => $LANG['plugin_appliances']['setup'][10],
                      'plugin_appliances_transfert'  => $LANG['buttons'][48]);

      default :
         if (in_array($type, PluginAppliancesAppliance::getTypes())) {
            return array("plugin_appliances_add_item" => $LANG['plugin_appliances']['setup'][13]);
         }
   }
   return array();
}


function plugin_appliances_MassiveActionsDisplay($type,$action) {
   global $LANG;

   switch ($type) {
      case PLUGIN_APPLIANCES_TYPE :
         switch ($action) {
            // No case for add_document : use GLPI core one
            case "plugin_appliances_install" :
               dropdownAllItems("item_item",0,0,-1,PluginAppliancesAppliance::getTypes());
               echo "<input type='submit' name='massiveaction' class='submit' ".
                     "value='".$LANG['buttons'][2]."'>";
               break;

            case "plugin_appliances_desinstall" :
               dropdownAllItems("item_item",0,0,-1,PluginAppliancesAppliance::getTypes());
               echo "<input type='submit' name='massiveaction' class='submit' ".
                     "value='".$LANG['buttons'][2]."'>";
               break;

            case "plugin_appliances_transfert" :
               dropdownValue("glpi_entities", "entities_id", '');
               echo "&nbsp;<input type='submit' name='massiveaction' class='submit' ".
                     "value='".$LANG['buttons'][2]."'>";
               break;
         }
         break;

      default :
         if (in_array($type, PluginAppliancesAppliance::getTypes())) {
            plugin_appliances_dropdownappliances("conID");
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
         $PluginItem = new PluginAppliancesAppliance_Item();
         foreach ($data["items"] as $key => $val) {
            if ($val == 1) {
               $input = array('appliances_id' => $data['conID'],
                              'items_id'      => $key,
                              'itemtype'      => $data['itemtype']);
               if ($PluginItem->can(-1,'w',$input)) {
                  $PluginItem->add($input);
               }
            }
         }
         break;

      case "plugin_appliances_install" :
         if ($data['itemtype'] == PLUGIN_APPLIANCES_TYPE) {
            $PluginItem = new PluginAppliancesAppliance_Item();
            foreach ($data["items"] as $key => $val) {
               if ($val == 1) {
                  $input = array('appliances_id' => $key,
                                 'items_id'      => $data["item_item"],
                                 'itemtype'      => $data['itemtype']);
                  if ($PluginItem->can(-1,'w',$input)) {
                     $PluginItem->add($input);
                  }
               }
            }
         }
         break;

      case "plugin_appliances_desinstall" :
         if ($data['itemtype'] == PLUGIN_APPLIANCES_TYPE) {
            foreach ($data["items_id"] as $key => $val) {
               if ($val == 1) {
                  $query = "DELETE
                            FROM `glpi_plugin_appliances_appliances_items`
                            WHERE `itemtype` = '".$data['appliancetypes_id']."'
                                  AND `items_id` = '".$data['item_item']."'
                                  AND `appliances_id` = '$key'";
                  $DB->query($query);
               }
            }
         }
         break;

      case "plugin_appliances_transfert" :
         if ($data['itemtype'] == PLUGIN_APPLIANCES_TYPE) {
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $appliance = new PluginAppliancesAppliance;
                  $appliance->getFromDB($key);

                  $type = PluginAppliancesAppliancetype::transfer($appliance->fields["appliancetypes_id"],
                                                                  $data['entities_id']);
                  $values["id"] = $key;
                  $values["appliancetypes_id"] = $type;
                  $values["entities_id"] = $data['entities_id'];
                  $appliance->update($values);
               }
            }
         }
         break;
   }
}


//////////////////////////////

// Hook done on delete item case

function plugin_pre_item_delete_appliances($input) {

   if (isset($input["_item_type_"])) {
      switch ($input["_item_type_"]) {
         case PROFILE_TYPE :
            // Manipulate data if needed
            $PluginAppliancesProfile = new PluginAppliancesProfile;
            $PluginAppliancesProfile->cleanProfiles($input["id"]);
            break;
      }
   }
   return $input;
}


function plugin_item_delete_appliances($parm) {

   switch ($parm['type']) {
      case TRACKING_TYPE :
         $temp = new PluginAppliancesAppliance_Item();
         $temp->clean(array('itemtype' => $parm['type'],
                            'items_id' => $parm['id']));

         $temp = new PluginAppliancesOptvalue_Item();
         $temp->clean(array('itemtype' => $parm['type'],
                            'items_id' => $parm['id']));

         return true;
   }
   return false;
}


// Hook done on purge item case
function plugin_item_purge_appliances($parm) {

   if (in_array($parm['type'], PluginAppliancesAppliance::getTypes())
       && $parm['type'] != TRACKING_TYPE) { // TRACKING_TYPE handle in plugin_item_delete_appliances

      $temp = new PluginAppliancesAppliance_Item();
      $temp->clean(array('itemtype' => $parm['type'],
                         'items_id' => $parm['id']));

      $temp = new PluginAppliancesOptvalue_Item();
      $temp->clean(array('itemtype' => $parm['type'],
                         'items_id' => $parm['id']));
      return true;
   }
   return false;
}


// Define headings added by the plugin
function plugin_get_headings_appliances($type,$ID,$withtemplate) {
   global $LANG;

   if ($type == PROFILE_TYPE) {
      if ($ID > 0) {
         return array(1 => $LANG['plugin_appliances']['title'][1]);
      }
   } else if (in_array($type, PluginAppliancesAppliance::getTypes())) {
      if (!$withtemplate) {
         // Non template case
         return array(1 => $LANG['plugin_appliances']['title'][1]);
      }
   }
   return false;
}


// Define headings actions added by the plugin
function plugin_headings_actions_appliances($type) {

   if (in_array($type,PluginAppliancesAppliance::getTypes())
       || $type == PROFILE_TYPE) {
      return array(1 => "plugin_headings_appliances");
   }
   return false;
}


// applicatifs of an action heading
// Define headings actions added by the plugin
function plugin_headings_appliances($type,$ID,$withtemplate=0) {
   global $CFG_GLPI,$LANG;

   switch ($type) {
      case PROFILE_TYPE :
         $prof = new PluginAppliancesProfile();
         if ($prof->GetfromDB($ID) || $prof->createUserAccess($ID)) {
            $prof->showForm($CFG_GLPI["root_doc"]."/plugins/appliances/front/profile.form.php",$ID);
         }
         break;

      default :
         if (in_array($type, PluginAppliancesAppliance::getTypes())) {
            echo "<div class='center'>";
            PluginAppliancesAppliance::showAssociated($type,$ID, $withtemplate);
            echo "</div>";
         }
   }
}


// Define PDF informations added by the plugin
function plugin_headings_actionpdf_appliances($type) {

   if (in_array($type,PluginAppliancesAppliance::getTypes())) {
      return array(1 => "plugin_headings_appliances_PDF");
   }
   return false;
}


// Genrerate PDF with informations added by the plugin
// Define headings actions added by the plugin
function plugin_headings_appliances_PDF($pdf,$ID,$type) {

   if (in_array($type, PluginAppliancesAppliance::getTypes())) {
      echo PluginAppliancesAppliance::showAssociated_PDF($pdf,$ID,$type);
   }
}

/* TODO : A revoir avec data injection quand il sera porte en 0.80
function plugin_appliances_data_injection_variables() {
   global $IMPORT_PRIMARY_TYPES, $DATA_INJECTION_MAPPING, $LANG, $IMPORT_TYPES,
          $DATA_INJECTION_INFOS;

   if (plugin_appliances_haveRight("appliances","w")) {
      if (!in_array(PLUGIN_APPLIANCES_TYPE, $IMPORT_PRIMARY_TYPES)) {
         //Add types of objects to be injected by data_injection plugin
         array_push($IMPORT_PRIMARY_TYPES, PLUGIN_APPLIANCES_TYPE);



         $DATA_INJECTION_MAPPING[PLUGIN_APPLIANCES_TYPE]['name']['table'] = 'glpi_plugin_applicatifs';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLIANCES_TYPE]['name']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLIANCES_TYPE]['name']['name'] = $LANG['plugin_applicatifs'][8];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLIANCES_TYPE]['name']['type'] = "text";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['table'] = 'glpi_dropdown_plugin_applicatifs_type';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['linkfield'] = 'type';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['name'] = $LANG['plugin_applicatifs'][20];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['table_type'] = "dropdown";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['table'] = 'glpi_dropdown_plugin_applicatifs_environment';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['linkfield'] = 'environment';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['name'] = $LANG['plugin_applicatifs'][3];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['table_type'] = "dropdown";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['table'] = 'glpi_users';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['linkfield'] = 'FK_users';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['name'] = $LANG['plugin_applicatifs'][21];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['table_type'] = "user";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['table'] = 'glpi_groups';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['linkfield'] = 'FK_groups';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['name'] = $LANG['common'][35];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['table_type'] = "single";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['table'] = 'glpi_dropdown_locations';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['field'] = 'completename';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['linkfield'] = 'location';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['name'] = $LANG['plugin_applicatifs'][2];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['table_type'] = "dropdown";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['comments']['table'] = 'glpi_plugin_applicatifs';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['comments']['field'] = 'comments';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['comments']['name'] = $LANG['plugin_applicatifs'][12];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['comments']['type'] = "text";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['recursive']['table'] = 'glpi_plugin_applicatifs';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['recursive']['field'] = 'recursive';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['recursive']['name'] = $LANG["entity"][9];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['recursive']['type'] = 'integer';

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['table'] = 'glpi_dropdown_plugin_applicatifs_type';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['field'] = 'name';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['linkfield'] = 'type';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['name'] = $LANG['plugin_applicatifs'][20];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['table_type'] = "dropdown";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['table'] = 'glpi_dropdown_plugin_applicatifs_environment';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['field'] = 'name';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['linkfield'] = 'environment';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['name'] = $LANG['plugin_applicatifs'][3];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['table_type'] = "dropdown";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['table'] = 'glpi_users';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['field'] = 'name';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['linkfield'] = 'FK_users';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['name'] = $LANG['plugin_applicatifs'][21];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['table_type'] = "user";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['table'] = 'glpi_groups';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['field'] = 'name';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['linkfield'] = 'FK_groups';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['name'] = $LANG['common'][35];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['input_type'] = "single";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['table'] = 'glpi_dropdown_locations';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['field'] = 'completename';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['linkfield'] = 'location';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['name'] = $LANG['plugin_applicatifs'][2];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['table_type'] = "dropdown";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['comments']['table'] = 'glpi_plugin_applicatifs';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['comments']['field'] = 'comments';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['comments']['name'] = $LANG['plugin_applicatifs'][12];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['comments']['type'] = "text";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['recursive']['table'] = 'glpi_plugin_applicatifs';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['recursive']['field'] = 'recursive';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['recursive']['name'] = $LANG["entity"][9];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['recursive']['type'] = 'integer';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['recursive']['input_type'] = 'yesno';
		}
	}
}
*/

/**
 * Hook : options for one type
 *
 * @param $type of item
 *
 * @return array of string which describe the options
 */
function plugin_appliances_prefPDF($type) {
   global $LANG;

   $tabs = array();
   switch ($type) {
      case PLUGIN_APPLIANCES_TYPE :
         $item = new PluginAppliancesAppliance();
         $tabs = $item->defineTabs(1,'');
         unset($tabs[2]); // Custom fields
         break;
   }
   return $tabs;
}


/**
 * Hook to generate a PDF for a type
 *
 * @param $type of item
 * @param $tab_id array of ID
 * @param $tab of option to be printed
 * @param $page boolean true for landscape
 */
function plugin_appliances_generatePDF($type, $tab_id, $tab, $page=0) {

   $pdf = new simplePDF('a4', ($page ? 'landscape' : 'portrait'));
   $nb_id = count($tab_id);

   foreach ($tab_id as $key => $ID) {
      if (plugin_pdf_add_header($pdf,$ID,$type)) {
         $pdf->newPage();
      } else {
         // Object not found or no right to read
         continue;
      }

      switch ($type) {
         case PLUGIN_APPLIANCES_TYPE :
            $appli = new PluginAppliancesAppliance();
            if (!$appli->getFromDB($ID)) {
               continue;
            }
            $appli->show_PDF($pdf,$ID);

            foreach($tab as $i) {
               switch($i) { // See plugin_appliance::defineTabs();
                  case 1 :
                     $appli->showItem_PDF($pdf);
                     break;

                  case 6 :
                     plugin_pdf_ticket($pdf,$ID,$type);
                     plugin_pdf_oldticket($pdf,$ID,$type);
                     break;

                  case 9 :
                     plugin_pdf_financial($pdf,$ID,$type);
                     plugin_pdf_contract ($pdf,$ID,$type);
                     break;

                  case 10 :
                     plugin_pdf_document($pdf,$ID,$type);
                     break;

                  case 11 :
                     plugin_pdf_note($pdf,$ID,$type);
                     break;

                  case 12 :
                     plugin_pdf_history($pdf,$ID,$type);
                     break;

                  default :
                     plugin_pdf_pluginhook($i,$pdf,$ID,$type);
               }
            }
            break;
      } // Switch type
   } // Each ID
   $pdf->render();
}

?>