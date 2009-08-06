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

include_once ("inc/plugin_applicatifs.auth.function.php");
include_once ("inc/plugin_applicatifs.profile.class.php");

// Init the hooks of the plugins -Needed
function plugin_init_applicatifs() {

	global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
	
	// Params : plugin name - string type - number - attributes
	registerPluginType('applicatifs', 'PLUGIN_APPLICATIFS_TYPE', 1200, array(
		'classname'  => 'PluginApplicatifs',
		'tablename'  => 'glpi_plugin_applicatifs',
		'formpage'   => 'front/plugin_applicatifs.form.php',
		'searchpage' => 'index.php',
		'typename'   => $LANG['plugin_applicatifs']["title"][1],
		'deleted_tables' => true,
		'specif_entities_tables' => true,
		'recursive_type' => true,
		'linkuser_types' => true,
		'linkgroup_types' => true,
		'infocom_types' => true,
		'doc_types' => true,
		'contract_types' => true,	
		'helpdesk_visible_types' => true
		));
		
	// Define the type for which we know how to generate PDF, need :
	// - plugin_applicatifs_prefPDF($type)
	// - plugin_applicatifs_generatePDF($type, $tab_id, $tab, $page=0)
	$PLUGIN_HOOKS['plugin_pdf'][PLUGIN_APPLICATIFS_TYPE]='applicatifs';

	$PLUGIN_HOOKS['change_profile']['applicatifs'] = 'plugin_applicatifs_changeProfile';
	$PLUGIN_HOOKS['assign_to_ticket']['applicatifs'] = true;
	
	if (isset($_SESSION["glpiID"])){

		array_push($CFG_GLPI["specif_entities_tables"],"glpi_dropdown_plugin_applicatifs_type");
		
		if ((isset($_SESSION["glpi_plugin_environment_installed"]) && $_SESSION["glpi_plugin_environment_installed"]==1)){
			
			$_SESSION["glpi_plugin_environment_applicatifs"]=1;
			
			// Display a menu entry ?			
			 if (plugin_applicatifs_haveRight("applicatifs","r")){
				$PLUGIN_HOOKS['menu_entry']['applicatifs'] = false;
				$PLUGIN_HOOKS['submenu_entry']['environment']['search']['applicatifs'] = 'front/plugin_environment.form.php?plugin=applicatifs&search=1';
				$PLUGIN_HOOKS['headings']['applicatifs'] = 'plugin_get_headings_applicatifs';
				$PLUGIN_HOOKS['headings_action']['applicatifs'] = 'plugin_headings_actions_applicatifs';
				$PLUGIN_HOOKS['headings_actionpdf']['applicatifs'] = 'plugin_headings_actionpdf_applicatifs';
			}
			
			 if (plugin_applicatifs_haveRight("applicatifs","w")){
				$PLUGIN_HOOKS['submenu_entry']['environment']['add']['applicatifs'] = 'front/plugin_environment.form.php?plugin=applicatifs&add=1';
				$PLUGIN_HOOKS['use_massive_action']['applicatifs']=1;
				$PLUGIN_HOOKS['pre_item_delete']['applicatifs'] = 'plugin_pre_item_delete_applicatifs';
				$PLUGIN_HOOKS['item_purge']['applicatifs'] = 'plugin_item_purge_applicatifs';
			} 		
		}else{
		
			// Display a menu entry ?			
			 if (plugin_applicatifs_haveRight("applicatifs","r")){
				$PLUGIN_HOOKS['menu_entry']['applicatifs'] = true;
				$PLUGIN_HOOKS['submenu_entry']['applicatifs']['search'] = 'index.php';
				$PLUGIN_HOOKS['headings']['applicatifs'] = 'plugin_get_headings_applicatifs';
				$PLUGIN_HOOKS['headings_action']['applicatifs'] = 'plugin_headings_actions_applicatifs';
				$PLUGIN_HOOKS['headings_actionpdf']['applicatifs'] = 'plugin_headings_actionpdf_applicatifs';
			}
			
			 if (plugin_applicatifs_haveRight("applicatifs","w")){
				$PLUGIN_HOOKS['submenu_entry']['applicatifs']['add'] = 'front/plugin_applicatifs.form.php?new=1'; 
				$PLUGIN_HOOKS['use_massive_action']['applicatifs']=1;
				$PLUGIN_HOOKS['pre_item_delete']['applicatifs'] = 'plugin_pre_item_delete_applicatifs';
				$PLUGIN_HOOKS['item_purge']['applicatifs'] = 'plugin_item_purge_applicatifs';
			}           
		}

		// Add specific files to add to the header : javascript or css
		//$PLUGIN_HOOKS['add_javascript']['example']="example.js";
		//$PLUGIN_HOOKS['add_css']['applicatifs']="applicatifs.css";
		
		// Import from Data_Injection plugin
		$PLUGIN_HOOKS['data_injection']['applicatifs'] = "plugin_applicatifs_data_injection_variables";
		}
	
}

// Get the name and the version of the plugin - Needed

function plugin_version_applicatifs(){
	global $LANG;
	
	return array (
		'name' => $LANG['plugin_applicatifs']['title'][1],
		'version' => '1.5.1',
		'author'=>'Remi Collet, Xavier Caillaud',
		'homepage'=>'http://glpi-project.org/wiki/doku.php?id='.substr($_SESSION["glpilanguage"],0,2).':plugins:pluginslist',
		'minGlpiVersion' => '0.72',// For compatibility / no install in version < 0.72
	);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_applicatifs_check_prerequisites(){

	if (GLPI_VERSION>=0.72){
		return true;
	} else {
		echo "GLPI version not compatible need 0.72";
	}
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_applicatifs_check_config(){
	return true;
}

// Define rights for the plugin types
function plugin_applicatifs_haveTypeRight($type,$right){
	switch ($type){
		case PLUGIN_APPLICATIFS_TYPE :
			// 1 - All rights for all users
			// return true;
			// 2 - Similarity right : same right of computer
			return plugin_applicatifs_haveRight("applicatifs",$right);
			break;
	}
}

?>