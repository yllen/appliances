<?php
/*
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
 @copyright Copyright (c) 2021-2022 Appliances plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/appliances
 @since     version 2.0
 --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}
toolbox::logError("post", $_POST);
$appliance = new Appliance();
$appItem   = new Appliance_Item();
$relation  = new Appliance_Item_Relation();

// delete a relation
if (isset($_POST["dellieu"])) {
   if (isset($_POST['itemrelation'])) {
      foreach($_POST["itemrelation"] as $key => $val) {
         $relation->delete(['id' => $key]);
      }
   }
   Html::back();

// add a relation
} else if (isset($_POST["addlieu"])) {
   if ($_POST['tablekey'] >0) {
      foreach($_POST["tablekey"] as $key => $val) {
         if ($val > 0) {
            $relation->add(['appliances_items_id'   => $key,
                            'relations_id'          => $val]);
         }
      }
   }
   Html::back();

} else if (isset($_POST['update_optvalues'])) {
   $appliance->check($_POST['id'], UPDATE);
   $Optvalue = new PluginAppliancesOptvalue();
   $Optvalue->updateList($_POST);
   Html::back();

} else if (isset($_POST["add_opt_val"])){
   $appliance->check($_POST['appliances_id'], READ);
   $item = new $_POST['itemtype']();
   $item->check($_POST['items_id'], UPDATE);

   $OptvalueItem = new PluginAppliancesOptvalue_Item();
   $OptvalueItem->updateList($_POST);
   Html::back();

} else {
   $appliance->checkGlobal(READ);

   //check environment meta-plugin installtion for change header
   $plugin = new Plugin();
   if ($plugin->isActivated("environment")) {
      Html::header(Appliance_Item_Relation::getTypes(true),
                     '',"assets","pluginenvironmentdisplay","appliances");
   }

   Html::footer();
}
