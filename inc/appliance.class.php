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
 @copyright Copyright (c) 2009-2022 Appliances plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/appliances
 @since     version 2.0
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


/**
 * Class PluginAppliancesAppliance
**/
class PluginAppliancesAppliance extends CommonDBTM {



   /**
    * @param $params
    * @param $protocol
    *
    * @return array
   **/
   static function methodTestAppliance($params, $protocol) {
      global $PLUGIN_HOOKS;

      if (isset ($params['help'])) {
         return ['help' => 'bool,optional'];
      }

      $resp = ['glpi' => GLPI_VERSION];

      $plugin = new Plugin();
      foreach ($PLUGIN_HOOKS['webservices'] as $name => $fct) {
         if ($plugin->getFromDBbyDir($name)) {
            $resp[$name] = $plugin->fields['version'];
         }
      }

      return $resp;
   }


   /**
    * @param $params
    * @param $protocol
    *
    * @return array
   **/
   static function methodListAppliances($params, $protocol) {
      global $DB, $CFG_GLPI;

      // TODO add some search options (name, type, ...)

      if (isset ($params['help'])) {
         return ['help'      => 'bool,optional',
                 'id2name'   => 'bool,optional',
                 'count'     => 'bool,optional',
                 'start'     => 'integer,optional',
                 'limit'     => 'integer,optional'];
      }

      $dbu = new DbUtils();

      if (!Session::getLoginUserID()) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $resp  = [];
      $start = 0;
      if (isset($params['start']) && is_numeric($params['start'])) {
         $start = $params['start'];
      }

      $limit = $CFG_GLPI["list_limit_max"];
      if (isset($params['limit']) && is_numeric($params['limit'])) {
         $limit = $params['limit'];
      }

      $orders = [];
      if (isset($params['order'])) {
         if (is_array($params['order'])) {
            $tab = $params['order'];
         } else {
            $tab = [$params['order'] => 'DESC'];
         }

         foreach ($tab as $key => $val) {
            if ($val != 'ASC') {
               $val = 'DESC';
            }

            //TODO A revoir
            if (in_array($key, ['date_mod', 'entities_id', 'externalid', 'groups_id', 'id',
                                'name', 'users_id'])) {
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

      if (isset ($params['count'])) {
         $query = ['SELECT' => ['COUNT' => 'id AS count'],
                   'FROM'   => 'glpi_appliances'
                   + getEntitiesRestrictCriteria('glpi_appliances')];

         foreach ($DB->request($query) as $data) {
            $resp = $data;
         }

      } else {
         if (isset ($params['id2name'])) {
            // TODO : users_name and groups_name ?
            $query = "SELECT `glpi_appliances`.*,
                             `glpi_appliancetypes`.`name` AS appliancetypes_name,
                             `glpi_applianceenvironments`.`name` AS environments_name
                      FROM `glpi_appliances`
                      LEFT JOIN `glpi_appliancetypes`
                        ON `glpi_appliancetypes`.`id` =`glpi_appliances`.`appliancetypes_id`
                      LEFT JOIN `glpi_applianceenvironments`
                        ON `glpi_applianceenvironments`.`id` =`glpi_appliances`.`environments_id`
                      ORDER BY ".$order."
                      LIMIT ".$start.", ".$limit;

         } else {
            // TODO review list of fields (should probably be minimal, or configurable)
            $query = "SELECT `glpi_appliances`.*
                      FROM `glpi_appliances`
                      ORDER BY ".$order."
                      LIMIT ".$start.", ".$limit;
         }

         foreach ($DB->request($query) as $data) {
            $resp[] = $data;
         }
      }
      return $resp;
   }


   /**
    * @param $params
    * @param $protocol
    *
    * @return array
    **/
    static function methodDeleteAppliance($params, $protocol) {

      if (isset ($params['help'])) {
         return ['help'  => 'bool,optional',
                 'force' => 'boolean,optional',
                 'id'    => 'string'];
      }

      if (!Session::getLoginUserID()) {
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
      $appliance = new Appliance();
      if (!$appliance->can($id, 'd')) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      if (!$appliance->delete(["id" => $id], $force)) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_FAILED);
      }

      return ["id" => $id];
   }


   /**
    * @param $params
    * @param $protocol
    *
    * @return array
    **/
    static function methodUpdateAppliance($params, $protocol) {

      // TODO : add more fields + factorize field translation with methodAddAppliance

      if (isset ($params['help'])) {
         return ['help'                                  => 'bool,optional',
                 'is_helpdesk_visible'                   => 'bool,optional',
                 'is_recursive'                          => 'bool,optional',
                 'name'                                  => 'string,optional',
                 'plugin_appliances_appliancetypes_id'   => 'integer,optional',
                 'plugin_appliances_appliancetypes_name' => 'string,optional',
                 'externalid'                            => 'string,optional',
                 'id'                                    => 'string'];
      }

      if (!Session::getLoginUserID()) {
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
      $appliance = new Appliance();
      if (!$appliance->can($id, UPDATE)) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      $input = ['id' => $id];
      if (isset($params['name'])) {
         $input['name'] = addslashes($params['name']);
      }

      if (isset($params['externalid'])) {
         if (empty($params['externalid'])) {
            $input['externalidentifier'] = 'NULL';
         } else {
            $input['externalidentifier'] = addslashes($params['externalid']);
         }
      }

      // Old field name for compatibility
      if (isset($params['notes'])) {
         $input['notepad'] = addslashes($params['notes']);
      }
      foreach (['comment', 'notepad', 'serial', 'otherserial'] as $field) {
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
         $type   = new ApplianceType();
         $input2 = [];
         $input2['entities_id']  = (isset($input['entities_id'])? $input['entities_id']
                                                                : $appliance->fields['entities_id']);
         $input2['is_recursive'] = (isset($input['is_recursive'])? $input['is_recursive']
                                                                 : $appliance->fields['entities_id']);
         $input2['name']         = addslashes($params['plugin_appliances_appliancetypes_name']);
         $input['appliancetypes_id'] = $type->import($input2);

      } else if (isset($params['plugin_appliances_appliancetypes_id'])) {
         $input['appliancetypes_id'] = intval($params['plugin_appliances_appliancetypes_id']);
      }

      if ($appliance->update($input)) {
         // Does not detect unicity error on externalid :(
         return $appliance->methodGetAppliance(['id' => $id], $protocol);
      }

      return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_FAILED);
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

      if ($result = $DB->request(['FROM'  => 'glpi_appliances',
                                  'WHERE' => ['externalidentifier' => $extid]])) {
            if (count($result) != 1) {
               return false;
            }

            foreach ($result as $id => $row) {
               $this->fields[$id] = $row;
            }
            if (is_array($this->fields) && count($this->fields)) {
               return true;
            }
      }
      return false;
   }

   /**
    * @param $params
    * @param $protocol
    *
    * @return array
    **/
    static function methodAddAppliance($params, $protocol) {

      // TODO : add more fields
      if (isset ($params['help'])) {
         return ['help'                                  => 'bool,optional',
                 'name'                                  => 'string',
                 'entities_id'                           => 'integer,optional',
                 'is_helpdesk_visible'                   => 'bool,optional',
                 'is_recursive'                          => 'bool,optional',
                 'comment'                               => 'string,optional',
                 'externalid'                            => 'string,optional',
                 'plugin_appliances_appliancetypes_id'   => 'integer,optional',
                 'plugin_appliances_appliancetypes_name' => 'string,optional'];
      }

      if (!Session::getLoginUserID()) {
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
      $input = [];
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
         $input['externalidentifier'] = addslashes($params['externalid']);
      }

      if (isset($params['plugin_appliances_appliancetypes_name'])) {
         $type   = new ApplianceType();
         $input2 = [];
         $input2['entities_id']  = $input['entities_id'];
         $input2['is_recursive'] = $input['is_recursive'];
         $input2['name']         = addslashes($params['plugin_appliances_appliancetypes_name']);
         $input['appliancetypes_id'] = $type->import($input2);

      } else if (isset($params['plugin_appliances_appliancetypes_id'])) {
         // TODO check if this id exists and is readable and is available in appliance entity
         $input['appliancetypes_id'] = intval($params['plugin_appliances_appliancetypes_id']);
      }

      if (isset($params['is_helpdesk_visible'])) {
         $input['is_helpdesk_visible'] = ($params['is_helpdesk_visible'] ? 1 : 0);
      }

      // Old field name for compatibility
      if (isset($params['notes'])) {
         $input['notepad'] = addslashes($params['notes']);
      }
      foreach (['comment', 'notepad', 'serial', 'otherserial'] as $field) {
         if (isset($params[$field])) {
            $input[$field] = addslashes($params[$field]);
         }
      }

      $appliance = new Appliance();
      if (!$appliance->can(-1, CREATE, $input)) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTALLOWED);
      }

      $id = $appliance->add($input);
      if ($id) {
         // Return the newly created object
         return self::methodGetAppliance(['id'=>$id], $protocol);
      }

      return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_FAILED);
   }


   /**
    * @param $params
    * @param $protocol
    *
    * @return array
    **/
    static function methodGetAppliance($params, $protocol) {

      if (isset ($params['help'])) {
         return ['help'               => 'bool,optional',
                 'id2name'            => 'bool,optional',
                 'externalid OR id'   => 'string'];
      }

      if (!Session::getLoginUserID()) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset($params['externalid']) && !isset($params['id'])) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER);
      }

      $appli = new Appliance();
      $found = false;

      if (isset($params['id'])) {
         $found = $appli->getFromDB(intval($params['id']));

      } else if (isset($params['externalid'])){
         $found = self::getFromDBbyExternalID(addslashes($params["externalid"]));
      }

      if (!$found || !$appli->can($appli->fields["id"],READ)) {
         return PluginWebservicesMethodCommon::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
      }
      $resp = $appli->fields;

      if (isset($params['id2name'])) {
         $resp['appliancetypes_name']
            = Toolbox::stripTags(Dropdown::getDropdownName('glpi_appliancetypes',
                                                           $resp['appliancetypes_id']));
         $resp['environments_name']
            = Toolbox::stripTags(Dropdown::getDropdownName('glpi_appliances_environments',
                                                           $resp['environments_id']));
         $resp['users_name']
            = Toolbox::stripTags(Dropdown::getDropdownName('glpi_users', $resp['users_id']));
         $resp['groups_name']
            = Toolbox::stripTags(Dropdown::getDropdownName('glpi_groups', $resp['groups_id']));
      }
      return $resp;
   }


    /**
     * @param Migration $migration
    **/
    static function updateSchema(Migration $migration) {
      global $DB;

      $migration->displayTitle(sprintf(__('%1$s: %2$s'), __('Update'), self::getTypeName(2)));
      $dbu = new DbUtils();
      $table = $dbu->getTableForItemType(__CLASS__);

      // Version 1.6.1
      $migration->changeField($table, 'notes', 'notepad', 'text');

      // Version 1.8.0
      $migration->addKey($table, 'users_id');
      $migration->addKey($table, 'groups_id');
      $migration->addKey($table, 'plugin_appliances_appliancetypes_id');
      $migration->addKey($table, 'plugin_appliances_environments_id');

      $migration->addField($table, 'states_id', 'integer', ['after' => 'date_mod']);
      $migration->addKey($table, 'states_id');

      $migration->addField($table, 'users_id_tech', 'integer', ['after' => 'users_id']);
      $migration->addKey($table, 'users_id_tech');

      $migration->addField($table, 'groups_id_tech', 'integer', ['after' => 'groups_id']);
      $migration->addKey($table, 'groups_id_tech');

      // version 2.0
      if ($DB->tableExists("glpi_plugin_appliances_profiles")) {
         $notepad_tables = ['glpi_plugin_appliances_appliances'];

         foreach ($notepad_tables as $t) {
            // Migrate data
            if ($DB->fieldExists($t, 'notepad')) {
                 $query = "SELECT id, notepad
                           FROM `$t`
                           WHERE notepad IS NOT NULL
                               AND notepad <> '';";
               foreach ($DB->request($query) as $data) {
                  $iq = "INSERT INTO `glpi_notepads`
                                (`itemtype`, `items_id`, `content`, `date`, `date_mod`)
                         VALUES ('".$dbu->getItemTypeForTable($t)."', '".$data['id']."',
                                 '".addslashes($data['notepad'])."', NOW(), NOW())";
                  $DB->queryOrDie($iq, "0.85 migrate notepad data");
               }
               $migration->dropField(`glpi_plugin_appliances_appliances`, `notepad`);
            }
         }
      }
   }

}
