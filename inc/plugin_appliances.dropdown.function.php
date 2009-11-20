<?php
/*
   ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

function plugin_appliances_getTypes () {

   static $types = array(COMPUTER_TYPE, PRINTER_TYPE, MONITOR_TYPE, PERIPHERAL_TYPE,
                         NETWORKING_TYPE, PHONE_TYPE, SOFTWARE_TYPE);
   // temporary disabled TRACKING_TYPE,

   $plugin = new Plugin();
   if ($plugin->isActivated("rack")) {
      $types[] = PLUGIN_RACK_TYPE;
   }
   foreach ($types as $key=>$type) {
      if (!haveTypeRight($type,'r')) {
         unset($types[$key]);
      }
   }
   return $types;
}


function plugin_appliances_dropdownappliances($myname,$entity_restrict='',$used=array()) {
   global $DB,$LANG,$CFG_GLPI;

   $rand = mt_rand();

   $where =" WHERE `glpi_plugin_appliances_appliances`.`is_deleted` = '0' ".
                   getEntitiesRestrictRequest("AND","glpi_plugin_appliances_appliances",'',
                                              $entity_restrict,true);

   if (count($used)) {
      $where .= " AND `id` NOT IN ('0'";
      foreach ($used as $ID) {
         $where .= ", '$ID'";
      }
      $where .= ")";
   }

   $query = "SELECT *
             FROM `glpi_plugin_appliances_appliancetypes`
             WHERE `id` IN (SELECT DISTINCT `appliancetypes_id`
                            FROM `glpi_plugin_appliances_appliances`
                            $where)
             GROUP BY `name`";
   $result = $DB->query($query);

   echo "<select name='_type' id='type_appliances'>\n";
   echo "<option value='0'>------</option>\n";
   while ($data = $DB->fetch_assoc($result)) {
      echo "<option value='".$data['id']."'>".$data['name']."</option>\n";
   }
   echo "</select>\n";

   $params = array('type_appliances' => '__VALUE__',
                   'entity_restrict' => $entity_restrict,
                   'rand'            => $rand,
                   'myname'          => $myname,
                   'used'            => $used);

   ajaxUpdateItemOnSelectEvent("type_appliances","show_$myname$rand",
            $CFG_GLPI["root_doc"]."/plugins/appliances/ajax/dropdownTypeAppliances.php",$params);

   echo "<span id='show_$myname$rand'>";
   $_POST["entity_restrict"] = $entity_restrict;
   $_POST["type_appliances"] = 0;
   $_POST["myname"] = $myname;
   $_POST["rand"] = $rand;
   $_POST["used"] = $used;
   include (GLPI_ROOT."/plugins/appliances/ajax/dropdownTypeAppliances.php");
   echo "</span>\n";

   return $rand;
}


function plugin_appliances_relationtypes($myname,$value=0) {
   global $LANG;

   dropdownArrayValues($myname, array (0 => "-----",
                                       1 => $LANG['common'][15],  // Location
                                       2 => $LANG['setup'][88],   // Réseau
                                       3 => $LANG['setup'][89]),  // Domain
                       $value);
}


function plugin_appliances_getrelationtypename($value=0) {
   global $LANG;

   switch ($value) {
      case 1 : // Location
         $name = $LANG['common'][15];
         break;

      case 2 : // Réseau
         $name = $LANG['setup'][88];
         break;

      case 3 : // Domain
         $name = $LANG['setup'][89];
         break;

      default :
         $name = "&nbsp;";
   }
   return $name;
}


function plugin_appliances_getrelationtypetable ($value) {

   switch ($value) {
      case 1 : // Location
         $name = "glpi_locations";
         break;

      case 2 : // Réseau
         $name = "glpi_networks";
         break;

      case 3 : // Domain
         $name = "glpi_domains";
         break;

      default:
         $name ="";
   }
   return $name;
}

?>