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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

/// PluginAppliancesApplianceInjection class
class PluginAppliancesApplianceInjection extends PluginAppliancesAppliance
   implements PluginDatainjectionInjectionInterface {


   function __construct() {
      //Needed for getSearchOptions !
      $this->table = getTableForItemType('PluginAppliancesAppliance');
   }


   function isPrimaryType() {
      return true;
   }


   function connectedTo() {
      return array();
   }


   function getOptions($primary_type = '') {

      $tab = Search::getOptions(get_parent_class($this));

      //Specific to location
      //$tab[3]['linkfield'] = 'locations_id';
      //$blacklist = PluginDatainjectionCommonInjectionLib::getBlacklistedOptions();
      //Remove some options because some fields cannot be imported
      $notimportable = array(5, 9, 29, 30, 31, 50, 53, 56, 57, 58, 59, 60, 80, 91, 92,
                             122, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140);
      $options['ignore_fields'] = $notimportable;
      $options['displaytype']   = array("dropdown"       => array(2,32,3,8,49,10),
                                        "user"           => array(6,24),
                                        "multiline_text" => array(4),
                                        "date"           => array(9),
                                        "bool"           => array(11,7));

      $tab = PluginDatainjectionCommonInjectionLib::addToSearchOptions($tab, $options, $this);

      return $tab;
   }


   /**
    * Standard method to delete an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param fields fields to add into glpi
    * @param options options used during creation
   **/
   function deleteObject($values=array(), $options=array()) {

      $lib = new PluginDatainjectionCommonInjectionLib($this,$values,$options);
      $lib->deleteObject();
      return $lib->getInjectionResults();
   }


   /**
    * Standard method to add an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param values fields to add into glpi
    * @param options options used during creation
    *
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
   **/
   function addOrUpdateObject($values=array(), $options=array()) {
      global $LANG;

      $lib = new PluginDatainjectionCommonInjectionLib($this,$values,$options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }

}
?>