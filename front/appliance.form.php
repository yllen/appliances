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

include ("../../../inc/includes.php");

Plugin::load('appliances',true);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$PluginAppliances = new PluginAppliancesAppliance();
$PluginItem       = new PluginAppliancesAppliance_Item();

if (isset($_POST["add"])) {
   $PluginAppliances->check(-1, 'w', $_POST);
   $newID = $PluginAppliances->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {
   $PluginAppliances->check($_POST['id'], 'w');
   $PluginAppliances->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $PluginAppliances->check($_POST['id'], 'w');
   $PluginAppliances->delete($_POST);
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/appliances/front/appliance.php");

} else if (isset($_POST["restore"])) {
   $PluginAppliances->check($_POST['id'],'w');
   $PluginAppliances->restore($_POST);
   Html::back();

} else if (isset($_POST["purge"])) {
   $PluginAppliances->check($_POST['id'], 'w');
   $PluginAppliances->delete($_POST, 1);

   Html::redirect($CFG_GLPI["root_doc"]."/plugins/appliances/front/appliance.php");

// delete a relation
} else if (isset($_POST["dellieu"])) {
   $relation = new PluginAppliancesRelation();
   if (isset($_POST['itemrelation'])) {
      foreach($_POST["itemrelation"] as $key => $val) {
         $relation->delete(array('id' => $key));
      }
   }
   Html::back();

// add a relation
} else if (isset($_POST["addlieu"])) {
   $relation = new PluginAppliancesRelation();
   if ($_POST['tablekey'] >0) {
      foreach($_POST["tablekey"] as $key => $val) {
         if ($val > 0) {
            $relation->add(array('plugin_appliances_appliances_items_id' => $key,
                                 'relations_id'                          => $val));
         }
      }
   }
   Html::back();

} else if (isset($_POST['update_optvalues'])) {
   $PluginAppliances->check($_POST['plugin_appliances_appliances_id'], 'w');

   $Optvalue = new PluginAppliancesOptvalue();
   $Optvalue->updateList($_POST);
   Html::back();

} else if (isset($_POST["add_opt_val"])){
   $PluginAppliances->check($_POST['plugin_appliances_appliances_id'], 'r');
   $item = new $_POST['itemtype']();
   $item->check($_POST['items_id'], 'w');

   $OptvalueItem = new PluginAppliancesOptvalue_Item();
   $OptvalueItem->updateList($_POST);
   Html::back();

} else if (isset($_POST["additem"])) {
   if ($_POST['itemtype']
       && ($_POST['item'] > 0)) {
      $input = array('plugin_appliances_appliances_id' => $_POST['conID'],
                     'items_id'                        => $_POST['item'],
                     'itemtype'                        => $_POST['itemtype']);

      $PluginItem->check(-1, 'w', $input);
      $newID = $PluginItem->add($input);
   }
   Html::back();

} else if (isset($_POST["deleteitem"])){
   foreach ($_POST["item"] as $key => $val) {
      $input = array('id' => $key);
      if ($val == 1) {
         $PluginItem->check($key, 'w');
         $PluginItem->delete($input);
      }
   }
   Html::back();

} else if (isset($_POST["deleteappliance"])) {
   $input = array('id' => $_POST["id"]);
   $PluginItem->check($_POST["id"], 'w');
   $PluginItem->delete($input);
   Html::back();

} else {
   $PluginAppliances->checkGlobal('r');
   if (!isset($_SESSION['glpi_tab'])) {
      $_SESSION['glpi_tab'] = 1;
   }
   if (isset($_GET['onglet'])) {
      $_SESSION['glpi_tab'] = $_GET['onglet'];
   }

   $plugin = new Plugin();
   if ($plugin->isActivated("environment")) {
      Html::header(_n('Appliance', 'Appliances', 2, 'appliances'), $_SERVER['PHP_SELF'], "plugins",
                   "environment", "appliances");
   } else {
      Html::header(_n('Appliance', 'Appliances', 2, 'appliances'), $_SERVER["PHP_SELF"], "plugins",
                   "appliances");
   }
   $PluginAppliances->showForm($_GET["id"]);

   Html::footer();
}
?>