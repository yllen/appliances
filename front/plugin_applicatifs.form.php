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

$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");
define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");

useplugin('applicatifs',true);

if(!isset($_GET["ID"])) $_GET["ID"] = "";
if(!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$PluginApplicatifs=new PluginApplicatifs();

if (isset($_POST["add"])){

	if(plugin_applicatifs_haveRight("applicatifs","w")) {
		$newID=$PluginApplicatifs->add($_POST);
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($_POST["update"])){
	if(plugin_applicatifs_haveRight("applicatifs","w")) {
		$PluginApplicatifs->update($_POST);
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($_POST["delete"])){

	if(plugin_applicatifs_haveRight("applicatifs","w")) {
		$PluginApplicatifs->delete($_POST);
	}
	// CNAMTS END
	glpi_header($CFG_GLPI["root_doc"]."/plugins/applicatifs/index.php");
}
else if (isset($_POST["restore"])){

	if(plugin_applicatifs_haveRight("applicatifs","w"))
		$PluginApplicatifs->restore($_POST);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/applicatifs/index.php");
}
else if (isset($_POST["purge"]))
{
	if(plugin_applicatifs_haveRight("applicatifs","w")) {
		$PluginApplicatifs->delete($_POST,1);		
	}
	glpi_header($CFG_GLPI["root_doc"]."/plugins/applicatifs/index.php");
}

// delete a relation
else if (isset($_POST["dellieu"])){

	if (isset($_POST['itemrelation'])){
		foreach($_POST["itemrelation"]  as $key => $val) {
			plugin_applicatifs_delRelation($key);	
		}
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}

// add a relation
else if (isset($_POST["addlieu"])){

	if ($_POST['tablekey']>0){
	
		foreach($_POST["tablekey"]  as $key => $val) {
			if ($val > 0){
				plugin_applicatifs_addRelation($key,$val);	
			}
		}
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}

else if (isset($_POST["add_opt_val"])){

	plugin_applicatifs_updateOptValues($_POST);		
	glpi_header($_SERVER['HTTP_REFERER']);
}

else if (isset($_POST["additem"])){

	if ($_POST['type']>0&&$_POST['item']>0){

		if(plugin_applicatifs_haveRight("applicatifs","w"))
			plugin_applicatifs_addDevice($_POST["conID"],$_POST['item'],$_POST['type']);
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($_POST["deleteitem"])){

	if(plugin_applicatifs_haveRight("applicatifs","w"))
		foreach ($_POST["item"] as $key => $val){
		if ($val==1) {
			plugin_applicatifs_deleteDevice($key);
			}
		}

	glpi_header($_SERVER['HTTP_REFERER']);
}else if (isset($_GET["deleteapplicatifs"])){

	if(plugin_applicatifs_haveRight("applicatifs","w"))
		plugin_applicatifs_deleteDevice($_GET["ID"]);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else{

	plugin_applicatifs_checkRight("applicatifs","r");

	if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_tab']=$_GET['onglet'];
		//		glpi_header($_SERVER['HTTP_REFERER']);
	}

	$plugin = new Plugin();
	if ($plugin->isActivated("environment"))
		commonHeader($LANG['plugin_applicatifs']['title'][1],$_SERVER['PHP_SELF'],"plugins","environment","applicatifs");
	else
		commonHeader($LANG['plugin_applicatifs']['title'][1],$_SERVER["PHP_SELF"],"plugins","applicatifs");

	$PluginApplicatifs->showForm($_SERVER["PHP_SELF"],$_GET["ID"]);

	commonFooter();
}

?>