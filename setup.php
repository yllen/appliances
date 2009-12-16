<?php
/*
 * @version $Id: setup.php,v 1.2 2006/04/02 14:45:27 moyo Exp $
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

// Init the hooks of the plugins -Needed
function plugin_init_appliances() {
   global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;

   // Params : plugin name - string type - number - attributes
   Plugin::registerClass('PluginAppliancesAppliance',
                         array('classname'              => 'PluginAppliancesAppliance',
                               'tablename'              => 'glpi_plugin_appliances_appliances',
                               'formpage'               => 'front/appliance.form.php',
                               'searchpage'             => 'front/appliance.php',
                               'typename'               => $LANG['plugin_appliances']['title'][1],
                               'deleted_tables'         => true,
                               'specif_entities_tables' => true,
                               'recursive_type'         => true,
                               'linkuser_types'         => true,
                               'linkgroup_types'        => true,
                               'infocom_types'          => true,
                               'doc_types'              => true,
                               'contract_types'         => true,
                               'helpdesk_visible_types' => true));

   Plugin::registerClass('PluginAppliancesEnvironment',
                         array('classname'              => 'PluginAppliancesEnvironment',
                               'tablename'              => 'glpi_plugin_appliances_environments',
                               'typename'               => $LANG['plugin_appliances'][3],
                               'formpage'               => 'front/environment.form.php',
                               'searchpage'             => 'front/environment.php'));

   Plugin::registerClass('PluginAppliancesApplianceType',
                         array('classname'              => 'PluginAppliancesApplianceType',
                               'tablename'              => 'glpi_plugin_appliances_appliancetypes',
                               'typename'               => $LANG['plugin_appliances']['setup'][2],
                               'formpage'               => 'front/appliancetype.form.php',
                               'searchpage'             => 'front/appliancetype.php',
                               'specif_entities_tables' => true,
                               'recursive_type'         => true));

   Plugin::registerClass('PluginAppliancesAppliance_Item',
                         array('classname'              => 'PluginAppliancesAppliance_Item',
                               'tablename'              => 'glpi_plugin_appliances_appliances_items'));

   Plugin::registerClass('PluginAppliancesOptvalue',
                         array('classname'              => 'PluginAppliancesOptvalue',
                               'tablename'              => 'glpi_plugin_appliances_optvalues'));

   Plugin::registerClass('PluginAppliancesOptvalue_Item',
                         array('classname'              => 'PluginAppliancesOptvalue_Item',
                               'tablename'              => 'glpi_plugin_appliances_optvalues_items'));

   Plugin::registerClass('PluginAppliancesRelation',
                         array('classname'              => 'PluginAppliancesRelation',
                               'tablename'              => 'glpi_plugin_appliances_relations'));

   // Define the type for which we know how to generate PDF, need :
   // - plugin_appliances_prefPDF($type)
   // - plugin_appliances_generatePDF($type, $tab_id, $tab, $page=0)
   $PLUGIN_HOOKS['plugin_pdf']['PluginAppliancesAppliance'] = 'appliances';

   $PLUGIN_HOOKS['change_profile']['appliances']   = array('PluginAppliancesProfile','select');
   $PLUGIN_HOOKS['assign_to_ticket']['appliances'] = true;
   $PLUGIN_HOOKS['pre_item_purge']['appliances']     = 'plugin_pre_item_purge_appliances';
   $PLUGIN_HOOKS['item_purge']['appliances']         = 'plugin_item_purge_appliances';

   if (isset($_SESSION["glpiID"])) {
      ////array_push($CFG_GLPI["specif_entities_tables"],"glpi_plugin_appliances_appliancetypes");

      if ((isset($_SESSION["glpi_plugin_environment_installed"])
           && $_SESSION["glpi_plugin_environment_installed"] == 1)) {

         $_SESSION["glpi_plugin_environment_appliances"] = 1;

         // Display a menu entry ?
         if (plugin_appliances_haveRight("appliance","r")) {
            $PLUGIN_HOOKS['menu_entry']['appliances']      = false;
            $PLUGIN_HOOKS['submenu_entry']['environment']['options']['appliances']['title']
                                                           = $LANG['plugin_appliances']['title'][1];
            $PLUGIN_HOOKS['submenu_entry']['environment']['options']['appliances']['page']
                                                           = '/plugins/appliances/front/appliance.php';
            $PLUGIN_HOOKS['submenu_entry']['environment']['options']['appliances']['links']['search']
                                                           = '/plugins/appliances/front/appliance.php';
            $PLUGIN_HOOKS['headings']['appliances']        = 'plugin_get_headings_appliances';
            $PLUGIN_HOOKS['headings_action']['appliances'] = 'plugin_headings_actions_appliances';
            $PLUGIN_HOOKS['headings_actionpdf']['appliances']
                                                           = 'plugin_headings_actionpdf_appliances';
         }

         if (plugin_appliances_haveRight("appliance","w")) {
            $PLUGIN_HOOKS['submenu_entry']['environment']['options']['appliances']['links']['add']
                                                      = '/plugins/appliances/front/appliance.form.php';
            $PLUGIN_HOOKS['use_massive_action']['appliances'] = 1;
         }

       } else {
         // Display a menu entry ?
         if (plugin_appliances_haveRight("appliance","r")) {
            $PLUGIN_HOOKS['menu_entry']['appliances']      = 'front/appliance.php';
            $PLUGIN_HOOKS['submenu_entry']['appliances']['search'] = 'front/appliance.php';
            $PLUGIN_HOOKS['headings']['appliances']        = 'plugin_get_headings_appliances';
            $PLUGIN_HOOKS['headings_action']['appliances'] = 'plugin_headings_actions_appliances';
            $PLUGIN_HOOKS['headings_actionpdf']['appliances']
                                                           = 'plugin_headings_actionpdf_appliances';
         }

         if (plugin_appliances_haveRight("appliance","w")) {
            $PLUGIN_HOOKS['submenu_entry']['appliances']['add']
                                                           = 'front/appliance.form.php?new=1';
            $PLUGIN_HOOKS['use_massive_action']['appliances'] = 1;
         }
      }
   }
      // Import from Data_Injection plugin
      $PLUGIN_HOOKS['data_injection']['appliances'] = "plugin_appliances_data_injection_variables";
}


// Get the name and the version of the plugin - Needed
function plugin_version_appliances() {
   global $LANG;

   return array('name'           => $LANG['plugin_appliances']['title'][1],
                'version'        => '1.6.0',
                'author'         => 'Remi Collet, Xavier Caillaud, Nelly Lasson',
                'homepage'       => 'https://forge.indepnet.net/projects/show/appliances',
                'minGlpiVersion' => '0.80');
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_appliances_check_prerequisites() {

   if (GLPI_VERSION < 0.80) {
      echo "GLPI version not compatible need 0.80";
   } else {
      return true;
   }
}


// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_appliances_check_config() {
   return true;
}

function plugin_appliances_haveRight($module,$right) {

   $matches = array(""  => array("","r","w"), // ne doit pas arriver normalement
                    "r" => array("r","w"),
                    "w" => array("w"),
                    "1" => array("1"),
                    "0" => array("0",
                                 "1")); // ne doit pas arriver non plus

   if (isset($_SESSION["glpi_plugin_appliances_profiles"][$module])
       && in_array($_SESSION["glpi_plugin_appliances_profiles"][$module],$matches[$right])) {
      return true;
   }
   return false;
}

function plugin_appliances_checkRight($module, $right) {
   global $CFG_GLPI;

   if (!plugin_appliances_haveRight($module, $right)) {
      // Gestion timeout session
      if (!isset ($_SESSION["glpiID"])) {
         glpi_header($CFG_GLPI["root_doc"] . "/index.php");
         exit ();
      }
      displayRightError();
   }
}

?>