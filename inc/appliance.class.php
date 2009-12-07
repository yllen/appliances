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

class PluginAppliancesAppliance extends CommonDBTM {

   // From CommonDBTM
   public $table            = 'glpi_plugin_appliances_appliances';
   public $type             = 'PluginAppliancesAppliance';
   public $entity_assign    = true;
   public $may_be_recursive = true;
   public $dohistory        = true;


   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_appliances']['title'][1];
   }

   function getSearchOptions() {
      global $LANG;

      $tab = array();

      $tab['common'] = $LANG['plugin_appliances']['title'][1];

      $tab[1]['table']     = 'glpi_plugin_appliances_appliances';
      $tab[1]['field']     = 'name';
      $tab[1]['linkfield'] = 'name';
      $tab[1]['name']      = $LANG['common'][16];
      $tab[1]['datatype']  = 'itemlink';

      $tab[2]['table']     = 'glpi_plugin_appliances_appliancetypes';
      $tab[2]['field']     = 'name';
      $tab[2]['linkfield'] = 'plugin_appliances_appliancetypes_id';
      $tab[2]['name']      = $LANG['common'][17];

      $tab[3]['table']     = 'glpi_locations';
      $tab[3]['field']     = 'completename';
      $tab[3]['linkfield'] = 'locations_id';
      $tab[3]['name']      = $LANG['plugin_appliances'][2];

      $tab[4]['table']     = 'glpi_plugin_appliances_appliances';
      $tab[4]['field']     =  'comment';
      $tab[4]['linkfield'] =  'comment';
      $tab[4]['name']      =  $LANG['common'][25];
      $tab[4]['datatype']  =  'text';

      $tab[5]['table']        = 'glpi_plugin_appliances_appliances_items';
      $tab[5]['field']        = 'items_id';
      $tab[5]['linkfield']    = '';
      $tab[5]['name']         = $LANG['plugin_appliances'][7];
      $tab[5]['forcegroupby'] =  true;

      $tab[6]['table']     = 'glpi_users';
      $tab[6]['field']     = 'name';
      $tab[6]['linkfield'] = 'users_id';
      $tab[6]['name']      = $LANG['plugin_appliances'][21];

      $tab[7]['table']     = 'glpi_plugin_appliances_appliances';
      $tab[7]['field']     = 'is_recursive';
      $tab[7]['linkfield'] = '';
      $tab[7]['name']      = $LANG['entity'][9];
      $tab[7]['datatype']  = 'bool';

      $tab[8]['table']     = 'glpi_groups';
      $tab[8]['field']     = 'name';
      $tab[8]['linkfield'] = 'groups_id';
      $tab[8]['name']      = $LANG['common'][35];

      $tab[9]['table']     = 'glpi_plugin_appliances_appliances';
      $tab[9]['field']     = 'date_mod';
      $tab[9]['linkfield'] = 'date_mod';
      $tab[9]['name']      = $LANG['common'][26];
      $tab[9]['datatype']  = 'datetime';

      $tab[10]['table']     = 'glpi_plugin_appliances_environments';
      $tab[10]['field']     = 'name';
      $tab[10]['linkfield'] = 'plugin_appliances_environments_id';
      $tab[10]['name']      = $LANG['plugin_appliances'][3];

      $tab[11]['table']     = 'glpi_plugin_appliances_appliances';
      $tab[11]['field']     = 'is_helpdesk_visible';
      $tab[11]['linkfield'] = 'is_helpdesk_visible';
      $tab[11]['name']      = $LANG['software'][46];
      $tab[11]['datatype']  = 'bool';

      $tab[30]['table']     = 'glpi_plugin_appliances_appliances';
      $tab[30]['field']     = 'id';
      $tab[30]['linkfield'] = '';
      $tab[30]['name']      = $LANG['common'][2];

      $tab[80]['table']     = 'glpi_entities';
      $tab[80]['field']     = 'completename';
      $tab[80]['linkfield'] = 'entities_id';
      $tab[80]['name']      = $LANG['entity'][0];

      $tab['tracking'] = $LANG['title'][24];

      $tab[60]['table']        = 'glpi_tickets';
      $tab[60]['field']        = 'count';
      $tab[60]['linkfield']    = '';
      $tab[60]['name']         = $LANG['stats'][13];
      $tab[60]['forcegroupby'] = true;
      $tab[60]['usehaving']    = true;
      $tab[60]['datatype']     = 'number';

   return $tab;
   }


   function cleanDBonPurge($ID) {

      $temp = new PluginAppliancesAppliance_Item();
      $temp->clean(array('plugin_appliances_appliances_id' => $ID));

      $temp = new PluginAppliancesOptvalue();
      $temp->clean(array('plugin_appliances_appliances_id' => $ID));
   }

   function defineTabs($ID,$withtemplate) {
      global $LANG;

      $ong[1] = $LANG['title'][26];
      if ($ID > 0) {
         $ong[2] = $LANG['plugin_appliances'][24];
         if (haveRight("show_all_ticket","1")) {
            $ong[6] = $LANG['title'][28];
         }
         if (haveRight("contract","r") || haveRight("infocom","r")) {
            $ong[9]=$LANG['Menu'][26];
         }
         if (haveRight("document","r")) {
            $ong[10] = $LANG['Menu'][27];
         }
         if (haveRight("notes","r")) {
            $ong[11] = $LANG['title'][37];
         }
         $ong[12] = $LANG['title'][38];
      }
      return $ong;
   }

   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   function getSelectLinkedItem () {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_appliances_appliances_items`
              WHERE `plugin_appliances_appliances_id`='" . $this->fields['id']."'";
   }

   function showForm ($target,$ID,$withtemplate='') {
      global $CFG_GLPI, $LANG;

      if ($ID>0) {
         $this->check($ID,'r');
      } else {
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $canedit = $this->can($ID,'w');
      $canrecu = $this->can($ID,'recursive');

      $this->showTabs($ID, $withtemplate,getActiveTab($this->type));
      $this->showFormHeader($target,$ID,'',2);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][16]."&nbsp;:</td>";
      echo "<td>";
      autocompletionTextField("name","glpi_plugin_appliances_appliances","name",
                              $this->fields["name"], 34, $this->fields["entities_id"]);
      echo "</td><td>".$LANG['common'][17]."&nbsp;:</td><td>";
      dropdownValue("glpi_plugin_appliances_appliancetypes", "plugin_appliances_appliancetypes_id",
                    $this->fields["plugin_appliances_appliancetypes_id"],1,
                    $this->fields["entities_id"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][10]."&nbsp;:</td><td>";
      if ($canedit) {
         User::dropdownAllUsers("users_id", $this->fields["users_id"],1,$this->fields["entities_id"]);
      } else {
         echo getUsername($this->fields["users_id"]);
      }
      echo "</td>";
      echo "<td>".$LANG['plugin_appliances'][3]."&nbsp;:</td><td>";
      dropdownValue("glpi_plugin_appliances_environments", "plugin_appliances_environments_id",
                    $this->fields["plugin_appliances_environments_id"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][35]."&nbsp;:</td><td>";
      if ($canedit) {
         dropdownValue("glpi_groups", "groups_id", $this->fields["groups_id"],1,
                       $this->fields["entities_id"]);
      } else {
         echo getdropdownname("glpi_groups", $this->fields["groups_id"]);
      }
      echo "</td>";
      echo "<td>" . $LANG['software'][46] . "&nbsp;:</td><td>";
      dropdownYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][15]."&nbsp;:</td><td>";
      if ($canedit) {
         dropdownValue("glpi_locations", "location", $this->fields["locations_id"],1,
                       $this->fields["entities_id"]);
      } else {
         echo getdropdownname("glpi_locations",$this->fields["locations_id"]);
      }
      echo "</td>";
      echo "<td rowspan='3'>".$LANG['common'][25]."&nbsp;:</td>";
      echo "<td rowspan='3' class='middle'>";
      echo "<textarea cols='45' rows='5' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      // dropdown relationtype added
      echo "<td>".$LANG['plugin_appliances'][22]."&nbsp;:</td><td>";
      if ($canedit
          && !($ID && countElementsInTable
                       ("glpi_plugin_appliances_relations,
                         glpi_plugin_appliances_appliances_items",
                        "glpi_plugin_appliances_relations.plugin_appliances_appliances_items_id
                                 = glpi_plugin_appliances_appliances_items.id
                         AND glpi_plugin_appliances_appliances_items.plugin_appliances_appliances_id
                                 = $ID"))) {
         PluginAppliancesRelation::dropdownType("relationtype",$this->fields["relationtype"]);
      } else {
         echo PluginAppliancesRelation::getTypeName($this->fields["relationtype"]);
         $rand = mt_rand();
         $comment = $LANG['common'][84];
         $image = "/pics/lock.png";
         echo "&nbsp;<img alt='' src='".$CFG_GLPI["root_doc"].$image.
               "' onmouseout=\"cleanhide('comment_relationtypes$rand')\" ".
               " onmouseover=\"cleandisplay('comment_relationtypes$rand')\">";
         echo "<span class='over_link' id='comment_relationtypes$rand'>$comment</span>";
      }
      echo "</td></tr>";

      $datestring = $LANG['common'][26].": ";
      $date = convDateTime($this->fields["date_mod"]);
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' class='center'>".$datestring.$date;
      echo "</td></tr>";

      $this->showFormButtons($ID,$withtemplate,2);
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

      return true;
   }

   /**
    * Show for PDF the current applicatif
    *
    * @param $pdf object for the output
    */
   function show_PDF ($pdf) {
      global $LANG, $DB;

      $pdf->setColumnsSize(50,50);
      $col1 = '<b>'.$LANG["common"][2].' '.$this->fields['id'].'</b>';
      if (isset($this->fields["date_mod"])) {
         $col2 = $LANG["common"][26].' : '.convDateTime($this->fields["date_mod"]);
      } else {
         $col2 = '';
      }
      $pdf->displayTitle($col1, $col2);

      $pdf->displayLine(
         '<b><i>'.$LANG["common"][16].' :</i></b> '.$this->fields['name'],
         '<b><i>'.$LANG['common'][17].' :</i></b> '.
            html_clean(getDropdownName('glpi_plugin_appliances_appliancetypes',
                                       $this->fields['plugin_appliances_appliancetypes_id'])));
      $pdf->displayLine(
         '<b><i>'.$LANG["common"][10].' :</i></b> '.getUserName($this->fields['users_id']),
         '<b><i>'.$LANG["common"][35].' :</i></b> '.
            html_clean(getDropdownName('glpi_groups',$this->fields['groups_id'])));

      $pdf->displayLine(
         '<b><i>'.$LANG["common"][15].' :</i></b> '.
            html_clean(getDropdownName('glpi_locations',$this->fields['locations_id'])),
         '<b><i>'.$LANG['plugin_appliances'][22].' :</i></b> '.
            html_clean(PluginAppliancesRelation::getTypeName($this->fields["relationtype"])));

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
      $pdf->displayLine("<b><i>".$LANG['plugin_appliances'][24].": </i></b>".implode(', ',$opts));

      $pdf->displayText('<b><i>'.$LANG["common"][25].' :</i></b>', $this->fields['comment']);

      $pdf->displaySpace();
   }

   /**
    * Show the Device associated with an applicatif
    *
    * Called from the applicatif form
    *
    */
   function showItem() {
      global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE;

      $instID = $this->fields['id'];

      if (!$this->can($instID,"r")) {
         return false;
      }
      $rand = mt_rand();

      $canedit = $this->can($instID,'w');

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
      if ($this->fields["relationtype"]) {
         echo "<th>".$LANG['plugin_appliances'][22].
               "<br>".$LANG['plugin_appliances'][24]."</th>";
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

            $query = "SELECT `".$item->table."`.*,
                             `glpi_plugin_appliances_appliances_items`.`id` AS IDD,
                             `glpi_entities`.`id` AS entity
                      FROM `glpi_plugin_appliances_appliances_items`, ".$LINK_ID_TABLE[$type]."
                      LEFT JOIN `glpi_entities`
                           ON (`glpi_entities`.`id` = `".$item->table."`.`entities_id`)
                      WHERE `".$item->table."`.`id`
                                 = `glpi_plugin_appliances_appliances_items`.`items_id`
                            AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$type'
                            AND `glpi_plugin_appliances_appliances_items`.`plugin_appliances_appliances_id`
                                 = '$instID' ".
                            getEntitiesRestrictRequest(" AND ", $item->table);

            if (in_array($item->table,$CFG_GLPI["template_tables"])) {
               $query .= " AND `".$item->table."`.`is_template` = '0'";
            }
            $query.=" ORDER BY `glpi_entities`.`completename`, `".$item->table."`.$column";

            if ($result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {
                  initNavigateListItems($type,$LANG['plugin_appliances']['title'][1]." = ".
                                              $this->fields['name']);

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
                     $name= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type].
                              "?id=".$data["id"]."\">".$data["name"]."$ID</a>";

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
                        echo "<td class='center'>".getDropdownName("glpi_entities",$data['entity']).
                              "</td>";
                     }

                     if ($this->fields["relationtype"]) {
                        echo "<td class='center'>".
                           PluginAppliancesRelation::getTypeName($this->fields["relationtype"]).
                           "&nbsp;:&nbsp;";
                        PluginAppliancesRelation::showList($this->fields["relationtype"],
                                                           $data["IDD"], $item->fields["entities_id"],
                                                           false);
                        PluginAppliancesOptvalue_Item::showList($type, $data["id"], $instID, false);
                        echo "</td>";
                     }

                     echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-").
                           "</td>";
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
         dropdownAllItems("item",0,0,
                          ($this->fields['is_recursive']?-1:$this->fields['entities_id']),
                          $this->getTypes());
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

   function showItem_PDF($pdf) {
      global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE;

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

               $query = "SELECT `".$item->table."`.*,
                                `glpi_plugin_appliances_appliances_items`.`id` AS IDD,
                                `glpi_entities`.`id` AS entity
                         FROM `glpi_plugin_appliances_appliances_items`, `".$item->table."`
                         LEFT JOIN `glpi_entities`
                              ON (`glpi_entities`.`id` = `".$item->table."`.`entities_id`)
                         WHERE `".$item->table."`.`id`
                                    = `glpi_plugin_appliances_appliances_items`.`items_id`
                               AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$type'
                               AND `glpi_plugin_appliances_appliances_items`.`plugin_appliances_appliances_id`
                                    = '$instID' ".
                               getEntitiesRestrictRequest(" AND ",$item->table);

               if (in_array($item->table,$CFG_GLPI["template_tables"])) {
                  $query .= " AND `".$item->table."`.`is_template` = '0'";
               }
               $query.=" ORDER BY `glpi_entities`.`completename`, `".$item->table."`.$column";

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
                           $pdf->displayLine(
                                       $item->getTypeName(),
                                       $name,
                                       getDropdownName("glpi_entities",$data['entities_id']),
                                       (isset($data["serial"])? "".$data["serial"]."" :"-"),
                                       (isset($data["otherserial"])? "".$data["otherserial"]."" :"-"));
                        } else {
                           $pdf->setColumnsSize(25,31,22,22);
                           $pdf->displayTitle(
                                       $item->getTypeName(),
                                       $name,
                                       (isset($data["serial"])? "".$data["serial"]."" :"-"),
                                       (isset($data["otherserial"])? "".$data["otherserial"]."" :"-"));
                        }

                        PluginAppliancesRelation::showList_PDF($pdf,
                                                               $this->fields["relationtype"],
                                                               $data["IDD"]);
                        PluginAppliancesOptvalue_Item::showList_PDF($pdf,$data["id"], $instID);
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
    * @param $ID of the device
    * @param $withtemplate : not used, always empty
    *
    **/
   static function showAssociated($itemtype,$ID,$withtemplate='') {
      global $DB,$CFG_GLPI, $LANG;

      $item = new $itemtype();
      $canread = $item->can($ID,'r');
      $canedit = $item->can($ID,'w');

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
                      getEntitiesRestrictRequest(" AND ","glpi_plugin_appliances_appliances",
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
         $used[] = $appliancesID;

         echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";
         if ($withtemplate !=3
             && $canread
             && (in_array($data['entities_id'],
                          $_SESSION['glpiactiveentities']) || $data["is_recursive"])) {

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
            echo "<td class='center'>".getDropdownName("glpi_entities",$data['entities_id'])."</td>";
         }
         echo "<td class='center'>".getdropdownname("glpi_groups",$data["groups_id"])."</td>";
         echo "<td class='center'>".getdropdownname("glpi_plugin_appliances_appliancetypes",
                                                    $data["plugin_appliances_appliancetypes_id"]).
               "</td>";

         if ($number_app >0) {
            // add or delete a relation to an applicatifs
            echo "<td class='center'>";
            PluginAppliancesRelation::showList ($data["relationtype"], $data["entID"],
                                                $item->fields["entities_id"],$canedit);
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
            $entities = getSonsOf('glpi_entities',$item->getEntityID());
         } else {
            $entities = $item->getEntityID();
         }
         $limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_appliances_appliances",'',$entities,
                                             true);

         $q = "SELECT count(*)
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
            PluginAppliancesAppliance::dropdown("conID",$entities,$used);

            echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
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
    * @param $ID of the device
    * @param $itemtype : type of the device
    *
    */
   static function showAssociated_PDF($pdf, $ID, $itemtype){
      global $DB,$CFG_GLPI, $LANG;

      $item = new $itemtype();
      if (!$item->can($ID,'r')) {
         return false;
      }

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
                      getEntitiesRestrictRequest(" AND ","glpi_plugin_appliances_appliances",
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
                                 html_clean(getDropdownName("glpi_entities",$data['entities_id'])),
                                 html_clean(getDropdownName("glpi_groups",$data["groups_id"])),
                                 html_clean(getDropdownName("glpi_plugin_appliances_appliancetypes",
                                                   $data["plugin_appliances_appliancetypes_id"])));
            } else {
               $pdf->setColumnsSize(50,25,25);
               $pdf->displayLine($data["name"],
                                 html_clean(getDropdownName("glpi_groups",$data["groups_id"])),
                                 html_clean(getDropdownName("glpi_plugin_appliances_appliancetypes",
                                                      $data["plugin_appliances_appliancetypes_id"])));
            }
            PluginAppliancesRelation::showList_PDF($pdf,$data["relationtype"], $data["entID"]);
            PluginAppliancesOptvalue_Item::showList_PDF($pdf,$ID, $appliancesID);
         }
      }
   }

   /**
    * Diplay a dropdown to select an Appliance
    *
    * @param $myname string name of the dropdown
    * @param $entity_restrict
    * @param $used array of value to exclude
    *
    * @return nothing (HTML display)
    */
   static function dropdown($myname, $entity_restrict='', $used=array()) {
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

   /**
    * Type than could be linked to a Appliance
    *
    * @return array of types
    */
   static function getTypes () {

      static $types = array('Computer', 'Printer', 'Monitor', 'Peripheral', 'NetworkEquipment',
                            'Phone', 'Software');
      // temporary disabled TRACKING_TYPE,

      $plugin = new Plugin();
      if ($plugin->isActivated("rack")) {
         $types[] = 'PluginRacksRack';
      }
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

}

?>