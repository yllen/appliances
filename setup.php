<?php
/*
 * @version $Id$
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
 @copyright Copyright (c) 2009-2021 Appliances plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/appliances
 @since     version 2.0
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_appliances() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['appliances'] = true;

   Plugin::registerClass('PluginAppliancesOptvalue', ['addtabon' => 'Appliance']);
   Plugin::registerClass('PluginAppliancesOptvalue_Item', ['addtabon' => 'Appliance']);

   if (class_exists('PluginAccountsAccount')) {
      PluginAccountsAccount::registerType('PluginAppliancesAppliance');
   }

   if (class_exists('PluginCertificatesCertificate')) {
      PluginCertificatesCertificate::registerType('PluginAppliancesAppliance');
   }

   if (class_exists('PluginDatabasesDatabase')) {
      PluginDatabasesDatabase::registerType('PluginAppliancesAppliance');
   }

   if (class_exists('PluginDomainsDomain')) {
      PluginDomainsDomain::registerType('PluginAppliancesAppliance');
   }

   // Define the type for which we know how to generate PDF, need :
   $PLUGIN_HOOKS['plugin_pdf']['PluginAppliancesAppliance'] = 'PluginAppliancesAppliancePDF';

   $PLUGIN_HOOKS['migratetypes']['appliances'] = 'plugin_datainjection_migratetypes_appliances';

   // Import webservice
   $PLUGIN_HOOKS['webservices']['appliances'] = 'plugin_appliances_registerMethods';

   // End init, when all types are registered
   $PLUGIN_HOOKS['post_init']['appliances'] = 'plugin_appliances_postinit';
}


// Get the name and the version of the plugin - Needed
function plugin_version_appliances() {

   return ['name'           => __('Appliances', 'appliances'),
           'version'        => '3.1.1',
           'author'         => 'Remi Collet, Nelly Mahu-Lasson',
           'license'        => 'GPLv3+',
           'homepage'       => 'https://forge.glpi-project.org/projects/appliances',
           'minGlpiVersion' => '9.5',
           'requirements'   => ['glpi' => ['min' => '9.5.0',
                                           'max' => '9.6.0']]];
}


function plugin_appliances_check_config() {
   return true;
}


function plugin_datainjection_migratetypes_appliances($types) {

   $types[1200] = 'PluginAppliancesAppliance';
   return $types;
}
