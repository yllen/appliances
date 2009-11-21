<?php
/*
   ----------------------------------------------------------------------
   GLPI - financialnaire Libre de Parc Informatique
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

$NEEDED_ITEMS = array('computer', 'contract', 'document', 'group', 'infocom', 'monitor',
                      'networking', 'peripheral', 'phone', 'printer', 'software', 'supplier',
                      'tracking','user');

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

useplugin('appliances',true);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$PluginAppliances = new PluginAppliancesAppliance();
$PluginItem = new PluginAppliancesAppliance_Item();

if (isset($_POST["add"])) {
   $PluginAppliances->check(-1,'w',$_POST);
   $newID = $PluginAppliances->add($_POST);
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["update"])) {
   $PluginAppliances->check($_POST['id'],'w');
   $PluginAppliances->update($_POST);
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["delete"])) {
   $PluginAppliances->check($_POST['id'],'w');
   $PluginAppliances->delete($_POST);
   glpi_header($CFG_GLPI["root_doc"]."/plugins/appliances/index.php");

} else if (isset($_POST["restore"])) {
   $PluginAppliances->check($_POST['id'],'w');
   $PluginAppliances->restore($_POST);
   glpi_header($CFG_GLPI["root_doc"]."/plugins/appliances/index.php");

} else if (isset($_POST["purge"])) {
   $PluginAppliances->check($_POST['id'],'w');
   $PluginAppliances->delete($_POST,1);

   glpi_header($CFG_GLPI["root_doc"]."/plugins/appliances/index.php");

// delete a relation
} else if (isset($_POST["dellieu"])) {
   if (isset($_POST['itemrelation'])) {
      foreach($_POST["itemrelation"] as $key => $val) {
         plugin_appliances_delRelation($key);
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);

// add a relation
} else if (isset($_POST["addlieu"])) {
   if ($_POST['tablekey'] >0) {
      foreach($_POST["tablekey"] as $key => $val) {
         if ($val > 0) {
            plugin_appliances_addRelation($key,$val);
         }
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST['update_optvalues'])) {
   $PluginAppliances->check($_POST['appliances_id'],'w');

   $Optvalue = new PluginAppliancesOptvalue();
   $Optvalue->updateList($_POST);
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["add_opt_val"])){
   $PluginAppliances->check($_POST['appliances_id'],'r');
   $temp = new Commonitem();
   $temp->setType($_POST['itemtype'], true);
   $temp->obj->check($_POST['itemtype'],'w');

   $OptvalueItem = new PluginAppliancesOptvalue_Item();
   $OptvalueItem->updateList($_POST);
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["additem"])) {
   if ($_POST['itemtype'] >0 && $_POST['item'] >0) {
      $input = array('appliances_id' => $_POST['conID'],
                     'items_id'      => $_POST['item'],
                     'itemtype'      => $_POST['itemtype']);

      $PluginItem->check(-1,'w',$input);
      $newID = $PluginItem->add($input);
   }
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["deleteitem"])){
   foreach ($_POST["item"] as $key => $val) {
      $input = array('id' => $key);
      if ($val == 1) {
         $PluginItem->check($key,'w');
         $PluginItem->delete($input);
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_GET["deleteappliance"])) {
   $input = array('id' => $_GET["id"]);
   $PluginItem->check($_GET["id"],'w');
   $PluginItem->delete($input);
   glpi_header($_SERVER['HTTP_REFERER']);

} else {
   plugin_appliances_checkRight("appliance","r");
   if (!isset($_SESSION['glpi_tab'])) {
      $_SESSION['glpi_tab'] = 1;
   }
   if (isset($_GET['onglet'])) {
      $_SESSION['glpi_tab'] = $_GET['onglet'];
   }

   $plugin = new Plugin();
   if ($plugin->isActivated("environment")) {
      commonHeader($LANG['plugin_appliances']['title'][1],$_SERVER['PHP_SELF'],"plugins",
                   "environment","appliances");
   } else {
      commonHeader($LANG['plugin_appliances']['title'][1],$_SERVER["PHP_SELF"],"plugins","appliances");
   }
   $PluginAppliances->showForm($_SERVER["PHP_SELF"],$_GET["id"]);

   commonFooter();
}

?>