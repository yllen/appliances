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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


/**
 * Class PluginAppliancesOptvalue_Item
**/
class PluginAppliancesOptvalue_Item extends CommonDBTM {


   /**
    * @param $item   Appliance Object
    *
    * @return integer
    **/
   static function countForAppliance(Appliance $item) {

      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_appliances_items',
                                        ['appliances_id' => $item->getID()]);
   }


   /**
    * Get Tab Name used for itemtype
    *
    * @see CommonGLPI getTabNameForItem()
    **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Appliance') {
         $nb = '';
         if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = self::countForAppliance($item);
         }
         return self::createTabEntry(_n('Associated item from plugin',
                                        'Associated items from plugin', $nb, 'appliance'), $nb);
      }
      return '';
   }


   /**
    * show Tab content
    *
    * @see CommonGLPI::displayTabContentForItem()
    **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Appliance') {
         self::showForApplianceFromPlugin($item);
      }
      return true;
   }


   /**
    * Show the Device associated with an applicatif
    *
    * Called from the applicatif form
    *
    * @param $appli   PluginAppliancesAppliance object
    *
    * @return bool
    **/
   static function showForApplianceFromPlugin(Appliance $appli) {
      global $DB,$CFG_GLPI;

      $instID = $appli->fields['id'];

      if (!$appli->can($instID, READ)) {
         return false;
      }

      $canedit = $appli->can($instID, UPDATE);

      $result = $DB->request(['SELECT'   => 'itemtype',
                              'DISTINCT' => true,
                              'FROM'     => 'glpi_appliances_items',
                              'WHERE'    => ['appliances_id' => $instID]]);
      $number = count($result);

      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }
      $rand = mt_rand();

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>".__('Type')."</th>";
      echo "<th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>".__('Entity')."</th>";
      }
      if (isset($appli->fields["relationtype"])) {
         echo "<th>".__('Item to link', 'appliances')."<br>".__('User fields', 'appliances')."</th>";
      }
      echo "<th>".__('Serial number')."</th>";
      echo "<th>".__('Inventory number')."</th>";
      echo "</tr>";

      $dbu = new DbUtils();
      foreach ($result as $id => $row) {
         $type = $row['itemtype'];
         if (!($item = $dbu->getItemForItemtype($type))) {
            continue;
         }
         if ($item->canView()) {
            // Ticket and knowbaseitem can't be associated to an appliance
            $column = "name";

            $query = ['SELECT'    => [$item->getTable().'.*',
                                      'glpi_appliances_items.id AS IDD',
                                      'glpi_entities.id AS entity'],
                      'FROM'      => 'glpi_appliances_items',
                      'LEFT JOIN' => [$dbu->getTableForItemType($type)
                                       =>['FKEY' => [$item->getTable()       => 'id',
                                                     'glpi_appliances_items' => 'items_id'],
                                                    ['glpi_appliances_items.itemtype' => $type]],
                                     'glpi_entities'
                                      => ['FKEY' => ['glpi_entities'   => 'id',
                                                     $item->getTable() => 'entities_id']]],
                      'WHERE'     => ['glpi_appliances_items.appliances_id' => $instID]
                                     + getEntitiesRestrictCriteria($item->getTable())];

            if ($item->maybeTemplate()) {
               $query['WHERE'][$item->getTable().'.is_template'] = 0;
            }
            $query['ORDER'] = ['glpi_entities.completename', $item->getTable().'.'.$column];


            if ($result_linked = $DB->request($query)) {
               if (count($result_linked)) {
                  Session::initNavigateListItems($type, _n('Appliance', 'Appliances', 2, 'appliances')."
                                                = ".$appli->getNameID());

                  foreach ($result_linked as $id => $data) {
                     $item->getFromDB($data["id"]);
                     Session::addToNavigateListItems($type, $data["id"]);
                     $name = $item->getLink();

                     echo "<tr class='tab_bg_1'>";
                     echo "<td class='center'>".$item->getTypeName(1)."</td>";
                     echo "<td class='center' ".
                           (isset($data['deleted']) && $data['deleted']?"class='tab_bg_2_2'":"").">".
                          $name."</td>";
                     if (Session::isMultiEntitiesMode()) {
                        echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
                              $data['entity']). "</td>";
                     }

                     if (isset($appli->fields["relationtype"]) && $appli->fields["relationtype"]) {
                        echo "<td class='center'>".
                              PluginAppliancesRelation::getTypeName($appli->fields["relationtype"]).
                              "&nbsp;:&nbsp;";
                              PluginAppliancesRelation::showList($appli->fields["relationtype"],
                                                                 $data["IDD"],
                                                                 $item->fields["entities_id"],
                                                                 $canedit);
                              PluginAppliancesOptvalue_Item::showList($type, $data["id"], $instID,
                                                                      $canedit);
                        echo "</td>";
                     }

                     echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
                     echo "<td class='center'>".
                           (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
                     echo "</tr>";
                  }
               }
            }
         }
      }
      echo "</table></div>";
   }


   /**
    * Show the optional values for a item / applicatif
    *
    * @param $itemtype                type of the item
    * @param $items_id                ID of the item
    * @param $appliances_id           ID of the applicatif
    * @param $canedit                 if user is allowed to edit the values
    *    - canedit the device if called from the device form
    *    - must be false if called from the applicatif form
   **/
   static function showList ($itemtype, $items_id, $appliances_id, $canedit) {
      global $DB, $CFG_GLPI;

      $result_app_opt = $DB->request(['FROM'  => 'glpi_plugin_appliances_optvalues',
                                      'WHERE' => ['appliances_id' => $appliances_id],
                                      'ORDER' => 'vvalues']);
      $number  = count($result_app_opt);

      if ($canedit) {
         echo "<form method='post' action='".$CFG_GLPI["root_doc"].
               "/plugins/appliances/front/optvalue.form.php'>";
         echo "<input type='hidden' name='number_champs' value='".$number."'>";
      }
      echo "<table>";

      for ($i=1 ; $i<=$number ; $i++) {
         if ($data_opt = $result_app_opt->next()) {
            $query_val = $DB->request(['SELECT' => 'vvalue',
                                       'FROM'   => 'glpi_plugin_appliances_optvalues_items',
                                       'WHERE'  => ['plugin_appliances_optvalues_id' => $data_opt["id"],
                                                    'items_id'                       => $items_id]]);
            $data_val = $query_val->next();
            $vvalue     = ($data_val? $data_val['vvalue'] : "");
            if (empty($vvalue) && !empty($data_opt['ddefault'])) {
               $vvalue = $data_opt['ddefault'];
            }
            echo "<tr><td>".$data_opt['champ']."&nbsp;</td><td>";
            if ($canedit) {
               echo "<input type='hidden' name='opt_id$i' value='".$data_opt["id"]."'>";
               echo "<input type='hidden' name='ddefault$i' value='".$data_opt["ddefault"]."'>";
               echo "<input type='text' name='vvalue$i' value='".$vvalue."'>";
            } else {
               echo $vvalue;
            }
            echo "</td></tr>";

         }

         echo "<input type='hidden' name='opt_id$i' value='".$data_opt["id"]."'>";
      } // For

      echo "</table>";

      if ($canedit) {
         echo "<input type='hidden' name='itemtype' value='".$itemtype."'>";
         echo "<input type='hidden' name='items_id' value='".$items_id."'>";
         echo "<input type='hidden' name='appliances_id' value='".$appliances_id."'>";
         echo "<input type='hidden' name='number_champs' value='".$number."'>";
         echo "<input type='submit' name='add_opt_val' value='"._sx('button', 'Update')."'
                class='submit'>";
         Html::closeForm();
      }
   }


   /**
    * Update to optional values for an appliance / item
    *
    * @param $input array on input value (form)
   **/
   function updateList($input) {
      global $DB;

      $number_champs = $input["number_champs"];
      for ($i=1 ; $i<=$number_champs ; $i++) {
         $opt_id   = "opt_id$i";
         $vvalue   = "vvalue$i";
         $ddefault = "ddefault$i";

         $query_app = $DB->request(['SELECT' => 'id',
                                    'FROM'   => 'glpi_plugin_appliances_optvalues_items',
                                    'WHERE'  => ['plugin_appliances_optvalues_id' => $input[$opt_id],
                                                 'itemtype'                       => $input['itemtype'],
                                                 'items_id'                       => $input['items_id']]]);

         if ($data = $query_app->next()) {
            // l'entrée existe déjà, il faut faire un update ou un delete
            if (empty($input[$vvalue])
                || ($input[$vvalue] == $input[$ddefault])) {
               $this->delete($data);
            } else {
               $data['vvalue'] = $input[$vvalue];
               $this->update($data);
            }

         } else if (!empty($input[$vvalue])
                    && ($input[$vvalue] != $input[$ddefault])) {
            // l'entrée n'existe pas
            // et la valeur saisie est non nulle -> on fait un insert
            foreach ($DB->request(['SELECT' => 'id',
                                   'FROM'   => 'glpi_plugin_appliances_optvalues',
                                   'WHERE'  => ['ddefault' => $input[$ddefault]]]) as $optid) {
               $data = ['plugin_appliances_optvalues_id' => $optid['id'],
                        'itemtype'                       => $input['itemtype'],
                        'items_id'                       => $input['items_id'],
                        'vvalue'                         => $input[$vvalue]];
               $this->add($data);
            }
         }
      } // For
   }

}
