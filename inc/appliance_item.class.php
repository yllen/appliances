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


class PluginAppliancesAppliance_Item extends CommonDBRelation {

   // From CommonDBTM
   public $table = 'glpi_plugin_appliances_appliances_items';
   public $type  = 'PluginAppliancesAppliance_Item';

   // From CommonDBRelation
   public $itemtype_1 = 'PluginAppliancesAppliance';
   public $items_id_1 = 'plugin_appliances_appliances_id';

   public $itemtype_2 = 'itemtype';
   public $items_id_2 = 'items_id';

   /**
    * Clean object veryfing criteria (when a relation is deleted)
    *
    * @param $crit array of criteria (should be an index)
    */
   public function clean ($crit) {
      global $DB;

      foreach ($DB->request($this->table, $crit) as $data) {
         $this->delete($data);
      }
   }

   function cleanDBonPurge($ID) {
      $temp = new PluginAppliancesOptvalue_Item();
      $temp->clean(array('itemtype' => $this->fields['itemtype'],
                         'items_id' => $this->fields['items_id']));

      $temp = new PluginAppliancesRelation();
      $temp->clean(array('plugin_appliances_appliances_items_id' => $ID));
   }
}

?>