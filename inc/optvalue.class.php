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


class PluginAppliancesOptvalue extends CommonDBTM {


   function cleanDBonPurge() {

      $temp = new PluginAppliancesOptvalue_Item();
      $temp->deleteByCriteria(array('plugin_appliances_optvalues_id' => $this->fields['id']));
   }


   /**
    * Display list of Optvalues for an appliance
    *
    * @param $appli PluginAppliancesAppliance instance
    *
    * @return nothing (display form)
    */
   function showList (PluginAppliancesAppliance $appli) {
      global $DB, $LANG, $CFG_GLPI;

      if (!$appli->can($appli->fields['id'],'r')) {
         return false;
      }
      $canedit = $appli->can($appli->fields['id'],'w');

      $rand = mt_rand();
      if ($canedit) {
         echo "<form method='post' name='optvalues_form$rand' id='optvalues_form$rand' action=\"".
               $CFG_GLPI["root_doc"]."/plugins/appliances/front/appliance.form.php\">";
      }

      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>".$LANG['plugin_appliances'][24]."</th></tr>\n";

      $query_app = "SELECT *
                    FROM `glpi_plugin_appliances_optvalues`
                    WHERE `plugin_appliances_appliances_id` = '".$appli->fields['id']."'
                    ORDER BY `vvalues`";

      $result_app    = $DB->query($query_app);
      $number_champs = $DB->numrows($result_app);
      $number_champs++;
      for ($i=1 ; $i <= $number_champs ; $i++) {
         if ($data = $DB->fetch_array($result_app)) {
            $champ = $data["champ"];
            $ddefault = $data["ddefault"];
         } else {
            $champ = '';
            $ddefault = '';
         }
         echo "<tr class='top tab_bg_1'>";

         if ($i == 1) {
            echo "<td rowspan='$number_champs'>".$LANG['plugin_appliances'][25]."&nbsp;:</td>";
         }
         echo "<td><input type='text' name='champ$i' value='$champ' size='35'></td>\n";
         if ($i == 1) {
            echo "<td rowspan='$number_champs'>".$LANG['plugin_appliances'][26]."&nbsp;:</td>";
         }
         echo "<td><input type='text' name='ddefault$i' value='$ddefault' size='35'></td></tr>\n";
      }

      if ($canedit) {
         echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
         echo "<input type='hidden' name='plugin_appliances_appliances_id' value='".
                $appli->fields['id']."'>\n";
         echo "<input type='hidden' name='number_champs' value='$number_champs'>\n";
         echo "<input type='submit' name='update_optvalues' value=\"".$LANG['buttons'][7]."\"
                class='submit'>";
         echo "</td></tr>\n</table></div></form>";
      } else {
         echo "</table></div>";
      }
      return true;
   }


   /**
    * Update the list of Optvalues defined for an appliance
    *
    * @param $input array of input data (form)
   **/
   function updateList($input) {
      global $DB;

      if (!isset($input['number_champs']) || !isset($input['plugin_appliances_appliances_id'])) {
         return false;
      }
      $number_champs = $input['number_champs'];

      for ($i=1 ; $i<=$number_champs ; $i++) {
         $champ    = "champ$i";
         $ddefault = "ddefault$i";

         $query_app = "SELECT `id`
                       FROM `glpi_plugin_appliances_optvalues`
                       WHERE `plugin_appliances_appliances_id`
                                 = '".$input['plugin_appliances_appliances_id']."'
                             AND `vvalues` = '$i'";
         $result_app = $DB->query($query_app);

         if ($data = $DB->fetch_array($result_app)) {
            // l'entrée existe déjà, il faut faire un update ou un delete
            if (empty($input[$champ])) {
               $this->delete($data);
            } else {
               $data['champ'] = $input[$champ];
               $data['ddefault'] = $input[$ddefault];
               $this->update($data);
            }

         } else if (!empty($input[$champ])) {
            // l'entrée n'existe pas
            // et la valeur saisie est non nulle -> on fait un insert
            $data = array('plugin_appliances_appliances_id' => $input['plugin_appliances_appliances_id'],
                          'champ'                           => $input[$champ],
                          'ddefault'                        => $input[$ddefault],
                          'vvalues'                         => $i);
            $this->add($data);
         }
      } // for
   }
}

?>