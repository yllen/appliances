<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 appliances - Appliances plugin for GLPI
 Copyright (C) 2003-2013 by the appliances Development Team.

 https://forge.indepnet.net/projects/appliances
 -------------------------------------------------------------------------

 LICENSE

 This file is part of appliances.

 appliances is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 appliances is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with appliances. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


class PluginAppliancesProfile extends CommonDBTM {


   // if profile deleted
   static function cleanProfile(Profile $prof) {

      $plugprof = new self();
      $plugprof->delete(array('id' => $prof->getID()));
   }


   // if profile cloned
   static function cloneProfile(Profile $prof) {

      $plugprof = new self();
      if ($plugprof->getFromDB($prof->input['_old_id'])) {
         $input = ToolBox::addslashes_deep($plugprof->fields);
         $input['id'] = $prof->getID();
         $plugprof->add($input);
      }
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

      $target = $this->getFormURL();
      if (isset($options['target'])) {
        $target = $options['target'];
      }

      if (!Session::haveRight("profile","r")) {
         return false;
      }

      $canedit = Session::haveRight("profile", "w");
      $prof = new Profile();
      if ($ID) {
         $this->getFromDB($ID);
         $prof->getFromDB($ID);
      }
      echo "<form action='".$target."' method='post'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='2'>".sprintf(__('%1$s %2$s'), __('Rights management', 'appliances'),
                                          $this->fields["name"]);
      echo "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>".__('Appliances', 'appliances')."</td><td>";

      if ($prof->fields['interface'] != 'helpdesk') {
         Profile::dropdownNoneReadWrite("appliance", $this->fields["appliance"], 1, 1, 1);
      } else {
         _e('No access');
      }
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>".__('Linkable items to a ticket - Appliances')."</td><td>";
      if ($prof->fields['create_ticket']) {
         Dropdown::showYesNo("open_ticket", $this->fields["open_ticket"]);
      } else {
         echo Dropdown::getYesNo(0);
      }
      echo "</td></tr>";

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='2'>";
         echo "<input type='hidden' name='id' value='".$ID."'>";
         echo "<input type='submit' name='update_user_profile' value=\""._sx('button', 'Update')."\"
               class='submit'>";
         echo "</td></tr>";
      }
      echo "</table>";
      Html::closeForm();
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

      if ($item->getType()=='Profile') {
         return __('Appliances', 'appliances');
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