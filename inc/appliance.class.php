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


class PluginAppliancesAppliance extends CommonDBTM {

   public $dohistory = true;


   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_appliances'][1];
   }


   function canCreate() {
      return plugin_appliances_haveRight('appliance', 'w');
   }


   function canView() {
      return plugin_appliances_haveRight('appliance', 'r');
   }


   /**
    * Retrieve an Appliance from the database using its externalid (unique index)
    *
    * @param $extid string externalid
    *
    * @return true if succeed else false
   **/
   function getFromDBbyExternalID($extid) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `externalid` = '$extid'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
      }
      return false;
   }


   function getSearchOptions() {
      global $LANG;

      $tab = array();

      $tab['common'] = $LANG['plugin_appliances']['title'][1];

      $tab[1]['table']         = 'glpi_plugin_appliances_appliances';
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = $LANG['common'][16];
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      $tab[1]['displaytype']   = 'text';
      $tab[1]['checktype']     = 'text';
      $tab[1]['injectable']    = true;

      $tab[2]['table']        = 'glpi_plugin_appliances_appliancetypes';
      $tab[2]['field']        = 'name';
      $tab[2]['name']         = $LANG['common'][17];
      $tab[2]['displaytype']  = 'dropdown';
      $tab[2]['checktype']    = 'text';
      $tab[2]['injectable']   = true;

      $tab += Location::getSearchOptionsToAdd();

      $tab[3]['displaytype']  = 'dropdown';
      $tab[3]['checktype']    = 'text';
      $tab[3]['injectable']   = true;

      $tab[4]['table']        = 'glpi_plugin_appliances_appliances';
      $tab[4]['field']        =  'comment';
      $tab[4]['name']         =  $LANG['common'][25];
      $tab[4]['datatype']     =  'text';
      $tab[4]['displaytype']  = 'multiline_text';
      $tab[4]['injectable']   = true;

      $tab[5]['table']         = 'glpi_plugin_appliances_appliances_items';
      $tab[5]['field']         = 'items_id';
      $tab[5]['name']          = $LANG['plugin_appliances'][7];
      $tab[5]['massiveaction'] = false;
      $tab[5]['forcegroupby']  =  true;
      $tab[5]['injectable']    = false;

      $tab[6]['table']        = 'glpi_users';
      $tab[6]['field']        = 'name';
      $tab[6]['name']         = $LANG['plugin_appliances'][21];
      $tab[6]['displaytype']  = 'user';
      $tab[6]['checktype']    = 'text';
      $tab[6]['injectable']   = true;

      $tab[7]['table']         = 'glpi_plugin_appliances_appliances';
      $tab[7]['field']         = 'is_recursive';
      $tab[7]['name']          = $LANG['entity'][9];
      $tab[7]['massiveaction'] = false;
      $tab[7]['datatype']      = 'bool';
      $tab[7]['displaytype']   = 'bool';
      $tab[7]['checktype']     = 'decimal';
      $tab[7]['injectable']    = true;

      $tab[8]['table']        = 'glpi_groups';
      $tab[8]['field']        = 'name';
      $tab[8]['name']         = $LANG['common'][35];
      $tab[8]['displaytype']  = 'dropdown';
      $tab[8]['checktype']    = 'text';
      $tab[8]['injectable']   = true;

      $tab[9]['table']         = 'glpi_plugin_appliances_appliances';
      $tab[9]['field']         = 'date_mod';
      $tab[9]['name']          = $LANG['common'][26];
      $tab[9]['massiveaction'] = false;
      $tab[9]['datatype']      = 'datetime';
      $tab[9]['displaytype']   = 'date';
      $tab[9]['checktype']     = 'date';
      $tab[9]['injectable']    = true;

      $tab[10]['table']       = 'glpi_plugin_appliances_environments';
      $tab[10]['field']       = 'name';
      $tab[10]['name']        = $LANG['plugin_appliances'][3];
      $tab[10]['displaytype'] = 'dropdown';
      $tab[10]['checktype']   = 'text';
      $tab[10]['injectable']  = true;

      $tab[11]['table']       = 'glpi_plugin_appliances_appliances';
      $tab[11]['field']       = 'is_helpdesk_visible';
      $tab[11]['name']        = $LANG['software'][46];
      $tab[11]['datatype']    = 'bool';
      $tab[11]['displaytype'] = 'bool';
      $tab[11]['checktype']   = 'decimal';
      $tab[11]['injectable']  = true;

      $tab[12]['table']       = 'glpi_plugin_appliances_appliances';
      $tab[12]['field']       = 'serial';
      $tab[12]['name']        = $LANG['common'][19];
      $tab[12]['displaytype'] = 'text';
      $tab[12]['checktype']   = 'text';
      $tab[12]['injectable']  = true;

      $tab[13]['table']       = 'glpi_plugin_appliances_appliances';
      $tab[13]['field']       = 'otherserial';
      $tab[13]['name']        = $LANG['common'][20];
      $tab[13]['displaytype'] = 'text';
      $tab[13]['checktype']   = 'text';
      $tab[13]['injectable']  = true;

      $tab[31]['table']       = 'glpi_plugin_appliances_appliances';
      $tab[31]['field']        = 'id';
      $tab[31]['name']         = $LANG['common'][2];
      $tab[31]['massiveaction'] = false;
      $tab[31]['injectable']   = false;

      $tab[80]['table']       = 'glpi_entities';
      $tab[80]['field']       = 'completename';
      $tab[80]['name']        = $LANG['entity'][0];
      $tab[80]['injectable']  = false;

      return $tab;
   }


   function cleanDBonPurge() {

      $temp = new PluginAppliancesAppliance_Item();
      $temp->deleteByCriteria(array('plugin_appliances_appliances_id' => $this->fields['id']));

      $temp = new PluginAppliancesOptvalue();
      $temp->deleteByCriteria(array('plugin_appliances_appliances_id' => $this->fields['id']));
   }


   function defineTabs($options=array()) {
      global $LANG;

      $ong['empty'] = $this->getTypeName();
      if (!$this->isNewItem()) {
         $this->addStandardTab('PluginAppliancesAppliance_Item', $ong, $options);
         $this->addStandardTab('PluginAppliancesOptvalue', $ong, $options);
         $this->addStandardTab('Ticket', $ong, $options);
         $this->addStandardTab('Infocom', $ong, $options);
         $this->addStandardTab('Contract_Item', $ong, $options);
         $this->addStandardTab('Document', $ong, $options);
         $this->addStandardTab('Note', $ong, $options);
         $this->addStandardTab('Log', $ong, $options);
      }
      return $ong;
   }


   /**
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
   **/
   function getSelectLinkedItem() {

      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_appliances_appliances_items`
              WHERE `plugin_appliances_appliances_id` = '" . $this->fields['id']."'";
   }


   function showForm ($ID, $options=array()) {
      global $CFG_GLPI, $LANG;

      if ($ID>0) {
         $this->check($ID,'r');
      } else {
         $this->check(-1, 'w');
         $this->getEmpty();
      }

      $canedit = $this->can($ID,'w');
      $canrecu = $this->can($ID,'recursive');

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][16]."&nbsp;:</td>";
      echo "<td>";
      autocompletionTextField($this, "name", array('size' => 34));
      echo "</td><td>".$LANG['common'][17]."&nbsp;:</td><td>";
      Dropdown::show('PluginAppliancesApplianceType',
                      array('value'  => $this->fields["plugin_appliances_appliancetypes_id"],
                            'entity' => $this->fields["entities_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][10]."&nbsp;:</td><td>";
      if ($canedit) {
         User::dropdown(array('value'  => $this->fields["users_id"],
                              'right'  => 'all',
                              'entity' => $this->fields["entities_id"]));
      } else {
         echo getUsername($this->fields["users_id"]);
      }
      echo "</td>";
      echo "<td>".$LANG['plugin_appliances'][3]."&nbsp;:</td><td>";
      Dropdown::show('PluginAppliancesEnvironment',
                     array('value' => $this->fields["plugin_appliances_environments_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][35]."&nbsp;:</td><td>";
      if ($canedit) {
         Dropdown::show('Group', array('value'  => $this->fields["groups_id"],
                                       'entity' =>$this->fields["entities_id"]));
      } else {
         echo Dropdown::getDropdownName("glpi_groups", $this->fields["groups_id"]);
      }
      echo "</td>";
      echo "<td>".$LANG['common'][19]."&nbsp;:</td>";
      echo "<td >";
      autocompletionTextField($this,'serial');
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][15]."&nbsp;:</td><td>";
      if ($canedit) {
         Dropdown::show('Location', array('value'  => $this->fields["locations_id"],
                                          'entity' => $this->fields["entities_id"]));
      } else {
         echo Dropdown::getDropdownName("glpi_locations",$this->fields["locations_id"]);
      }
      echo "</td>";
      echo "<td>".$LANG['common'][20]."&nbsp;:</td>";
      echo "<td>";
      autocompletionTextField($this,'otherserial');
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['software'][46] . "&nbsp;:</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td>";
      echo "<td rowspan='3'>".$LANG['common'][25]."&nbsp;:</td>";
      echo "<td rowspan='3' class='middle'>";
      echo "<textarea cols='45' rows='5' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      // dropdown relationtype added
      echo "<td>".$LANG['plugin_appliances'][22]."&nbsp;:</td><td>";
      if ($canedit
          && !($ID
               && countElementsInTable(array("glpi_plugin_appliances_relations",
                                             "glpi_plugin_appliances_appliances_items"),
                                       "glpi_plugin_appliances_relations.plugin_appliances_appliances_items_id
                                          = glpi_plugin_appliances_appliances_items.id
                                        AND glpi_plugin_appliances_appliances_items.plugin_appliances_appliances_id
                                          = $ID"))) {
         PluginAppliancesRelation::dropdownType("relationtype", $this->fields["relationtype"]);
      } else {
         echo PluginAppliancesRelation::getTypeName($this->fields["relationtype"]);
         $rand    = mt_rand();
         $comment = $LANG['common'][84];
         $image   = "/pics/lock.png";
         echo "&nbsp;<img alt='' src='".$CFG_GLPI["root_doc"].$image.
               "' onmouseout=\"cleanhide('comment_relationtypes$rand')\" ".
               " onmouseover=\"cleandisplay('comment_relationtypes$rand')\">";
         echo "<span class='over_link' id='comment_relationtypes$rand'>$comment</span>";
      }
      echo "</td></tr>";

      $datestring = $LANG['common'][26]."&nbsp;: ";
      $date       = Toolbox::convDateTime($this->fields["date_mod"]);
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' class='center'>".$datestring.$date;
      echo "</td></tr>";

      $this->showFormButtons($options);
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

      return true;
   }


   /**
    * Show for PDF the current applicatif
    *
    * @param $pdf object for the output
   **/
   function show_PDF ($pdf) {
      global $LANG, $DB;

      $pdf->setColumnsSize(50,50);
      $col1 = '<b>'.$LANG["common"][2].' '.$this->fields['id'].'</b>';
      if (isset($this->fields["date_mod"])) {
         $col2 = $LANG["common"][26].' : '.Toolbox::convDateTime($this->fields["date_mod"]);
      } else {
         $col2 = '';
      }
      $pdf->displayTitle($col1, $col2);

      $pdf->displayLine(
         '<b><i>'.$LANG["common"][16].' :</i></b> '.$this->fields['name'],
         '<b><i>'.$LANG['common'][17].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_plugin_appliances_appliancetypes',
                                                 $this->fields['plugin_appliances_appliancetypes_id'])));
      $pdf->displayLine(
         '<b><i>'.$LANG["common"][10].' :</i></b> '.getUserName($this->fields['users_id']),
         '<b><i>'.$LANG['plugin_appliances'][3].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_plugin_appliances_environments',
                                                 $this->fields['plugin_appliances_environments_id'])));

      $pdf->displayLine(
         '<b><i>'.$LANG["common"][35].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_groups', $this->fields['groups_id'])),
         '<b><i>'.$LANG['common'][19].' :</i></b> '.$this->fields['serial']);

      $pdf->displayLine(
         '<b><i>'.$LANG["common"][15].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_locations', $this->fields['locations_id'])),
         '<b><i>'.$LANG['common'][20].' :</i></b> '.$this->fields['otherserial']);

      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_appliances'][22].' :</i></b> '.
            Html::clean(PluginAppliancesRelation::getTypeName($this->fields["relationtype"])),
         '<b><i>'.$LANG['software'][46].' :</i></b> '.
            Dropdown::getYesNo($this->fields["is_helpdesk_visible"]));

      $query_app = "SELECT `champ`, `ddefault`
                    FROM `glpi_plugin_appliances_optvalues`
                    WHERE `plugin_appliances_appliances_id` = '".$this->fields['id']."'
                    ORDER BY `vvalues`";
      $result_app = $DB->query($query_app);

      $opts = array();
      while ($data = $DB->fetch_array($result_app)) {
         $opts[] = $data["champ"].($data["ddefault"] ? '='.$data["ddefault"] : '');
      }
      $pdf->setColumnsSize(100);
      $pdf->displayLine("<b><i>".$LANG['plugin_appliances'][24]." : </i></b>".implode(', ',$opts));

      $pdf->displayText('<b><i>'.$LANG["common"][25].' : </i></b>', $this->fields['comment']);

      $pdf->displaySpace();
   }


   function showItem_PDF($pdf) {
      global $DB, $CFG_GLPI, $LANG;

      $instID = $this->fields['id'];

      if (!$this->can($instID,"r")) {
         return false;
      }
      if (!plugin_appliances_haveRight("appliance","r")) {
         return false;
      }

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.$LANG['plugin_appliances'][7].'</b>');

      $query = "SELECT DISTINCT `itemtype`
                FROM `glpi_plugin_appliances_appliances_items`
                WHERE `plugin_appliances_appliances_id` = '$instID'
                ORDER BY `itemtype`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (isMultiEntitiesMode()) {
         $pdf->setColumnsSize(12,27,25,18,18);
         $pdf->displayTitle('<b><i>'.$LANG['common'][17],
                                     $LANG['common'][16],
                                     $LANG['entity'][0],
                                     $LANG['common'][19],
                                     $LANG['common'][20].'</i></b>');
      } else {
         $pdf->setColumnsSize(25,31,22,22);
         $pdf->displayTitle('<b><i>'.$LANG['common'][17],
                                     $LANG['common'][16],
                                     $LANG['common'][19],
                                     $LANG['common'][20].'</i></b>');
      }

      if (!$number) {
         $pdf->displayLine($LANG['search'][15]);
      } else {
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
                         FROM `glpi_plugin_appliances_appliances_items`, `".$item->getTable()."`
                         LEFT JOIN `glpi_entities`
                              ON (`glpi_entities`.`id` = `".$item->getTable()."`.`entities_id`)
                         WHERE `".$item->getTable()."`.`id`
                                    = `glpi_plugin_appliances_appliances_items`.`items_id`
                               AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$type'
                               AND `glpi_plugin_appliances_appliances_items`.`plugin_appliances_appliances_id`
                                    = '$instID' ".
                               getEntitiesRestrictRequest(" AND ",$item->getTable());

               if ($item->maybeTemplate()) {
                  $query .= " AND `".$item->getTable()."`.`is_template` = '0'";
               }
               $query.=" ORDER BY `glpi_entities`.`completename`, `".$item->getTable()."`.$column";

               if ($result_linked=$DB->query($query)) {
                  if ($DB->numrows($result_linked)) {
                     while ($data = $DB->fetch_assoc($result_linked)) {
                        if (!$item->getFromDB($data["id"])) {
                           continue;
                        }
                        $ID = "";
                        if ($type == 'Ticket') {
                           $data["name"] = $LANG['job'][38]." ".$data["id"];
                        }
                        if ($type == 'KnowbaseItem') {
                           $data["name"] = $data["question"];
                        }

                        if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                           $ID = " (".$data["id"].")";
                        }
                        $name = $data["name"].$ID;

                        if (isMultiEntitiesMode()) {
                           $pdf->setColumnsSize(12,27,25,18,18);
                           $pdf->displayLine($item->getTypeName(), $name,
                                             Dropdown::getDropdownName("glpi_entities",
                                                                       $data['entities_id']),
                                             (isset($data["serial"])? $data["serial"] :"-"),
                                             (isset($data["otherserial"])?$data["otherserial"]:"-"));
                        } else {
                           $pdf->setColumnsSize(25,31,22,22);
                           $pdf->displayTitle($item->getTypeName(), $name,
                                              (isset($data["serial"])?$data["serial"]:"-"),
                                              (isset($data["otherserial"])?$data["otherserial"]:"-"));
                        }

                        PluginAppliancesRelation::showList_PDF($pdf, $this->fields["relationtype"],
                                                               $data["IDD"]);
                        PluginAppliancesOptvalue_Item::showList_PDF($pdf, $data["id"], $instID);
                     } // Each device
                  } // numrows device
               }
            } // type right
         } // each type
      } // numrows type
   }


   /**
    * Show the applicatif associated with a device
    *
    * Called from the device form (applicatif tab)
    *
    * @param $itemtype : type of the device
    * @param $withtemplate : not used, always empty
   **/
   static function showAssociated($item, $withtemplate='') {
      global $DB,$CFG_GLPI, $LANG;

      $ID       = $item->getField('id');
      $itemtype = get_Class($item);
      $canread  = $item->can($ID,'r');
      $canedit  = $item->can($ID,'w');

      $query = "SELECT `glpi_plugin_appliances_appliances_items`.`id` AS entID,
                       `glpi_plugin_appliances_appliances`.*
                FROM `glpi_plugin_appliances_appliances_items`,
                     `glpi_plugin_appliances_appliances`
                LEFT JOIN `glpi_entities`
                     ON (`glpi_entities`.`id` = `glpi_plugin_appliances_appliances`.`entities_id`)
                WHERE `glpi_plugin_appliances_appliances_items`.`items_id` = '$ID'
                      AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$itemtype'
                      AND `glpi_plugin_appliances_appliances_items`.`plugin_appliances_appliances_id`
                           = `glpi_plugin_appliances_appliances`.`id`".
                      getEntitiesRestrictRequest(" AND", "glpi_plugin_appliances_appliances",
                                                 'entities_id', $item->getEntityID(), true);
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      $query_app = "SELECT `ID`
                    FROM `glpi_plugin_appliances_appliances_items`
                    WHERE `items_id` = '$ID'";
      $result_app = $DB->query($query_app);
      $number_app = $DB->numrows($result_app);

      if ($number_app >0) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      if (isMultiEntitiesMode()) {
         $colsup += 1;
      }

      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='".(5+$colsup)."'>".$LANG['plugin_appliances'][9]." :</th></tr>";
      echo "<tr><th>".$LANG['common'][16]."</th>";
      if (isMultiEntitiesMode()) {
         echo "<th>".$LANG['entity'][0]."</th>";
      }
      echo "<th>".$LANG['common'][35]."</th>";
      echo "<th>".$LANG['common'][17]."</th>";
      if ($number_app >0) {
         echo "<th>".$LANG['plugin_appliances'][22]."</th>";
      }
      echo "<th>".$LANG['common'][25]."<br>".$LANG['plugin_appliances'][24]."</th>";

      if ($canedit) {
         if ($withtemplate <2) {
            echo "<th>&nbsp;</th>";
         }
      }
      echo "</tr>";
      $used = array();
      while ($data = $DB->fetch_array($result)) {
         $appliancesID = $data["id"];
         $used[]       = $appliancesID;

         echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";
         if ($withtemplate !=3
             && $canread
             && (in_array($data['entities_id'], $_SESSION['glpiactiveentities'])
                 || $data["is_recursive"])) {

            echo "<td class='center'><a href='".
                  $CFG_GLPI["root_doc"]."/plugins/appliances/front/appliance.form.php?id=".
                  $data["id"]."'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"]) {
               echo " (".$data["id"].")";
            }
            echo "</a></td>";
         } else {
            echo "<td class='center'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"]) {
               echo " (".$data["id"].")";
            }
            echo "</td>";
         }
         if ($_SESSION["glpiis_ids_visible"]) {
            echo " (".$data["id"].")";
         }
         echo "</b></a></td>";
         if (isMultiEntitiesMode()) {
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
                                                                 $data['entities_id'])."</td>";
         }
         echo "<td class='center'>".Dropdown::getDropdownName("glpi_groups", $data["groups_id"]).
              "</td>";
         echo "<td class='center'>".
                Dropdown::getDropdownName("glpi_plugin_appliances_appliancetypes",
                                          $data["plugin_appliances_appliancetypes_id"])."</td>";

         if ($number_app >0) {
            // add or delete a relation to an applicatifs
            echo "<td class='center'>";
            PluginAppliancesRelation::showList ($data["relationtype"], $data["entID"],
                                                $item->fields["entities_id"], $canedit);
            echo "</td>";
         }

         echo "<td class='center'>".$data["comment"];
         PluginAppliancesOptvalue_Item::showList($itemtype, $ID, $appliancesID, $canedit);
         echo "</td>";

         if ($canedit) {
            echo "<td class='center tab_bg_2'><a href='".$CFG_GLPI["root_doc"].
                  "/plugins/appliances/front/appliance.form.php?deleteappliance=1".
                  "&amp;id=".$data["entID"]."'><b>".$LANG['buttons'][6]."</b></a></td>";
         }
         echo "</tr>";
      }

      if ($canedit){
         $entities = "";
         if ($item->isRecursive()) {
            $entities = getSonsOf('glpi_entities', $item->getEntityID());
         } else {
            $entities = $item->getEntityID();
         }
         $limit = getEntitiesRestrictRequest(" AND", "glpi_plugin_appliances_appliances", '',
                                             $entities, true);

         $q = "SELECT COUNT(*)
               FROM `glpi_plugin_appliances_appliances`
               WHERE `is_deleted` = '0'
               $limit";

         $result = $DB->query($q);
         $nb = $DB->result($result,0,0);

         if ($withtemplate<2 && $nb>count($used)) {
            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' colspan=5>";

            // needed to use the button "additem"
            echo "<form method='post' action=\"".$CFG_GLPI["root_doc"].
                  "/plugins/appliances/front/appliance.form.php\">";
            echo "<input type='hidden' name='item' value='$ID'>".
                 "<input type='hidden' name='itemtype' value='$itemtype'>";
            PluginAppliancesAppliance::dropdown(array('name'   => "conID",
                                                      'entity' => $entities,
                                                      'used'   => $used));

            echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\"
                   class='submit'>";
            echo "</form>";

            echo "</td>";
            echo "<td class='right' colspan='".($colsup)."'></td>";
            echo "</tr>";
         }
      }
      echo "</table></div>";
   }


   /**
    * show for PDF the applicatif associated with a device
    *
    * @param $pdf
    * @param $item
   **/
   static function showAssociated_PDF($pdf, $item){
      global $DB, $CFG_GLPI, $LANG;

      $ID       = $item->getField('id');
      $itemtype = get_Class($item);
      $canread  = $item->can($ID,'r');
      $canedit  = $item->can($ID,'w');

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.$LANG['plugin_appliances'][9].'</b>');

      $query = "SELECT `glpi_plugin_appliances_appliances_items`.`id` AS entID,
                       `glpi_plugin_appliances_appliances`.*
                FROM `glpi_plugin_appliances_appliances_items`,
                     `glpi_plugin_appliances_appliances`
                LEFT JOIN `glpi_entities`
                     ON (`glpi_entities`.`id` = `glpi_plugin_appliances_appliances`.`entities_id`)
                WHERE `glpi_plugin_appliances_appliances_items`.`items_id` = '$ID'
                      AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$itemtype'
                      AND `glpi_plugin_appliances_appliances_items`.`plugin_appliances_appliances_id`
                           = `glpi_plugin_appliances_appliances`.`id`".
                      getEntitiesRestrictRequest(" AND", "glpi_plugin_appliances_appliances",
                                                 'entities_id', $item->getEntityID(), true);
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (!$number) {
         $pdf->displayLine($LANG['search'][15]);
      } else {
         if (isMultiEntitiesMode()) {
            $pdf->setColumnsSize(30,30,20,20);
            $pdf->displayTitle('<b><i>'.$LANG['common'][16],
                                        $LANG['entity'][0],
                                        $LANG['common'][35],
                                        $LANG['common'][17].'</i></b>');
         } else {
            $pdf->setColumnsSize(50,25,25);
            $pdf->displayTitle('<b><i>'.$LANG['common'][16],
                                        $LANG['common'][35],
                                        $LANG['common'][17].'</i></b>');
         }

         while ($data = $DB->fetch_array($result)) {
            $appliancesID = $data["id"];
            if (isMultiEntitiesMode()) {
               $pdf->setColumnsSize(30,30,20,20);
               $pdf->displayLine($data["name"],
                                 Html::clean(Dropdown::getDropdownName("glpi_entities",
                                                                       $data['entities_id'])),
                                 Html::clean(Dropdown::getDropdownName("glpi_groups",
                                                                       $data["groups_id"])),
                                 Html::clean(Dropdown::getDropdownName("glpi_plugin_appliances_appliancetypes",
                                                                       $data["plugin_appliances_appliancetypes_id"])));
            } else {
               $pdf->setColumnsSize(50,25,25);
               $pdf->displayLine($data["name"],
                                 Html::clean(Dropdown::getDropdownName("glpi_groups",
                                                                       $data["groups_id"])),
                                 Html::clean(Dropdown::getDropdownName("glpi_plugin_appliances_appliancetypes",
                                                                       $data["plugin_appliances_appliancetypes_id"])));
            }
            PluginAppliancesRelation::showList_PDF($pdf, $data["relationtype"], $data["entID"]);
            PluginAppliancesOptvalue_Item::showList_PDF($pdf, $ID, $appliancesID);
         }
      }
   }


   /**
    * Diplay a dropdown to select an Appliance
    *
    * Parameters which could be used in options array :
    *    - name : string / name of the select (default is plugin_appliances_appliances_id)
    *    - entity : integer or array / restrict to a defined entity or array of entities
    *                   (default '' : current entity)
    *    - used : array / Already used items ID: not to display in dropdown (default empty)
    *
    * @param $options possible options
    *
    * @return nothing (HTML display)
   **/
   static function dropdown($options=array()) {
      global $DB, $CFG_GLPI;

      // Defautl values
      $p['name']     = 'plugin_appliances_appliances_id';
      $p['entity']   = '';
      $p['used']     = array();

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key]=$val;
         }
      }

      $rand = mt_rand();

      $where =" WHERE `glpi_plugin_appliances_appliances`.`is_deleted` = '0' ".
                      getEntitiesRestrictRequest("AND", "glpi_plugin_appliances_appliances", '',
                                                 $p['entity'], true);

      if (count($p['used'])) {
         $where .= " AND `id` NOT IN ('".implode("','", $p['used'])."')";
      }

      $query = "SELECT *
                FROM `glpi_plugin_appliances_appliancetypes`
                WHERE `id` IN (SELECT DISTINCT `plugin_appliances_appliancetypes_id`
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
                      'entity_restrict' => $p['entity'],
                      'rand'            => $rand,
                      'myname'          => $p['name'],
                      'used'            => $p['used']);

      ajaxUpdateItemOnSelectEvent("type_appliances", "show_".$p['name'].$rand,
                                  $CFG_GLPI["root_doc"].
                                    "/plugins/appliances/ajax/dropdownTypeAppliances.php",
                                  $params);

      echo "<span id='show_".$p['name']."$rand'>";
      $_POST["entity_restrict"] = $p['entity'];
      $_POST["type_appliances"] = 0;
      $_POST["myname"]          = $p['name'];
      $_POST["rand"]            = $rand;
      $_POST["used"]            = $p['used'];
      include (GLPI_ROOT."/plugins/appliances/ajax/dropdownTypeAppliances.php");
      echo "</span>\n";

      return $rand;
   }


   /**
    * Type than could be linked to a Appliance
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      static $types = array('Computer', 'Monitor', 'NetworkEquipment', 'Peripheral', 'Phone',
                            'Printer', 'Software');
      // temporary disabled TRACKING_TYPE,

      $plugin = new Plugin();
      if ($plugin->isActivated("racks")) {
         $types[] = 'PluginRacksRack';
      }

      if ($all) {
         return $types;
      }

      // Only allowed types
      foreach ($types as $key=>$type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }


   static function methodTestAppliance($params, $protocol) {
      global $PLUGIN_HOOKS;

      if (isset ($params['help'])) {
         return array('help' => 'bool,optional');
      }

      $resp = array('glpi' => GLPI_VERSION);

      $plugin = new Plugin();
      foreach ($PLUGIN_HOOKS['webservices'] as $name => $fct) {
         if ($plugin->getFromDBbyDir($name)) {
            $resp[$name] = $plugin->fields['version'];
         }
      }

      return $resp;
   }


   static function methodListAppliances($params, $protocol) {
      global $DB, $CFG_GLPI;

      // TODO add some search options (name, type, ...)

      if (isset ($params['help'])) {
         return array(  'help'      => 'bool,optional',
                        'id2name'   => 'bool,optional',
                        'count'     => 'bool,optional',
                        'start'     => 'integer,optional',
                        'limit'     => 'integer,optional' );
      }

      if (!getLoginUserID()) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $resp  = array ();
      $start = 0;
      if (isset ($params['start']) && is_numeric($params['start'])) {
         $start = $params['start'];
      }

      $limit = $CFG_GLPI["list_limit_max"];
      if (isset ($params['limit']) && is_numeric($params['limit'])) {
         $limit = $params['limit'];
      }

      $orders = array();
      if (isset ($params['order'])) {
         if (is_array($params['order'])) {
            $tab = $params['order'];
         } else {
            $tab = array($params['order']=>'DESC');
         }

         foreach ($tab as $key => $val) {
            if ($val != 'ASC') {
               $val = 'DESC';
            }

            //TODO A revoir
            if (in_array($key, array('date_mod', 'entities_id', 'externalid', 'groups_id', 'id',
                                     'name', 'users_id'))) {
               $orders[] ="`$key` $val";
            } else {
               return PluginWebservicesMethodCommon::Error($protocol,
                                                           WEBSERVICES_ERROR_BADPARAMETER, '',
                                                           'order=$key');
            }
         }
      }

      if (count($orders)) {
         $order = implode(',',$orders);
      } else {
         $order = "`name` DESC";
      }

      $where = getEntitiesRestrictRequest(' WHERE', 'glpi_plugin_appliances_appliances');

      if (isset ($params['count'])) {
         $query = "SELECT COUNT(DISTINCT `id`) AS count
                   FROM `glpi_plugin_appliances_appliances`
                   $where";

         foreach ($DB->request($query) as $data) {
            $resp = $data;
         }

      } else {
         $where = "";
         if (isset ($params['id2name'])) {
            // TODO : users_name and groups_name ?
            $query = "SELECT `glpi_plugin_appliances_appliances`.*,
                             `glpi_plugin_appliances_appliancetypes`.`name` AS plugin_appliances_appliancetypes_name,
                             `glpi_plugin_appliances_environments`.`name` AS plugin_appliances_environments_name
                      FROM `glpi_plugin_appliances_appliances`
                      LEFT JOIN `glpi_plugin_appliances_appliancetypes`
                           ON `glpi_plugin_appliances_appliancetypes`.`id`
                                 =`glpi_plugin_appliances_appliances`.`plugin_appliances_appliancetypes_id`
                      LEFT JOIN `glpi_plugin_appliances_environments`
                           ON `glpi_plugin_appliances_environments`.`id`
                                 =`glpi_plugin_appliances_appliances`.`plugin_appliances_environments_id`
                      ORDER BY $order
                      LIMIT $start,$limit";

         } else {
            // TODO review list of fields (should probably be minimal, or configurable)
            $query = "SELECT `glpi_plugin_appliances_appliances`.*
                      FROM `glpi_plugin_appliances_appliances`
                      ORDER BY $order
                      LIMIT $start,$limit";
         }

         foreach ($DB->request($query) as $data) {
            $resp[] = $data;
         }
      }
      return $resp;
   }


   static function methodDeleteAppliance($params, $protocol) {
      global $DB;

      if (isset ($params['help'])) {
         return array('help'  => 'bool,optional',
                      'force' => 'boolean,optional',
                      'id'    => 'string' );
      }

      if (!getLoginUserID()) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset ($params['id'])) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER);
      }

      $force = 0;
      if (isset($params['force'])){
         $force = 1;
      }

      $id        = $params['id'];
      $appliance = new self();
      if (!$appliance->can($id, 'd')) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      if (!$appliance->delete(array("id" => $id),$force)) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_FAILED);
      }

      return array("id" => $id);
   }


   static function methodUpdateAppliance($params, $protocol) {
      global $DB;

      // TODO : add more fields + factorize field translation with methodAddAppliance

      if (isset ($params['help'])) {
         return array('help'                                  => 'bool,optional',
                      'is_helpdesk_visible'                   => 'bool,optional',
                      'is_recursive'                          => 'bool,optional',
                      'name'                                  => 'string,optional',
                      'plugin_appliances_appliancetypes_id'   => 'integer,optional',
                      'plugin_appliances_appliancetypes_name' => 'string,optional',
                      'externalid'                            => 'string,optional',
                      'id'                                    => 'string');
      }

      if (!getLoginUserID()) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset($params['id']) || !is_numeric($params['id'])) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER);
      }

      if (isset($params['is_helpdesk_visible']) && !is_numeric($params['is_helpdesk_visible'])) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                                                     'is_helpdesk_visible');
      }

      if (isset($params['is_recursive']) && !is_numeric($params['is_recursive'])) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                                                     'is_recursive');
      }

      $id        = intval($params['id']);
      $appliance = new self();
      if (!$appliance->can($id, 'w')) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      $input = array('id' => $id);
      if (isset($params['name'])) {
         $input['name'] = addslashes($params['name']);
      }

      if (isset($params['externalid'])) {
         if (empty($params['externalid'])) {
            $input['externalid'] = 'NULL';
         } else {
            $input['externalid'] = addslashes($params['externalid']);
         }
      }

      // Old field name for compatibility
      if (isset($params['notes'])) {
         $input['notepad'] = addslashes($params['notes']);
      }
      foreach (array('comment', 'notepad', 'serial', 'otherserial') as $field) {
         if (isset($params[$field])) {
            $input[$field] = addslashes($params[$field]);
         }
      }

      if (isset($params['is_helpdesk_visible'])) {
         $input['is_helpdesk_visible'] = ($params['is_helpdesk_visible'] ? 1 : 0);
      }

      if (isset($params['is_recursive'])) {
         $input['is_recursive'] = ($params['is_recursive'] ? 1 : 0);
      }

      if (isset($params['plugin_appliances_appliancetypes_name'])) {
         $type   = new PluginAppliancesApplianceType();
         $input2 = array();
         $input2['entities_id']  = (isset($input['entities_id'])? $input['entities_id']
                                                                : $appliance->fields['entities_id']);
         $input2['is_recursive'] = (isset($input['is_recursive'])? $input['is_recursive']
                                                                 : $appliance->fields['entities_id']);
         $input2['name']         = addslashes($params['plugin_appliances_appliancetypes_name']);
         $input['plugin_appliances_appliancetypes_id'] = $type->import($input2);

      } else if (isset($params['plugin_appliances_appliancetypes_id'])) {
         $input['plugin_appliances_appliancetypes_id']
                     = intval($params['plugin_appliances_appliancetypes_id']);
      }

      if ($appliance->update($input)) {
         // Does not detect unicity error on externalid :(
         return $appliance->methodGetAppliance(array('id' => $id), $protocol);
      }

      return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_FAILED);
   }


   static function methodAddAppliance($params, $protocol) {
      global $DB;

      // TODO : add more fields
      if (isset ($params['help'])) {
         return array('help'                                  => 'bool,optional',
                      'name'                                  => 'string',
                      'entities_id'                           => 'integer,optional',
                      'is_helpdesk_visible'                   => 'bool,optional',
                      'is_recursive'                          => 'bool,optional',
                      'comment'                               => 'string,optional',
                      'externalid'                            => 'string,optional',
                      'plugin_appliances_appliancetypes_id'   => 'integer,optional',
                      'plugin_appliances_appliancetypes_name' => 'string,optional');
      }

      if (!getLoginUserID()) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset($params['name'])) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER);
      }

      if (isset($params['is_helpdesk_visible']) && !is_numeric($params['is_helpdesk_visible'])) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                                                     'is_helpdesk_visible');
      }

      if (isset($params['is_recursive']) && !is_numeric($params['is_recursive'])) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                                                     'is_recursive');
      }
      $input = array();
      $input['name'] = addslashes($params['name']);

      if (isset($params['entities_id'])) {
         $input['entities_id'] = intval($params['entities_id']);
      } else {
         $input['entities_id'] = $_SESSION["glpiactive_entity"];
      }

      if (isset($params['is_recursive'])) {
         // TODO check if canUnrecurs
         $input['is_recursive'] = ($params['is_recursive'] ? 1 : 0);
      }

      if (isset($params['externalid']) && !empty($params['externalid'])) {
         $input['externalid'] = addslashes($params['externalid']);
      }

      if (isset($params['plugin_appliances_appliancetypes_name'])) {
         $type   = new PluginAppliancesApplianceType();
         $input2 = array();
         $input2['entities_id']  = $input['entities_id'];
         $input2['is_recursive'] = $input['is_recursive'];
         $input2['name']         = addslashes($params['plugin_appliances_appliancetypes_name']);
         $input['plugin_appliances_appliancetypes_id'] = $type->import($input2);

      } else if (isset($params['plugin_appliances_appliancetypes_id'])) {
         // TODO check if this id exists and is readable and is available in appliance entity
         $input['plugin_appliances_appliancetypes_id']
                  = intval($params['plugin_appliances_appliancetypes_id']);
      }

      if (isset($params['is_helpdesk_visible'])) {
         $input['is_helpdesk_visible'] = ($params['is_helpdesk_visible'] ? 1 : 0);
      }

      // Old field name for compatibility
      if (isset($params['notes'])) {
         $input['notepad'] = addslashes($params['notes']);
      }
      foreach (array('comment', 'notepad', 'serial', 'otherserial') as $field) {
         if (isset($params[$field])) {
            $input[$field] = addslashes($params[$field]);
         }
      }

      $appliance = new self();
      if (!$appliance->can(-1, 'w', $input)) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      $id = $appliance->add($input);
      if ($id) {
         // Return the newly created object
         return $appliance->methodGetAppliance(array('id'=>$id), $protocol);
      }

      return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_FAILED);
   }


   static function methodGetAppliance($params, $protocol) {
      global $DB;

      if (isset ($params['help'])) {
         return array(  'help'               => 'bool,optional',
                        'id2name'            => 'bool,optional',
                        'externalid OR id'   => 'string' );
      }

      if (!getLoginUserID()) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset($params['externalid']) && !isset($params['id'])) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER);
      }

      $appli = new self();
      $found = false;

      if (isset($params['id'])) {
         $found = $appli->getFromDB(intval($params['id']));

      } else if (isset($params['externalid'])){
         $found = $appli->getFromDBbyExternalID(addslashes($params["externalid"]));
      }

      if (!$found || !$appli->can($appli->fields["id"],'r')) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }
      $resp = $appli->fields;

      if (isset($params['id2name'])) {
         $resp['plugin_appliances_appliancetypes_name']
            = Html::clean(Dropdown::getDropdownName('glpi_plugin_appliances_appliancetypes',
                                                    $resp['plugin_appliances_appliancetypes_id']));
         $resp['plugin_appliances_environments_name']
            = Html::clean(Dropdown::getDropdownName('glpi_plugin_appliances_environments',
                                                    $resp['plugin_appliances_environments_id']));
         $resp['users_name']
            = Html::clean(Dropdown::getDropdownName('glpi_users', $resp['users_id']));
         $resp['groups_name']
            = Html::clean(Dropdown::getDropdownName('glpi_groups', $resp['groups_id']));
      }
      return $resp;
   }
}

?>