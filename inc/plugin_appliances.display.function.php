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



function plugin_appliances_showDevice_PDF($pdf, $instID) {
   global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE;

   if (!plugin_appliances_haveRight("appliance","r")) {
      return false;
   }

   $PluginAppliances = new PluginAppliancesAppliance();
   if (!$PluginAppliances->getFromDB($instID)) {
      return false;
   }
   $pdf->setColumnsSize(100);
   $pdf->displayTitle('<b>'.$LANG['plugin_appliances'][7].'</b>');

   $query = "SELECT DISTINCT `itemtype`
             FROM `glpi_plugin_appliances_appliances_items`
             WHERE `appliances_id` = '$instID'
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

   $ci = new CommonItem();
   if (!$number) {
      $pdf->displayLine($LANG['search'][15]);
   } else {
      for ($i=0 ; $i < $number ; $i++) {
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

            if ($result_linked=$DB->query($query)) {
               if ($DB->numrows($result_linked)) {
                  while ($data = $DB->fetch_assoc($result_linked)) {
                     if (!$ci->getFromDB($type,$data["id"])) {
                        continue;
                     }
                     $ID = "";
                     if ($type == TRACKING_TYPE) {
                        $data["name"] = $LANG['job'][38]." ".$data["id"];
                     }
                     if ($type == KNOWBASE_TYPE) {
                        $data["name"] = $data["question"];
                     }

                     if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                        $ID = " (".$data["id"].")";
                     }
                     $name = $data["name"].$ID;

                     if (isMultiEntitiesMode()) {
                        $pdf->setColumnsSize(12,27,25,18,18);
                        $pdf->displayLine(
                                    $ci->getType(),
                                    $name,
                                    getDropdownName("glpi_entities",$data['entities_id']),
                                    (isset($data["serial"])? "".$data["serial"]."" :"-"),
                                    (isset($data["otherserial"])? "".$data["otherserial"]."" :"-"));
                     } else {
                        $pdf->setColumnsSize(25,31,22,22);
                        $pdf->displayTitle(
                                    $ci->getType(),
                                    $name,
                                    (isset($data["serial"])? "".$data["serial"]."" :"-"),
                                    (isset($data["otherserial"])? "".$data["otherserial"]."" :"-"));
                     }

                     plugin_appliances_showRelation_PDF($pdf,
                                                        $PluginAppliances->fields["relationtypes_id"],
                                                        $data["IDD"]);
                     plugin_appliances_showOptions_PDF($pdf,$data["id"], $instID);
                  } // Each device
               } // numrows device
            }
         } // type right
      } // each type
   } // numrows type
}


/**
 * show for PDF the applicatif associated with a device
 *
 * @param $ID of the device
 * @param $itemtype : type of the device
 *
 */
function plugin_appliances_showAssociated_PDF($pdf, $ID, $itemtype){
   global $DB,$CFG_GLPI, $LANG;

   $pdf->setColumnsSize(100);
   $pdf->displayTitle('<b>'.$LANG['plugin_appliances'][9].'</b>');

   $ci = new CommonItem();
   $ci->getFromDB($itemtype,$ID);

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

   if (!$number) {
      $pdf->displayLine($LANG['search'][15]);
   } else {
      if (isMultiEntitiesMode()) {
         $pdf->setColumnsSize(30,30,20,20);
         $pdf->displayTitle('<b><i>'.$LANG['plugin_appliANCEs'][8],
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
                                                         $data["type"])));
         } else {
            $pdf->setColumnsSize(50,25,25);
            $pdf->displayLine($data["name"],
                              html_clean(getDropdownName("glpi_groups",$data["groups_id"])),
                              html_clean(getDropdownName("glpi_plugin_appliances_appliancetypes",
                                                         $data["type"])));
         }
         plugin_appliances_showRelation_PDF($pdf,$data["relationtypes"], $data["entID"]);
         plugin_appliances_showOptions_PDF($pdf,$ID, $appliancesID);
      }
   }
}





/**
 * Show for PDF the relation for a device/applicatif
 *
 * @param $pdf object for the output
 * @param $drelation_type : type of the relation
 * @param $relID ID of the relation
 *
 */
function plugin_appliances_showRelation_PDF ($pdf, $relationtype, $relID) {
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

   $opts = array();
   while ($res = $DB->fetch_array($result_loc)) {
      $opts[] = $res["dispname"];
   }
   $pdf->setColumnsSize(100);
   $pdf->displayLine("<b><i>".$LANG['plugin_appliances'][22]." :</i> $title :</b> ".
                      implode(', ',$opts));

}


