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
// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


class PluginAppliancesProfile extends CommonDBTM {


   //if profile deleted
   static function cleanProfiles(Profile $prof) {

      $plugprof = new self();
      $plugprof->delete(array('id' => $prof->getField("id")));
   }


   static function select() {

      $prof = new self();
      if ($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_appliances_profiles"] = $prof->fields;
      } else {
         unset($_SESSION["glpi_plugin_appliances_profiles"]);
      }
   }


   //profiles modification
   function showForm($ID, $options=array()) {
      global $LANG;

      $target = $this->getFormURL();
      if (isset($options['target'])) {
        $target = $options['target'];
      }

      if (!haveRight("profile","r")) {
         return false;
      }

      $canedit = haveRight("profile","w");
      $prof = new Profile();
      if ($ID) {
         $this->getFromDB($ID);
         $prof->getFromDB($ID);
      }
      echo "<form action='".$target."' method='post'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='2'>".$LANG['plugin_appliances']['profile'][0]." ".$this->fields["name"].
            "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>".$LANG['plugin_appliances']['title'][1]." :</td><td>";

      if ($prof->fields['interface']!='helpdesk') {
         Profile::dropdownNoneReadWrite("appliance", $this->fields["appliance"], 1, 1, 1);
      } else {
         echo $LANG['profiles'][12]; // No access;
      }
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>".$LANG['setup'][352]." - ".$LANG['plugin_appliances']['title'][1]." : </td><td>";
      if ($prof->fields['create_ticket']) {
         Dropdown::showYesNo("open_ticket", $this->fields["open_ticket"]);
      } else {
         echo Dropdown::getYesNo(0);
      }
      echo "</td></tr>";

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='2'>";
         echo "<input type='hidden' name='id' value=$ID>";
         echo "<input type='submit' name='update_user_profile' value=\"".$LANG['buttons'][7]."\"
               class='submit'>";
         echo "</td></tr>";
      }
      echo "</table></form>";
   }


   static function createAdminAccess($ID) {

      $myProf = new self();
      if (!$myProf->GetfromDB($ID)) {
         $Profile = new Profile();
         $Profile->GetfromDB($ID);
         $name = $Profile->fields["name"];

         $myProf->add(array('id'          => $ID,
                            'name'        => $name,
                            'appliance'   => 'w',
                            'open_ticket' => '1'));
      }
   }


   function createUserAccess($Profile) {

      return $this->add(array('id'   => $Profile->getField('id'),
                              'name' => $Profile->getField('name')));
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType()=='Profile') {
            return $LANG['plugin_appliances']['title'][1];
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getField('id');
         $prof = new self();
         if ($prof->GetfromDB($ID) || $prof->createUserAccess($item)) {
            $prof->showForm($ID);
         }
      }
      return true;
   }
}

?>