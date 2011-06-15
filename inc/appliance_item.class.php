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
}

?>