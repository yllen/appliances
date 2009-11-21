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
   public $type             = PLUGIN_APPLIANCES_TYPE;
   public $entity_assign    = true;
   public $may_be_recursive = true;
   public $dohistory        = true;


   function plugin_appliances_getSearchOption() {
      global $LANG;

      $tab = array();

      if (plugin_appliances_haveRight("appliance","r")) {
         $tab['common'] = $LANG['appliances']['title'][1];

         $tab[1]['table']     = 'glpi_plugin_appliances_appliances';
         $tab[1]['field']     = 'name';
         $tab[1]['linkfield'] = 'name';
         $tab[1]['name']      = $LANG['plugin_appliances'][8];
         $tab[1]['datatype']  = 'itemlink';

         $tab[2]['table']     = 'glpi_plugin_appliances_appliancetypes';
         $tab[2]['field']     = 'name';
         $tab[2]['linkfield'] = 'appliancetypes_id';
         $tab[2]['name']      = $LANG['plugin_appliances'][20];

         $tab[3]['table']     = 'glpi_locations';
         $tab[3]['field']     = 'completename';
         $tab[3]['linkfield'] = 'locations_id';
         $tab[3]['name']      = $LANG['plugin_appliances'][2];

         $tab[4]['table']     = 'glpi_plugin_appliances_appliances';
         $tab[4]['field']     =  'comment';
         $tab[4]['linkfield'] =  'comment';
         $tab[4]['name']      =  $LANG['plugin_appliances'][12];
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
         $tab[10]['linkfield'] = 'environments_id';
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

         $tab[60]['table']        = 'glpi_tracking';
         $tab[60]['field']        = 'count';
         $tab[60]['linkfield']    = '';
         $tab[60]['name']         = $LANG['stats'][13];
         $tab[60]['forcegroupby'] = true;
         $tab[60]['usehaving']    = true;
         $tab[60]['datatype']     = 'number';

         $tab['contract'] = $LANG['Menu'][25];

         $tab[29]['table']        = 'glpi_contracts';
         $tab['field']            = 'name';
         $tab['linkfield']        = '';
         $tab[29]['name']         = $LANG['common'][16]." ".$LANG['financial'][1];
         $tab[29]['forcegroupby'] = true;

         $tab[30]['table']        = 'glpi_contracts';
         $tab[30]['field']        = 'num';
         $tab[30]['linkfield']    = '';
         $tab[30]['name']         = $LANG['financial'][4]." ".$LANG['financial'][1];
         $tab[30]['forcegroupby'] = true;

         $tab[130]['table']        = 'glpi_contracts';
         $tab[130]['field']        = 'duration';
         $tab[130]['linkfield']    = '';
         $tab[130]['name']         = $LANG['financial'][8]." ".$LANG['financial'][1];
         $tab[130]['forcegroupby'] = true;

         $tab[131]['table']        = 'glpi_contracts';
         $tab[131]['field']        = 'periodicity';
         $tab[131]['linkfield']    = '';
         $tab[131]['name']         = $LANG['financial'][69];
         $tab[131]['forcegroupby'] = true;

         $tab[132]['table']         = 'glpi_contracts';
         $tab[132]['field']         = 'begin_date';
         $tab[132]['linkfield']     = '';
         $tab[132]['name']          = $LANG['search'][8]." ".$LANG['financial'][1];
         $tab[132]['forcegroupby']  = true;
         $tab[132]['datatype']      = 'date';

         $tab[133]['table']         = 'glpi_contracts';
         $tab[133]['field']         = 'accounting_number';
         $tab[133]['linkfield']     = '';
         $tab[133]['name']          = $LANG['financial'][13]." ".$LANG['financial'][1];
         $tab[133]['forcegroupby']  = true;

         $tab[134]['table']         = 'glpi_contracts';
         $tab[134]['field']         = 'end_date';
         $tab[134]['linkfield']     = '';
         $tab[134]['name']          = $LANG['search'][9]." ".$LANG['financial'][1];
         $tab[134]['forcegroupby']  = true;
         $tab[134]['datatype']      = 'date_delay';
         $tab[134]['datafields'][1] = 'begin_date';
         $tab[134]['datafields'][2] = 'duration';

         $tab[135]['table']         = 'glpi_contracts';
         $tab[135]['field']         = 'notice';
         $tab[135]['linkfield']     = '';
         $tab[135]['name']          = $LANG['financial'][10]." ".$LANG['financial'][1];
         $tab[135]['forcegroupby']  = true;

         $tab[136]['table']         = 'glpi_contracts';
         $tab[136]['field']         = 'cost';
         $tab[136]['linkfield']     = '';
         $tab[136]['name']          = $LANG['financial'][5]." ".$LANG['financial'][1];
         $tab[136]['forcegroupby']  = true;

         $tab[137]['table']         = 'glpi_contracts';
         $tab[137]['field']         = 'billing';
         $tab[137]['linkfield']     = '';
         $tab[137]['name']          = $LANG['financial'][11]." ".$LANG['financial'][1];
         $tab[137]['forcegroupby']  = true;

         $tab[138]['table']         = 'glpi_contracts';
         $tab[138]['field']         = 'renewal';
         $tab[138]['linkfield']     = '';
         $tab[138]['name']          = $LANG['financial'][107]." ".$LANG['financial'][1];
         $tab[138]['forcegroupby']  = true;
   }
   return $tab;
}


   function cleanDBonPurge($ID) {
      global $DB;

      foreach ($DB->request("glpi_plugin_appliances_appliances_items",array("appliances_id"=>$ID)) as $data) {
         plugin_appliances_deleteItem($data["id"]);
      }

      $query = "DELETE
                FROM `glpi_documents_items`
                WHERE `items_id` = '$ID'
                      AND ìtemtype`= '".PLUGIN_APPLIANCES_TYPE."' ";
      $DB->query($query);

      $query = "DELETE
                FROM `glpi_plugin_appliances_optvalues`
                WHERE `appliances_id` = '$ID'";
      $DB->query($query);

      $query = "DELETE
                FROM `glpi_contract_items`
                WHERE `items_id` = '$ID'
                      AND `itemtype` = '".PLUGIN_APPLIANCES_TYPE."'";
      $DB->query($query);

      $query = "DELETE
                FROM `glpi_infocoms`
                WHERE `items_id` = '$ID'
                      AND `itemtype` = '".PLUGIN_APPLIANCES_TYPE."')";
      $result = $DB->query($query);
   }


   function cleanItems($ID,$type) {
      global $DB;

      $query = "DELETE
                FROM `glpi_plugin_appliances_appliances_items`
                WHERE `items_id` = '$ID'
                      AND `itemtype` = '$type'";
      $DB->query($query);
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
      dropdownValue("glpi_plugin_appliances_appliancetypes", "appliancetypes_id",
                    $this->fields["appliancetypes_id"],1,
                    $this->fields["entities_id"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][10]."&nbsp;:</td><td>";
      if ($canedit) {
         dropdownAllUsers("users_id", $this->fields["users_id"],1,$this->fields["entities_id"]);
      } else {
         echo getUsername($this->fields["users_id"]);
      }
      echo "</td>";
      echo "<td>".$LANG['plugin_appliances'][3]."&nbsp;:</td><td>";
      dropdownValue("glpi_plugin_appliances_environments", "environments_id", $this->fields["environments_id"]);
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
          && !($ID && countElementsInTable("glpi_plugin_appliances_relations,
                                            glpi_plugin_appliances_appliances_items",
                                           "glpi_plugin_appliances_relations.appliances_items_id
                                                =glpi_plugin_appliances_appliances_items.id
                                             AND glpi_plugin_appliances_appliances_items.appliances_id
                                                   =$ID"))) {
         plugin_appliances_relationtypes("relationtypes_id",$this->fields["relationtypes_id"]);
      } else {
         echo plugin_appliances_getrelationtypename($this->fields["relationtypes_id"]);
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
                WHERE `appliances_id` = '$instID'
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
      if ($this->fields["relationtypes_id"]) {
         echo "<th>".$LANG['plugin_appliances'][22].
               "<br>".$LANG['plugin_appliances'][24]."</th>";
      }
      echo "<th>".$LANG['common'][19]."</th>";
      echo "<th>".$LANG['common'][20]."</th>";
      echo "</tr>";

      $ci = new CommonItem();
      while ($i < $number) {
         $type = $DB->result($result, $i, "itemtype");
         if (haveTypeRight($type,"r")) {
            $column = "name";
            if ($type == TRACKING_TYPE) {
               $column = "id";
            }
            if ($type == KNOWBASE_TYPE) {
               $column = "question";
            }

            $query = "SELECT ".$LINK_ID_TABLE[$type].".*,
                             `glpi_plugin_appliances_appliances_items`.`id` AS IDD,
                             `glpi_entities`.`id` AS entity
                      FROM `glpi_plugin_appliances_appliances_items`, ".$LINK_ID_TABLE[$type]."
                      LEFT JOIN `glpi_entities`
                           ON (`glpi_entities`.`id` = ".$LINK_ID_TABLE[$type].".`entities_id`)
                      WHERE ".$LINK_ID_TABLE[$type].".`id` = `glpi_plugin_appliances_appliances_items`.`items_id`
                            AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$type'
                            AND `glpi_plugin_appliances_appliances_items`.`appliances_id` = '$instID' ".
                            getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',
                                                       isset($CFG_GLPI["recursive_type"][$type]));

            if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])) {
               $query .= " AND ".$LINK_ID_TABLE[$type].".`is_template` = '0'";
            }
            $query.=" ORDER BY `glpi_entities`.`completename`, ".$LINK_ID_TABLE[$type].".$column";

            if ($result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {
                  initNavigateListItems($type,$LANG['plugin_appliances']['title'][1]." = ".
                                              $this->fields['name']);

                  while ($data = $DB->fetch_assoc($result_linked)) {
                     $ci->getFromDB($type,$data["id"]);
                     addToNavigateListItems($type,$data["id"]);
                     $ID = "";
                     if ($type == TRACKING_TYPE) {
                        $data["name"] = $LANG['job'][38]." ".$data["id"];
                     }
                     if ($type == KNOWBASE_TYPE) {
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
                     echo "<td class='center'>".$ci->getType()."</td>";
                     echo "<td class='center' ".
                           (isset($data['deleted']) && $data['deleted']?"class='tab_bg_2_2'":"").">".
                           $name."</td>";
                     if (isMultiEntitiesMode()) {
                        echo "<td class='center'>".getDropdownName("glpi_entities",$data['entity']).
                              "</td>";
                     }

                     if ($this->fields["relationtypes_id"]) {
                        echo "<td class='center'>".
                           plugin_appliances_getrelationtypename($this->fields["relationtypes_id"]).
                           "&nbsp;:&nbsp;";
                        $this->showRelation($this->fields["relationtypes_id"],
                                                       $data["IDD"], $ci->obj->fields["entities_id"],
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
         $i++;
      }

      if ($canedit) {
         echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";

         echo "<input type='hidden' name='conID' value='$instID'>";
         dropdownAllItems("item",0,0,
                          ($this->fields['is_recursive']?-1:$this->fields['entities_id']),
                          plugin_appliances_getTypes());
         echo "</td>";
         echo "<td colspan='3' class='center' class='tab_bg_2'>";
         echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
         echo "</td></tr>";
         echo "</table></div>" ;

         openArrowMassive("appliances_form$rand", true);
         closeArrowMassive('deleteitem', $LANG['buttons'][6]);

      } else {
         echo "</table></div>";
      }
      echo "</form>";
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
    */ //(CommonDBTM $item,$withtemplate='')
    // $ID = $item->getField('id') au lieu de $ci= new...
    // $ci->obj deviendra $item
   static function showAssociated($itemtype,$ID,$withtemplate='') {
      global $DB,$CFG_GLPI, $LANG;

      $ci = new CommonItem();
      $ci->getFromDB($itemtype,$ID);
      $canread = $ci->obj->can($ID,'r');
      $canedit = $ci->obj->can($ID,'w');

      $query = "SELECT `glpi_plugin_appliances_appliances_items`.`id` AS entID,
                       `glpi_plugin_appliances_appliances`.*
                FROM `glpi_plugin_appliances_appliances_items`,
                     `glpi_plugin_appliances_appliances`
                LEFT JOIN `glpi_entities`
                     ON (`glpi_entities`.`id` = `glpi_plugin_appliances_appliances`.`entities_id`)
                WHERE `glpi_plugin_appliances_appliances_items`.`items_id` = '$ID'
                      AND `glpi_plugin_appliances_appliances_items`.`itemtype` = '$itemtype'
                      AND `glpi_plugin_appliances_appliances_items`.`appliances_id`
                           = `glpi_plugin_appliances_appliances`.`id`".
                      getEntitiesRestrictRequest(" AND ","glpi_plugin_appliances_appliances",'','',
                                                isset($CFG_GLPI["recursive_type"][PLUGIN_APPLIANCES_TYPE]));
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
                                                    $data["appliancetypes_id"]).
               "</td>";

         if ($number_app >0) {
            // add or delete a relation to an applicatifs
            echo "<td class='center'>";
            // PluginAppliancesAppliance::showRelation = self::showRelation
            self::showRelation ($data["relationtypes_id"], $data["entID"],
                                            $ci->obj->fields["entities_id"],$canedit);
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
         $ci = new CommonItem();
         $entities = "";
         if ($ci->getFromDB($itemtype,$ID) && isset($ci->obj->fields["entities_id"])) {
            if (isset($ci->obj->fields["is_recursive"]) && $ci->obj->fields["is_recursive"]) {
               $entities = getEntitySons($ci->obj->fields["entities_id"]);
            } else {
               $entities = $ci->obj->fields["entities_id"];
            }
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
            plugin_appliances_dropdownappliances("conID",$entities,$used);

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
    * Show the relation for a device/applicatif
    *
    * Called from PluginAppliancesAppliance->showItem and PluginAppliancesAppliance::showAssociated
    *
    * @param $drelation_type : type of the relation
    * @param $relID ID of the relation
    * @param $entity, ID of the entity of the device
    * @param $canedit, if user is allowed to edit the relation
    *    - canedit the device if called from the device form
    *    - must be false if called from the applicatif form
    *
    */
   static private function showRelation ($relationtype, $relID, $entity, $canedit) {
      global $DB,$CFG_GLPI, $LANG;

      if (!$relationtype) {
         return false;
      }

      // selects all the attached relations
      $tablename = plugin_appliances_getrelationtypetable($relationtype);
      $title = plugin_appliances_getrelationtypename($relationtype);

      if (in_array($tablename,$CFG_GLPI["dropdowntree_tables"])) {
         $sql_loc = "SELECT `glpi_plugin_appliances_relations`.`id`,
                            `completename` AS dispname ";
      } else {
         $sql_loc = "SELECT `glpi_plugin_appliances_relations`.`id`,
                            `name` AS dispname ";
      }
      $sql_loc .= "FROM `".$tablename."` ,
                        `glpi_plugin_appliances_relations`,
                        `glpi_plugin_appliances_appliances_items`
                        WHERE `".$tablename."`.`id` = `glpi_plugin_appliances_relations`.`relations_id`
                              AND `glpi_plugin_appliances_relations`.`appliances_items_id`
                                    = `glpi_plugin_appliances_appliances_items`.`id`
                              AND `glpi_plugin_appliances_appliances_items`.`id` = '$relID'";

      $result_loc = $DB->query($sql_loc);
      $number_loc = $DB->numrows($result_loc);

      if ($canedit) {
         echo "<form method='post' name='relation' action='".
               $CFG_GLPI["root_doc"]."/plugins/appliances/front/appliance.form.php'>";
         echo "<br><input type='hidden' name='deviceID' value='$relID'>";

         $i = 0;
         $itemlist = "";
         $used = array();

         if ($number_loc >0) {
            echo "<table>";
            while ($i < $number_loc) {
               $res = $DB->fetch_array($result_loc);
               echo "<tr><td class=top>";
               // when the value of the checkbox is changed, the corresponding hidden variable value
               // is also changed by javascript
               echo "<input type='checkbox' name='itemrelation[" . $res["id"] . "]' value='1'></td><td>";
               echo $res["dispname"];
               echo "</td></tr>";
               $i++;
            }
            echo "</table>";
            echo "<input type='submit' name='dellieu' value='".$LANG['buttons'][6]."' class='submit'><br><br>";
         }

         echo "$title&nbsp;:&nbsp;";

         dropdownValue($tablename,"tablekey[" . $relID . "]","",1,$entity,"",$used);
         echo "&nbsp;&nbsp;&nbsp;<input type='submit' name='addlieu' value=\"".
               $LANG['buttons'][8]."\" class='submit'><br>&nbsp;";
         echo "</form>";
      } else if ($number_loc > 0) {
         while ($res = $DB->fetch_array($result_loc)) {
            echo $res["dispname"]."<br>";
         }
      } else {
         echo "&nbsp;";
      }
   }

}

?>