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


class PluginAppliancesAppliance_Item extends CommonDBRelation {

   // From CommonDBRelation
   public $itemtype_1 = 'PluginAppliancesAppliance';
   public $items_id_1 = 'plugin_appliances_appliances_id';

   public $itemtype_2 = 'itemtype';
   public $items_id_2 = 'items_id';


   function cleanDBonPurge() {

      $temp = new PluginAppliancesOptvalue_Item();
      $temp->deleteByCriteria(array('itemtype' => $this->fields['itemtype'],
                                    'items_id' => $this->fields['items_id']));

      $temp = new PluginAppliancesRelation();
      $temp->deleteByCriteria(array('plugin_appliances_appliances_items_id' => $this->fields['id']));
   }


   /**
    * Hook called After an item is uninstall or purge
    */
   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         array('itemtype' => $item->getType(),
               'items_id' => $item->getField('id'))
      );
   }


   static function countForAppliance(PluginAppliancesAppliance $item) {

      return countElementsInTable('glpi_plugin_appliances_appliances_items',
                                  "`plugin_appliances_appliances_id` = '".$item->getID()."'");
   }


   /**
    * Show the Device associated with an applicatif
    *
    * Called from the applicatif form
   **/
   static function showForAppliance(PluginAppliancesAppliance $appli) {
      global $DB,$CFG_GLPI, $LANG;

      $instID = $appli->fields['id'];

      if (!$appli->can($instID,"r")) {
         return false;
      }
      $rand = mt_rand();

      $canedit = $appli->can($instID,'w');

      $query = "SELECT DISTINCT `itemtype`
                FROM `glpi_plugin_appliances_appliances_items`
                WHERE `plugin_appliances_appliances_id` = '$instID'
                ORDER BY `itemtype`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      $i = 0;

      if (isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      echo "<form method='post' name='appliances_form$rand' id='appliances_form$rand' action=\"".
            $CFG_GLPI["root_doc"]."/plugins/appliances/front/appliance.form.php\">";

      echo "<div class='center'><table class='tab_cadre_fixehov'>";
      echo "<tr><th colspan='".($canedit?(6+$colsup):(5+$colsup))."'>".
            $LANG['plugin_appliances'][7]."&nbsp;:</th></tr><tr>";
      if ($canedit) {
         echo "<th>&nbsp;</th>";
      }
      echo "<th>".$LANG['common'][17]."</th>";
      echo "<th>".$LANG['common'][16]."</th>";
      if (isMultiEntitiesMode()) {
         echo "<th>".$LANG['entity'][0]."</th>";
      }
      if ($appli->fields["relationtype"]) {
         echo "<th>".$LANG['plugin_appliances'][22]."<br>".$LANG['plugin_appliances'][24]."</th>";
      }
      echo "<th>".$LANG['common'][19]."</th>";
      echo "<th>".$LANG['common'][20]."</th>";
      echo "</tr>";

      for ($i=0 ; $i < $number ; $i++) {
         $type = $DB->result($result, $i, "itemtype");
         if (!class_exists($type)) {
            continue;
         }
         $item = new $type();
         if ($item->canView()) {
            $column = "name";
            if ($type == 'Ticket') {
               $column = "id";
            }
            if ($type == 'KnowbaseItem') {
               $column = "question";
            }

            $query = "SELECT `".$item->getTable()."`.*,
                             `glpi_plugin_appliances_appliances_items`.`id` AS IDD,
                             `glpi_entities`.`id` AS entity
                      FROM `glpi_plugin_appliances_appliances_items`, ".getTableForItemType($type)."
                      LEFT JOIN `glpi_entities`
                           ON (`glpi_entities`.`id` = `".$item->getTable()."`.`entities_id`)
                      WHERE `".$item->getTable()."`.`id`
                                 = `glpi_plugin_appliances_appliances_items`.`items_id`
                            AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$type'
                            AND `glpi_plugin_appliances_appliances_items`.`plugin_appliances_appliances_id`
                                 = '$instID' ".
                            getEntitiesRestrictRequest(" AND ", $item->getTable());

            if ($item->maybeTemplate()) {
               $query .= " AND `".$item->getTable()."`.`is_template` = '0'";
            }
            $query.=" ORDER BY `glpi_entities`.`completename`, `".$item->getTable()."`.$column";

            if ($result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {
                  initNavigateListItems($type,$LANG['plugin_appliances']['title'][1]." = ".
                                              $appli->getNameID());

                  while ($data = $DB->fetch_assoc($result_linked)) {
                     $item->getFromDB($data["id"]);
                     addToNavigateListItems($type,$data["id"]);
                     $ID = "";
                     if ($type == 'Ticket') {
                        $data["name"] = $LANG['job'][38]." ".$data["id"];
                     }
                     if ($type == 'KnowbaseItem') {
                        $data["name"] = $data["question"];
                     }

                     if($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                        $ID = " (".$data["id"].")";
                     }
                     $name= $item->getLink();

                     echo "<tr class='tab_bg_1'>";
                     if ($canedit) {
                        echo "<td width='10'>";
                        $sel = "";
                        if (isset($_GET["select"]) && $_GET["select"] == "all") {
                           $sel = "checked";
                        }
                        echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
                        echo "</td>";
                     }
                     echo "<td class='center'>".$item->getTypeName()."</td>";
                     echo "<td class='center' ".
                           (isset($data['deleted']) && $data['deleted']?"class='tab_bg_2_2'":"").">".
                           $name."</td>";
                     if (isMultiEntitiesMode()) {
                        echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
                                                                             $data['entity']).
                              "</td>";
                     }

                     if ($appli->fields["relationtype"]) {
                        echo "<td class='center'>".
                           PluginAppliancesRelation::getTypeName($appli->fields["relationtype"]).
                           "&nbsp;:&nbsp;";
                        PluginAppliancesRelation::showList($appli->fields["relationtype"],
                                                           $data["IDD"],
                                                           $item->fields["entities_id"], false);
                        PluginAppliancesOptvalue_Item::showList($type, $data["id"], $instID, false);
                        echo "</td>";
                     }

                     echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"].""
                                                                       :"-")."</td>";
                     echo "<td class='center'>".
                           (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
                     echo "</tr>";
                  }
               }
            }
         }
      }

      if ($canedit) {
         echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";

         echo "<input type='hidden' name='conID' value='$instID'>";
         Dropdown::showAllItems("item", 0, 0,
                                ($appli->fields['is_recursive']?-1:$appli->fields['entities_id']),
                                 $appli->getTypes());
         echo "</td>";
         echo "<td colspan='3' class='center' class='tab_bg_2'>";
         echo "<input type='submit' name='additem' value='".$LANG['buttons'][8]."' class='submit'>";
         echo "</td></tr>";
         echo "</table></div>" ;

         openArrowMassive("appliances_form$rand", true);
         closeArrowMassive('deleteitem', $LANG['buttons'][6]);

      } else {
         echo "</table></div>";
      }
      echo "</form>";
   }


   function getTabNameForItem(CommonGLPI $item) {
      global $LANG;

      if ($item->getType()=='PluginAppliancesAppliance') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry($LANG['title'][30], self::countForAppliance($item));
         }
         return $LANG['title'][30];
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='PluginAppliancesAppliance') {
         self::showForAppliance($item);
      }
      return true;
   }
}