/**
 * Show for PDF the optional value for a device / applicatif
 *
 * @param $pdf object for the output
 * @param $ID of the relation
 * @param $appliancesID, ID of the applicatif
 */
function plugin_appliances_showOptions_PDF ($pdf, $ID, $appliancesID) {
   global $DB, $CFG_GLPI, $LANG;

   $query_app_opt = "SELECT `id`, `champ`, `ddefault`
                     FROM `glpi_plugin_appliances_optvalues`
                     WHERE `appliances_id` = '$appliancesID'
                     ORDER BY `vvalues`";

   $result_app_opt = $DB->query($query_app_opt);
   $number_champs = $DB->numrows($result_app_opt);

   if (!$number_champs) {
      return;
   }

   $opts = array();
   for ($i=1 ; $i<=$number_champs ; $i++) {
      if ($data_opt = $DB->fetch_array($result_app_opt)) {
         $query_val = "SELECT `vvalue`
                       FROM `glpi_plugin_appliances_optvalues_items`
                       WHERE `optvalues_id` = '".$data_opt["id"]."'
                             AND `itesm_id` = '$ID'";

         $result_val = $DB->query($query_val);
         $data_val = $DB->fetch_array($result_val);
         $vvalue = ($data_val ? $data_val['vvalue'] : "");
         if (empty($vvalue) && !empty($data_opt['ddefault'])) {
            $vvalue = $data_opt['ddefault'];
         }
         $opts[] = $data_opt['champ'].($vvalue?"=".$vvalue:'');
      }
   } // For

   $pdf->setColumnsSize(100);
   $pdf->displayLine("<b><i>".$LANG['plugin_appliances'][24]." : </i></b>".implode(', ',$opts));
}


/**
 * Show for PDF an applicatif
 *
 * @param $pdf object for the output
 * @param $ID of the applicatif
 */
function plugin_appliances_main_PDF ($pdf, $ID) {
   global $LANG, $DB;

   $item = new PluginAppliancesAppliance();
   if (!$item->getFromDB($ID)) {
      return false;
   }

   $pdf->setColumnsSize(50,50);
   $col1 = '<b>'.$LANG["common"][2].' '.$item->fields['id'].'</b>';
   if (isset($item->fields["date_mod"])) {
      $col2 = $LANG["common"][26].' : '.convDateTime($item->fields["date_mod"]);
   } else {
      $col2 = '';
   }
   $pdf->displayTitle($col1, $col2);

   $pdf->displayLine(
      '<b><i>'.$LANG["common"][16].' :</i></b> '.$item->fields['name'],
      '<b><i>'.$LANG['common'][17].' :</i></b> '.
         html_clean(getDropdownName('glpi_plugin_appliances_appliancetypes',
                                    $item->fields['type'])));
   $pdf->displayLine(
      '<b><i>'.$LANG["common"][10].' :</i></b> '.getUserName($item->fields['users_id']),
      '<b><i>'.$LANG["common"][35].' :</i></b> '.
         html_clean(getDropdownName('glpi_groups',$item->fields['groups_id'])));

   $pdf->displayLine(
      '<b><i>'.$LANG["common"][15].' :</i></b> '.
         html_clean(getDropdownName('glpi_locations',$item->fields['locations_id'])),
      '<b><i>'.$LANG['plugin_appliances'][22].' :</i></b> '.
         html_clean(plugin_appliances_getrelationtypename($item->fields["relationtypes_id"])));

   $query_app = "SELECT `champ`, `ddefault`
                 FROM `glpi_plugin_appliances_optvalues`
                 WHERE `appliances_id` = '$ID'
                 ORDER BY `vvalues`";
   $result_app = $DB->query($query_app);

   $opts = array();
   while ($data = $DB->fetch_array($result_app)) {
      $opts[] = $data["champ"].($data["ddefault"] ? '='.$data["ddefault"] : '');
   }
   $pdf->setColumnsSize(100);
   $pdf->displayLine("<b><i>".$LANG['plugin_appliances'][24].": </i></b>".implode(', ',$opts));

   $pdf->displayText('<b><i>'.$LANG["common"][25].' :</i></b>', $item->fields['comment']);

   $pdf->displaySpace();
}

?>