<?php
/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

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
// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/*
 * This is a special relation between
 *    a glpi_application_items
 *    a dropdwn (glpi_locations, glpi_domains, glpi_networks)
 */
class PluginAppliancesRelation extends CommonDBTM {

   // From CommonDBTM
   public $table = 'glpi_plugin_appliances_relations';
   public $type  = PLUGIN_APPLIANCES_RELATIONS;

   /**
    * Clean object veryfing criteria (when a relation is deleted)
    *
    * @param $crit array of criteria (should be an index)
    */
   public function clean ($crit) {
      global $DB;

      foreach ($DB->request($this->table, $crit) as $data) {
         $this->delete($data);
      }
   }

   static function getTypeTable ($value) {

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

   static function getTypeName($value=0) {
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

   static function dropdown($myname,$value=0) {
      global $LANG;

      dropdownArrayValues($myname, array (0 => "-----",
                                          1 => $LANG['common'][15],  // Location
                                          2 => $LANG['setup'][88],   // Réseau
                                          3 => $LANG['setup'][89]),  // Domain
                          $value);
   }

}

?>