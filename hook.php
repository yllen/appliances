<?php
/*
 * @version $Id: hook.php 7355 2008-10-03 15:31:00Z moyo $
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copynetwork (C) 2003-2006 by the INDEPNET Development Team.

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
// Original Author of file: DOMBRE Julien
// Purpose of file:
// ----------------------------------------------------------------------

define("PLUGIN_APPLICATIF_RELATION_LOCATION",1);
foreach (glob(GLPI_ROOT . '/plugins/applicatifs/inc/*.php') as $file)
	include_once ($file);

function plugin_applicatifs_AssignToTicket($types){
	global $LANG;

	if (plugin_applicatifs_haveRight("open_ticket","1"))
		$types[PLUGIN_APPLICATIFS_TYPE]=$LANG['plugin_applicatifs']['title'][1];
	return $types;
}

function plugin_applicatifs_install(){

	include_once (GLPI_ROOT."/inc/profile.class.php");

	if(!TableExists("glpi_plugin_applicatifs_profiles") ){

		plugin_applicatifs_installing("1.5.0");

	}elseif(TableExists("glpi_plugin_applicatifs") && !FieldExists("glpi_plugin_applicatifs","helpdesk_visible")) {

		if (TableExists("glpi_plugin_applicatifs") &&!FieldExists("glpi_plugin_applicatifs","FK_Entities")){

			plugin_applicatifs_update("1.3");
			plugin_applicatifs_updatev14();
			plugin_applicatifs_update("1.5.0");
			plugin_applicatifs_update("1.5.1");

		}elseif (TableExists("glpi_plugin_applicatifs") &&!FieldExists("glpi_plugin_applicatifs","recursive")){

			plugin_applicatifs_updatev14();
			plugin_applicatifs_update("1.5.0");
			plugin_applicatifs_update("1.5.1");

		}elseif(TableExists("glpi_plugin_applicatifs") && !FieldExists("glpi_plugin_applicatifs","interface")) {

			plugin_applicatifs_update("1.5.0");
			plugin_applicatifs_update("1.5.1");

		}else {

			plugin_applicatifs_update("1.5.1");

		}
	}
	plugin_applicatifs_createFirstAccess($_SESSION['glpiactiveprofile']['ID']);
	return true;
}

function plugin_applicatifs_uninstall(){
	global $DB;

	$tables = array("glpi_plugin_applicatifs",
					"glpi_plugin_applicatifs_device",
					"glpi_dropdown_plugin_applicatifs_type",
					"glpi_dropdown_plugin_applicatifs_relationtype",
					"glpi_plugin_applicatifs_profiles",
					"glpi_plugin_applicatifs_relation",
					"glpi_plugin_applicatifs_optvalues",
					"glpi_plugin_applicatifs_optvalues_machines",
					"glpi_dropdown_plugin_applicatifs_environment");

	foreach($tables as $table)
		$DB->query("DROP TABLE `$table`;");

	$query="DELETE FROM glpi_display WHERE type='".PLUGIN_APPLICATIFS_TYPE."';";
	$DB->query($query);

	$query="DELETE FROM glpi_doc_device WHERE device_type='".PLUGIN_APPLICATIFS_TYPE."';";
	$DB->query($query);

	$query="DELETE FROM glpi_bookmark WHERE device_type='".PLUGIN_APPLICATIFS_TYPE."';";
	$DB->query($query);

	$query="DELETE FROM glpi_history WHERE device_type='".PLUGIN_APPLICATIFS_TYPE."';";
	$DB->query($query);

	if (TableExists("glpi_plugin_data_injection_models"))
		$DB->query("DELETE FROM glpi_plugin_data_injection_models, glpi_plugin_data_injection_mappings, glpi_plugin_data_injection_infos USING glpi_plugin_data_injection_models, glpi_plugin_data_injection_mappings, glpi_plugin_data_injection_infos
		WHERE glpi_plugin_data_injection_models.device_type=".PLUGIN_APPLICATIFS_TYPE."
		AND glpi_plugin_data_injection_mappings.model_id=glpi_plugin_data_injection_models.ID
		AND glpi_plugin_data_injection_infos.model_id=glpi_plugin_data_injection_models.ID");

	plugin_init_applicatifs();
	cleanCache("GLPI_HEADER_".$_SESSION["glpiID"]);

	return true;
}

// Define dropdown relations
function plugin_applicatifs_getDatabaseRelations(){
	$plugin = new Plugin();
	if ($plugin->isActivated("applicatifs"))
		return array(
			"glpi_dropdown_plugin_applicatifs_type"=>array("glpi_plugin_applicatifs"=>"type"),
			"glpi_dropdown_plugin_applicatifs_environment"=>array("glpi_plugin_applicatifs"=>"environment"),
			"glpi_entities"=>array("glpi_plugin_applicatifs"=>"FK_entities",
			"glpi_dropdown_plugin_applicatifs_type"=>"FK_entities"),
			"glpi_plugin_applicatifs"=>array("glpi_plugin_applicatifs_device"=>"FK_applicatif"),
			"_virtual_device"=>array("glpi_plugin_applicatifs_device"=> array("FK_device","device_type")),

		);
	else
		return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_applicatifs_getDropdown(){
	// Table => Name
	global $LANG;

	$plugin = new Plugin();
	if ($plugin->isActivated("applicatifs"))
		return array("glpi_dropdown_plugin_applicatifs_type"=>$LANG['plugin_applicatifs']['setup'][2],"glpi_dropdown_plugin_applicatifs_environment"=>$LANG['plugin_applicatifs'][3]);
		// Harded coded : no edit possible
		//	"glpi_dropdown_plugin_applicatifs_relationtype"=>$LANG['plugin_applicatifs']['setup'][15]);
	else
		return array();
}

////// SEARCH FUNCTIONS ///////(){

// Define search option for types of the plugins
function plugin_applicatifs_getSearchOption(){
	global $LANG;
	$sopt=array();

	if (plugin_applicatifs_haveRight("applicatifs","r")){
	// Part header
		$sopt[PLUGIN_APPLICATIFS_TYPE]['common']=$LANG['plugin_applicatifs']['title'][1];

		$sopt[PLUGIN_APPLICATIFS_TYPE][1]['table']='glpi_plugin_applicatifs';
		$sopt[PLUGIN_APPLICATIFS_TYPE][1]['field']='name';
		$sopt[PLUGIN_APPLICATIFS_TYPE][1]['linkfield']='name';
		$sopt[PLUGIN_APPLICATIFS_TYPE][1]['name']=$LANG['plugin_applicatifs'][8];
		$sopt[PLUGIN_APPLICATIFS_TYPE][1]['datatype']='itemlink';

		$sopt[PLUGIN_APPLICATIFS_TYPE][2]['table']='glpi_dropdown_plugin_applicatifs_type';
		$sopt[PLUGIN_APPLICATIFS_TYPE][2]['field']='name';
		$sopt[PLUGIN_APPLICATIFS_TYPE][2]['linkfield']='type';
		$sopt[PLUGIN_APPLICATIFS_TYPE][2]['name']=$LANG['plugin_applicatifs'][20];

		$sopt[PLUGIN_APPLICATIFS_TYPE][3]['table']='glpi_dropdown_locations';
		$sopt[PLUGIN_APPLICATIFS_TYPE][3]['field']='completename';
		$sopt[PLUGIN_APPLICATIFS_TYPE][3]['linkfield']='location';
		$sopt[PLUGIN_APPLICATIFS_TYPE][3]['name']=$LANG['plugin_applicatifs'][2];

		$sopt[PLUGIN_APPLICATIFS_TYPE][4]['table']='glpi_plugin_applicatifs';
		$sopt[PLUGIN_APPLICATIFS_TYPE][4]['field']='comments';
		$sopt[PLUGIN_APPLICATIFS_TYPE][4]['linkfield']='comments';
		$sopt[PLUGIN_APPLICATIFS_TYPE][4]['name']=$LANG['plugin_applicatifs'][12];
		$sopt[PLUGIN_APPLICATIFS_TYPE][4]['datatype']='text';

		$sopt[PLUGIN_APPLICATIFS_TYPE][5]['table']='glpi_plugin_applicatifs_device';
		$sopt[PLUGIN_APPLICATIFS_TYPE][5]['field']='FK_device';
		$sopt[PLUGIN_APPLICATIFS_TYPE][5]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][5]['name']=$LANG['plugin_applicatifs'][7];
		$sopt[PLUGIN_APPLICATIFS_TYPE][5]['forcegroupby']=true;

		$sopt[PLUGIN_APPLICATIFS_TYPE][6]['table']='glpi_users';
		$sopt[PLUGIN_APPLICATIFS_TYPE][6]['field']='name';
		$sopt[PLUGIN_APPLICATIFS_TYPE][6]['linkfield']='FK_users';
		$sopt[PLUGIN_APPLICATIFS_TYPE][6]['name']=$LANG['plugin_applicatifs'][21];

		$sopt[PLUGIN_APPLICATIFS_TYPE][7]['table']='glpi_plugin_applicatifs';
		$sopt[PLUGIN_APPLICATIFS_TYPE][7]['field']='recursive';
		$sopt[PLUGIN_APPLICATIFS_TYPE][7]['linkfield']='recursive';
		$sopt[PLUGIN_APPLICATIFS_TYPE][7]['name']=$LANG['entity'][9];
		$sopt[PLUGIN_APPLICATIFS_TYPE][7]['datatype']='bool';

		$sopt[PLUGIN_APPLICATIFS_TYPE][8]['table']='glpi_groups';
		$sopt[PLUGIN_APPLICATIFS_TYPE][8]['field']='name';
		$sopt[PLUGIN_APPLICATIFS_TYPE][8]['linkfield']='FK_Groups';
		$sopt[PLUGIN_APPLICATIFS_TYPE][8]['name']=$LANG['common'][35];

		$sopt[PLUGIN_APPLICATIFS_TYPE][9]['table']='glpi_plugin_applicatifs';
		$sopt[PLUGIN_APPLICATIFS_TYPE][9]['field']='date_mod';
		$sopt[PLUGIN_APPLICATIFS_TYPE][9]['linkfield']='date_mod';
		$sopt[PLUGIN_APPLICATIFS_TYPE][9]['name']=$LANG['common'][26];
		$sopt[PLUGIN_APPLICATIFS_TYPE][9]['datatype']='datetime';

		$sopt[PLUGIN_APPLICATIFS_TYPE][10]['table']='glpi_dropdown_plugin_applicatifs_environment';
		$sopt[PLUGIN_APPLICATIFS_TYPE][10]['field']='name';
		$sopt[PLUGIN_APPLICATIFS_TYPE][10]['linkfield']='environment';
		$sopt[PLUGIN_APPLICATIFS_TYPE][10]['name']=$LANG['plugin_applicatifs'][3];

		$sopt[PLUGIN_APPLICATIFS_TYPE][11]['table']='glpi_plugin_applicatifs';
		$sopt[PLUGIN_APPLICATIFS_TYPE][11]['field']='helpdesk_visible';
		$sopt[PLUGIN_APPLICATIFS_TYPE][11]['linkfield']='helpdesk_visible';
		$sopt[PLUGIN_APPLICATIFS_TYPE][11]['name']=$LANG['software'][46];
		$sopt[PLUGIN_APPLICATIFS_TYPE][11]['datatype']='bool';

		$sopt[PLUGIN_APPLICATIFS_TYPE][30]['table']='glpi_plugin_applicatifs';
		$sopt[PLUGIN_APPLICATIFS_TYPE][30]['field']='ID';
		$sopt[PLUGIN_APPLICATIFS_TYPE][30]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][30]['name']=$LANG['common'][2];

		$sopt[PLUGIN_APPLICATIFS_TYPE][80]['table']='glpi_entities';
		$sopt[PLUGIN_APPLICATIFS_TYPE][80]['field']='completename';
		$sopt[PLUGIN_APPLICATIFS_TYPE][80]['linkfield']='FK_entities';
		$sopt[PLUGIN_APPLICATIFS_TYPE][80]['name']=$LANG['entity'][0];

		$sopt[PLUGIN_APPLICATIFS_TYPE]['tracking']=$LANG['title'][24];

		$sopt[PLUGIN_APPLICATIFS_TYPE][60]['table']='glpi_tracking';
		$sopt[PLUGIN_APPLICATIFS_TYPE][60]['field']='count';
		$sopt[PLUGIN_APPLICATIFS_TYPE][60]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][60]['name']=$LANG['stats'][13];
		$sopt[PLUGIN_APPLICATIFS_TYPE][60]['forcegroupby']=true;
		$sopt[PLUGIN_APPLICATIFS_TYPE][60]['usehaving']=true;
		$sopt[PLUGIN_APPLICATIFS_TYPE][60]['datatype']='number';

		$sopt[PLUGIN_APPLICATIFS_TYPE]['contract']=$LANG['Menu'][25];

		$sopt[PLUGIN_APPLICATIFS_TYPE][29]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][29]['field']='name';
		$sopt[PLUGIN_APPLICATIFS_TYPE][29]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][29]['name']=$LANG['common'][16]." ".$LANG['financial'][1];
		$sopt[PLUGIN_APPLICATIFS_TYPE][29]['forcegroupby']=true;

		$sopt[PLUGIN_APPLICATIFS_TYPE][30]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][30]['field']='num';
		$sopt[PLUGIN_APPLICATIFS_TYPE][30]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][30]['name']=$LANG['financial'][4]." ".$LANG['financial'][1];
		$sopt[PLUGIN_APPLICATIFS_TYPE][30]['forcegroupby']=true;

		$sopt[PLUGIN_APPLICATIFS_TYPE][130]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][130]['field']='duration';
		$sopt[PLUGIN_APPLICATIFS_TYPE][130]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][130]['name']=$LANG['financial'][8]." ".$LANG['financial'][1];
		$sopt[PLUGIN_APPLICATIFS_TYPE][130]['forcegroupby']=true;

		$sopt[PLUGIN_APPLICATIFS_TYPE][131]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][131]['field']='periodicity';
		$sopt[PLUGIN_APPLICATIFS_TYPE][131]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][131]['name']=$LANG['financial'][69];
		$sopt[PLUGIN_APPLICATIFS_TYPE][131]['forcegroupby']=true;

		$sopt[PLUGIN_APPLICATIFS_TYPE][132]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][132]['field']='begin_date';
		$sopt[PLUGIN_APPLICATIFS_TYPE][132]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][132]['name']=$LANG['search'][8]." ".$LANG['financial'][1];
		$sopt[PLUGIN_APPLICATIFS_TYPE][132]['forcegroupby']=true;
		$sopt[PLUGIN_APPLICATIFS_TYPE][132]['datatype']='date';

		$sopt[PLUGIN_APPLICATIFS_TYPE][133]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][133]['field']='compta_num';
		$sopt[PLUGIN_APPLICATIFS_TYPE][133]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][133]['name']=$LANG['financial'][13]." ".$LANG['financial'][1];
		$sopt[PLUGIN_APPLICATIFS_TYPE][133]['forcegroupby']=true;

		$sopt[PLUGIN_APPLICATIFS_TYPE][134]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][134]['field']='end_date';
		$sopt[PLUGIN_APPLICATIFS_TYPE][134]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][134]['name']=$LANG['search'][9]." ".$LANG['financial'][1];
		$sopt[PLUGIN_APPLICATIFS_TYPE][134]['forcegroupby']=true;
		$sopt[PLUGIN_APPLICATIFS_TYPE][134]['datatype']='date_delay';
		$sopt[PLUGIN_APPLICATIFS_TYPE][134]['datafields'][1]='begin_date';
		$sopt[PLUGIN_APPLICATIFS_TYPE][134]['datafields'][2]='duration';

		$sopt[PLUGIN_APPLICATIFS_TYPE][135]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][135]['field']='notice';
		$sopt[PLUGIN_APPLICATIFS_TYPE][135]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][135]['name']=$LANG['financial'][10]." ".$LANG['financial'][1];
		$sopt[PLUGIN_APPLICATIFS_TYPE][135]['forcegroupby']=true;

		$sopt[PLUGIN_APPLICATIFS_TYPE][136]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][136]['field']='cost';
		$sopt[PLUGIN_APPLICATIFS_TYPE][136]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][136]['name']=$LANG['financial'][5]." ".$LANG['financial'][1];
		$sopt[PLUGIN_APPLICATIFS_TYPE][136]['forcegroupby']=true;

		$sopt[PLUGIN_APPLICATIFS_TYPE][137]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][137]['field']='facturation';
		$sopt[PLUGIN_APPLICATIFS_TYPE][137]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][137]['name']=$LANG['financial'][11]." ".$LANG['financial'][1];
		$sopt[PLUGIN_APPLICATIFS_TYPE][137]['forcegroupby']=true;

		$sopt[PLUGIN_APPLICATIFS_TYPE][138]['table']='glpi_contracts';
		$sopt[PLUGIN_APPLICATIFS_TYPE][138]['field']='renewal';
		$sopt[PLUGIN_APPLICATIFS_TYPE][138]['linkfield']='';
		$sopt[PLUGIN_APPLICATIFS_TYPE][138]['name']=$LANG['financial'][107]." ".$LANG['financial'][1];
		$sopt[PLUGIN_APPLICATIFS_TYPE][138]['forcegroupby']=true;

		$sopt[COMPUTER_TYPE][1210]['table']='glpi_plugin_applicatifs';
		$sopt[COMPUTER_TYPE][1210]['field']='name';
		$sopt[COMPUTER_TYPE][1210]['linkfield']='';
		$sopt[COMPUTER_TYPE][1210]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][8];
		$sopt[COMPUTER_TYPE][1210]['forcegroupby']='1';
		$sopt[COMPUTER_TYPE][1210]['datatype']='itemlink';
		$sopt[COMPUTER_TYPE][1210]['itemlink_type']=PLUGIN_APPLICATIFS_TYPE;

		$sopt[COMPUTER_TYPE][1211]['table']='glpi_dropdown_plugin_applicatifs_type';
		$sopt[COMPUTER_TYPE][1211]['field']='name';
		$sopt[COMPUTER_TYPE][1211]['linkfield']='';
		$sopt[COMPUTER_TYPE][1211]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][20];
		$sopt[COMPUTER_TYPE][1211]['forcegroupby']='1';

		$sopt[MONITOR_TYPE][1210]['table']='glpi_plugin_applicatifs';
		$sopt[MONITOR_TYPE][1210]['field']='name';
		$sopt[MONITOR_TYPE][1210]['linkfield']='';
		$sopt[MONITOR_TYPE][1210]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][8];
		$sopt[MONITOR_TYPE][1210]['forcegroupby']='1';
		$sopt[MONITOR_TYPE][1210]['datatype']='itemlink';
		$sopt[MONITOR_TYPE][1210]['itemlink_type']=PLUGIN_APPLICATIFS_TYPE;

		$sopt[MONITOR_TYPE][1211]['table']='glpi_dropdown_plugin_applicatifs_type';
		$sopt[MONITOR_TYPE][1211]['field']='name';
		$sopt[MONITOR_TYPE][1211]['linkfield']='';
		$sopt[MONITOR_TYPE][1211]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][20];
		$sopt[MONITOR_TYPE][1211]['forcegroupby']='1';

		$sopt[NETWORKING_TYPE][1210]['table']='glpi_plugin_applicatifs';
		$sopt[NETWORKING_TYPE][1210]['field']='name';
		$sopt[NETWORKING_TYPE][1210]['linkfield']='';
		$sopt[NETWORKING_TYPE][1210]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][8];
		$sopt[NETWORKING_TYPE][1210]['forcegroupby']='1';
		$sopt[NETWORKING_TYPE][1210]['datatype']='itemlink';
		$sopt[NETWORKING_TYPE][1210]['itemlink_type']=PLUGIN_APPLICATIFS_TYPE;

		$sopt[NETWORKING_TYPE][1211]['table']='glpi_dropdown_plugin_applicatifs_type';
		$sopt[NETWORKING_TYPE][1211]['field']='name';
		$sopt[NETWORKING_TYPE][1211]['linkfield']='';
		$sopt[NETWORKING_TYPE][1211]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][20];
		$sopt[NETWORKING_TYPE][1211]['forcegroupby']='1';

		$sopt[PERIPHERAL_TYPE][1210]['table']='glpi_plugin_applicatifs';
		$sopt[PERIPHERAL_TYPE][1210]['field']='name';
		$sopt[PERIPHERAL_TYPE][1210]['linkfield']='';
		$sopt[PERIPHERAL_TYPE][1210]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][8];
		$sopt[PERIPHERAL_TYPE][1210]['forcegroupby']='1';
		$sopt[PERIPHERAL_TYPE][1210]['datatype']='itemlink';
		$sopt[PERIPHERAL_TYPE][1210]['itemlink_type']=PLUGIN_APPLICATIFS_TYPE;

		$sopt[PERIPHERAL_TYPE][1211]['table']='glpi_dropdown_plugin_applicatifs_type';
		$sopt[PERIPHERAL_TYPE][1211]['field']='name';
		$sopt[PERIPHERAL_TYPE][1211]['linkfield']='';
		$sopt[PERIPHERAL_TYPE][1211]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][20];
		$sopt[PERIPHERAL_TYPE][1211]['forcegroupby']='1';

		$sopt[PHONE_TYPE][1210]['table']='glpi_plugin_applicatifs';
		$sopt[PHONE_TYPE][1210]['field']='name';
		$sopt[PHONE_TYPE][1210]['linkfield']='';
		$sopt[PHONE_TYPE][1210]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][8];
		$sopt[PHONE_TYPE][1210]['forcegroupby']='1';
		$sopt[PHONE_TYPE][1210]['datatype']='itemlink';
		$sopt[PHONE_TYPE][1210]['itemlink_type']=PLUGIN_APPLICATIFS_TYPE;

		$sopt[PHONE_TYPE][1211]['table']='glpi_dropdown_plugin_applicatifs_type';
		$sopt[PHONE_TYPE][1211]['field']='name';
		$sopt[PHONE_TYPE][1211]['linkfield']='';
		$sopt[PHONE_TYPE][1211]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][20];
		$sopt[PHONE_TYPE][1211]['forcegroupby']='1';

		$sopt[PRINTER_TYPE][1210]['table']='glpi_plugin_applicatifs';
		$sopt[PRINTER_TYPE][1210]['field']='name';
		$sopt[PRINTER_TYPE][1210]['linkfield']='';
		$sopt[PRINTER_TYPE][1210]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][8];
		$sopt[PRINTER_TYPE][1210]['forcegroupby']='1';
		$sopt[PRINTER_TYPE][1210]['datatype']='itemlink';
		$sopt[PRINTER_TYPE][1210]['itemlink_type']=PLUGIN_APPLICATIFS_TYPE;

		$sopt[PRINTER_TYPE][1211]['table']='glpi_dropdown_plugin_applicatifs_type';
		$sopt[PRINTER_TYPE][1211]['field']='name';
		$sopt[PRINTER_TYPE][1211]['linkfield']='';
		$sopt[PRINTER_TYPE][1211]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][20];
		$sopt[PRINTER_TYPE][1211]['forcegroupby']='1';

		$sopt[SOFTWARE_TYPE][1210]['table']='glpi_plugin_applicatifs';
		$sopt[SOFTWARE_TYPE][1210]['field']='name';
		$sopt[SOFTWARE_TYPE][1210]['linkfield']='';
		$sopt[SOFTWARE_TYPE][1210]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][8];
		$sopt[SOFTWARE_TYPE][1210]['forcegroupby']='1';
		$sopt[SOFTWARE_TYPE][1210]['datatype']='itemlink';
		$sopt[SOFTWARE_TYPE][1210]['itemlink_type']=PLUGIN_APPLICATIFS_TYPE;

		$sopt[SOFTWARE_TYPE][1211]['table']='glpi_dropdown_plugin_applicatifs_type';
		$sopt[SOFTWARE_TYPE][1211]['field']='name';
		$sopt[SOFTWARE_TYPE][1211]['linkfield']='';
		$sopt[SOFTWARE_TYPE][1211]['name']=$LANG['plugin_applicatifs']['title'][1]." - ".$LANG['plugin_applicatifs'][20];
		$sopt[SOFTWARE_TYPE][1211]['forcegroupby']='1';
		}

	return $sopt;
}

function plugin_applicatifs_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables){

	switch ($new_table){

		case "glpi_plugin_applicatifs_device" :
			return " LEFT JOIN $new_table ON ($ref_table.ID = $new_table.FK_applicatif) ";
			break;
		case "glpi_plugin_applicatifs" : // From items
			$out= " LEFT JOIN glpi_plugin_applicatifs_device ON ($ref_table.ID = glpi_plugin_applicatifs_device.FK_device AND glpi_plugin_applicatifs_device.device_type=$type) ";
			$out.= " LEFT JOIN glpi_plugin_applicatifs ON (glpi_plugin_applicatifs.ID = glpi_plugin_applicatifs_device.FK_applicatif) ";
			return $out;
			break;
		case "glpi_dropdown_plugin_applicatifs_type" : // From items
			$out=addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_applicatifs",$linkfield);
			$out.= " LEFT JOIN glpi_dropdown_plugin_applicatifs_type ON (glpi_dropdown_plugin_applicatifs_type.ID = glpi_plugin_applicatifs.type) ";
			return $out;
			break;
	}

	return "";
}

function plugin_applicatifs_forceGroupBy($type){

	return true;
	switch ($type){
		case PLUGIN_APPLICATIFS_TYPE:
			return true;
			break;

	}
	return false;
}

function plugin_applicatifs_giveItem($type,$ID,$data,$num){
	global $CFG_GLPI, $INFOFORM_PAGES, $LANG,$SEARCH_OPTION,$LINK_ID_TABLE,$DB;

	$table=$SEARCH_OPTION[$type][$ID]["table"];
	$field=$SEARCH_OPTION[$type][$ID]["field"];

	switch ($table.'.'.$field){
		case "glpi_plugin_applicatifs_device.FK_device" :
			$query_device = "SELECT DISTINCT device_type
							FROM glpi_plugin_applicatifs_device
							WHERE FK_applicatif = '".$data['ID']."'
							ORDER BY device_type";
			$result_device = $DB->query($query_device);
			$number_device = $DB->numrows($result_device);
			$y = 0;
			$out='';
			$applicatif=$data['ID'];
			if ($number_device>0){
				$ci=new CommonItem();
				while ($y < $number_device) {
					$column="name";
					if ($type==TRACKING_TYPE) $column="ID";
					$type=$DB->result($result_device, $y, "device_type");
					if (!empty($LINK_ID_TABLE[$type])){
						$query = "SELECT ".$LINK_ID_TABLE[$type].".*, glpi_plugin_applicatifs_device.ID AS IDD, glpi_entities.ID AS entity "
						." FROM glpi_plugin_applicatifs_device, ".$LINK_ID_TABLE[$type]
						." LEFT JOIN glpi_entities ON (glpi_entities.ID=".$LINK_ID_TABLE[$type].".FK_entities) "
						." WHERE ".$LINK_ID_TABLE[$type].".ID = glpi_plugin_applicatifs_device.FK_device
						AND glpi_plugin_applicatifs_device.device_type='$type'
						AND glpi_plugin_applicatifs_device.FK_applicatif = '".$applicatif."' "
						. getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',isset($CFG_GLPI["recursive_type"][$type]));

						if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
							$query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
						}
						$query.=" ORDER BY glpi_entities.completename, ".$LINK_ID_TABLE[$type].".$column";

						if ($result_linked=$DB->query($query))
							if ($DB->numrows($result_linked)){
								$ci->setType($type);
								while ($data=$DB->fetch_assoc($result_linked)){
									$out.=$ci->getType()." - ";
									$ID="";
									if($_SESSION["glpiview_ID"]||empty($data["name"])) $ID= " (".$data["ID"].")";
									$name= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type]."?ID=".$data["ID"]."\">"
									.$data["name"]."$ID</a>";
									$out.=$name."<br>";

								}
							}else
								$out.=' ';
						}else
							$out.=' ';
					$y++;
				}
			}
		return $out;
		break;
	}
	return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_applicatifs_MassiveActions($type){
	global $LANG;

	switch ($type){
		case PLUGIN_APPLICATIFS_TYPE:
			return array(
				// Specific one
				"plugin_applicatifs_install"=>$LANG['plugin_applicatifs']['setup'][9],
				"plugin_applicatifs_desinstall"=>$LANG['plugin_applicatifs']['setup'][10],
				"plugin_applicatifs_transfert"=>$LANG['buttons'][48],
				);
			break;
		default:
			if (in_array($type, plugin_applicatifs_getTypes())) {
				return array("plugin_applicatifs_add_item"=>$LANG['plugin_applicatifs']['setup'][13]);
			}
	}

	return array();
}

function plugin_applicatifs_MassiveActionsDisplay($type,$action){
	global $LANG;

	switch ($type){
		case PLUGIN_APPLICATIFS_TYPE:
			switch ($action){
				// No case for add_document : use GLPI core one
				case "plugin_applicatifs_install":
					dropdownAllItems("item_item",0,0,-1,plugin_applicatifs_getTypes());
					echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
				case "plugin_applicatifs_desinstall":
					dropdownAllItems("item_item",0,0,-1,plugin_applicatifs_getTypes());
					echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
				case "plugin_applicatifs_transfert":
					dropdownValue("glpi_entities", "FK_entities", '');
				echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
			}
			break;
		default:
			if (in_array($type, plugin_applicatifs_getTypes())) {
				plugin_applicatifs_dropdownapplicatifs("conID");
				echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
			}
	}

	return "";
}

function plugin_applicatifs_MassiveActionsProcess($data){
	global $LANG,$DB;

	switch ($data['action']){

		case "plugin_applicatifs_add_item":
				$PluginApplicatifs=new PluginApplicatifs();
 	                $ci2=new CommonItem();
 	                if ($PluginApplicatifs->getFromDB($data['conID'])){
	                    foreach ($data["item"] as $key => $val){
 	                        if ($val==1) {
	                            // Items exists ?
	                            if ($ci2->getFromDB($data["device_type"],$key)){
	                                // Entity security
 	                                if (!isset($PluginApplicatifs->obj->fields["FK_entities"])
								||$ci2->obj->fields["FK_entities"]==$PluginApplicatifs->obj->fields["FK_entities"]
								||($ci2->obj->fields["recursive"] && in_array($ci2->obj->fields["FK_entities"], getEntityAncestors($PluginApplicatifs->obj->fields["FK_entities"])))){
 	                                    plugin_applicatifs_addDevice($data["conID"],$key,$data['device_type']);
 	                                }
 	                            }
 	                        }
 	                    }
 	                }

		break;

		case "plugin_applicatifs_install":
			if ($data['device_type']==PLUGIN_APPLICATIFS_TYPE){

			$PluginApplicatifs=new PluginApplicatifs();
			$ci=new CommonItem();
			foreach ($data["item"] as $key => $val){
				if ($val==1){
					// Items exists ?
					if ($PluginApplicatifs->getFromDB($key)){
						// Entity security
						if ($ci->getFromDB($data['type'],$data['item_item'])){
							if (!isset($PluginApplicatifs->obj->fields["FK_entities"])
								||$ci->obj->fields["FK_entities"]==$PluginApplicatifs->obj->fields["FK_entities"]
								||($ci->obj->fields["recursive"] && in_array($ci->obj->fields["FK_entities"], getEntityAncestors($PluginApplicatifs->obj->fields["FK_entities"])))){
								plugin_applicatifs_addDevice($key,$data["item_item"],$data['type']);
							}
						}
					}
				}
			}
		}
		break;
		case "plugin_applicatifs_desinstall":
				if ($data['device_type']==PLUGIN_APPLICATIFS_TYPE){
					foreach ($data["item"] as $key => $val){
						if ($val==1){
							$query="DELETE
									FROM glpi_plugin_applicatifs_device
									WHERE device_type='".$data['type']."'
									AND FK_device='".$data['item_item']."'
									AND FK_applicatif = '$key'";
							$DB->query($query);
					}
				}
			}
		break;
		case "plugin_applicatifs_transfert":
		if ($data['device_type']==PLUGIN_APPLICATIFS_TYPE){
			foreach ($data["item"] as $key => $val){
				if ($val==1){

					$PluginApplicatifs=new PluginApplicatifs;
					$PluginApplicatifs->getFromDB($key);

					$type=plugin_applicatifs_transferDropdown($PluginApplicatifs->fields["type"],$data['FK_entities']);
					$values["ID"] = $key;
					$values["type"] = $type;
					$PluginApplicatifs->update($values);
					unset($values);
					$values["ID"] = $key;
					$values["FK_entities"] = $data['FK_entities'];
					$PluginApplicatifs->update($values);
				}
			}
		}
		break;
	}
}

//////////////////////////////

// Hook done on delete item case

function plugin_pre_item_delete_applicatifs($input){

	if (isset($input["_item_type_"]))
		switch ($input["_item_type_"]){
			case PROFILE_TYPE :
				// Manipulate data if needed
				$PluginApplicatifsProfile=new PluginApplicatifsProfile;
				$PluginApplicatifsProfile->cleanProfiles($input["ID"]);
				break;
		}
	return $input;
}

function plugin_item_delete_applicatifs($parm){

	switch ($parm["type"]){
		case TRACKING_TYPE :
			$PluginApplicatifs=new PluginApplicatifs;
			$PluginApplicatifs->cleanItems($parm['ID'], $parm['type']);
			return true;
			break;
	}

	return false;
}

// Hook done on purge item case
function plugin_item_purge_applicatifs($parm){

	if (in_array($parm["type"], plugin_applicatifs_getTypes())
		&& $parm["type"]!=TRACKING_TYPE) { // TRACKING_TYPE handle in plugin_item_delete_applicatifs

		$PluginApplicatifs=new PluginApplicatifs;
		$PluginApplicatifs->cleanItems($parm["ID"],$parm["type"]);
		return true;
	}
	return false;
}

// Define headings added by the plugin
function plugin_get_headings_applicatifs($type,$ID,$withtemplate){

	global $LANG;

	if ($type==PROFILE_TYPE) {
		if ($ID>0) {
			return array(
				1 => $LANG['plugin_applicatifs']['title'][1],
				);
		}
	}

	else if (in_array($type, plugin_applicatifs_getTypes())) {
		if (!$withtemplate) {
		// Non template case
			return array(
				1 => $LANG['plugin_applicatifs']['title'][1],
				);
		}
	}
	return false;
}

// Define headings actions added by the plugin
function plugin_headings_actions_applicatifs($type){

	if (in_array($type,plugin_applicatifs_getTypes()) ||
		$type==PROFILE_TYPE) {
		return array(
				1 => "plugin_headings_applicatifs",
				);
	} else {
		return false;
	}
}

// applicatifs of an action heading
// Define headings actions added by the plugin
function plugin_headings_applicatifs($type,$ID){

	global $CFG_GLPI,$LANG;

		switch ($type){
			case PROFILE_TYPE :
				$prof=new PluginApplicatifsProfile();
				if (!$prof->GetfromDB($ID))
					plugin_applicatifs_createAccess($ID);
				$prof->showForm($CFG_GLPI["root_doc"]."/plugins/applicatifs/front/plugin_applicatifs.profile.php",$ID);
				break;
			default :
				if (in_array($type, plugin_applicatifs_getTypes())){
					echo "<div align='center'>";
					echo plugin_applicatifs_showAssociated($type,$ID);
					echo "</div>";
				}
			break;
		}

}

// Define PDF informations added by the plugin
function plugin_headings_actionpdf_applicatifs($type){

	if (in_array($type,plugin_applicatifs_getTypes())) {
		return array(
				1 => "plugin_headings_applicatifs_PDF",
				);
	} else {
		return false;
	}
}

// Genrerate PDF with informations added by the plugin
// Define headings actions added by the plugin
function plugin_headings_applicatifs_PDF($pdf,$ID,$type) {

	if (in_array($type, plugin_applicatifs_getTypes())){
		echo plugin_applicatifs_showAssociated_PDF($pdf,$ID,$type);
	}

}

function plugin_applicatifs_data_injection_variables() {
	global $IMPORT_PRIMARY_TYPES, $DATA_INJECTION_MAPPING, $LANG, $IMPORT_TYPES,$DATA_INJECTION_INFOS;

	 if (plugin_applicatifs_haveRight("applicatifs","w")){
		if (!in_array(PLUGIN_APPLICATIFS_TYPE, $IMPORT_PRIMARY_TYPES)) {

			//Add types of objects to be injected by data_injection plugin
			array_push($IMPORT_PRIMARY_TYPES, PLUGIN_APPLICATIFS_TYPE);

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['name']['table'] = 'glpi_plugin_applicatifs';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['name']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['name']['name'] = $LANG['plugin_applicatifs'][8];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['name']['type'] = "text";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['table'] = 'glpi_dropdown_plugin_applicatifs_type';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['linkfield'] = 'type';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['name'] = $LANG['plugin_applicatifs'][20];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['table_type'] = "dropdown";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['table'] = 'glpi_dropdown_plugin_applicatifs_environment';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['linkfield'] = 'environment';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['name'] = $LANG['plugin_applicatifs'][3];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['type']['table_type'] = "dropdown";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['table'] = 'glpi_users';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['linkfield'] = 'FK_users';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['name'] = $LANG['plugin_applicatifs'][21];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_users']['table_type'] = "user";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['table'] = 'glpi_groups';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['linkfield'] = 'FK_groups';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['name'] = $LANG['common'][35];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['table_type'] = "single";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['table'] = 'glpi_dropdown_locations';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['field'] = 'completename';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['linkfield'] = 'location';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['name'] = $LANG['plugin_applicatifs'][2];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['location']['table_type'] = "dropdown";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['comments']['table'] = 'glpi_plugin_applicatifs';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['comments']['field'] = 'comments';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['comments']['name'] = $LANG['plugin_applicatifs'][12];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['comments']['type'] = "text";

			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['recursive']['table'] = 'glpi_plugin_applicatifs';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['recursive']['field'] = 'recursive';
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['recursive']['name'] = $LANG["entity"][9];
			$DATA_INJECTION_MAPPING[PLUGIN_APPLICATIFS_TYPE]['recursive']['type'] = 'integer';

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['table'] = 'glpi_dropdown_plugin_applicatifs_type';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['field'] = 'name';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['linkfield'] = 'type';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['name'] = $LANG['plugin_applicatifs'][20];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['table_type'] = "dropdown";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['table'] = 'glpi_dropdown_plugin_applicatifs_environment';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['field'] = 'name';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['linkfield'] = 'environment';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['name'] = $LANG['plugin_applicatifs'][3];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['type']['table_type'] = "dropdown";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['table'] = 'glpi_users';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['field'] = 'name';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['linkfield'] = 'FK_users';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['name'] = $LANG['plugin_applicatifs'][21];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_users']['table_type'] = "user";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['table'] = 'glpi_groups';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['field'] = 'name';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['linkfield'] = 'FK_groups';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['name'] = $LANG['common'][35];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['FK_groups']['input_type'] = "single";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['table'] = 'glpi_dropdown_locations';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['field'] = 'completename';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['linkfield'] = 'location';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['name'] = $LANG['plugin_applicatifs'][2];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['location']['table_type'] = "dropdown";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['comments']['table'] = 'glpi_plugin_applicatifs';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['comments']['field'] = 'comments';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['comments']['name'] = $LANG['plugin_applicatifs'][12];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['comments']['type'] = "text";

			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['recursive']['table'] = 'glpi_plugin_applicatifs';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['recursive']['field'] = 'recursive';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['recursive']['name'] = $LANG["entity"][9];
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['recursive']['type'] = 'integer';
			$DATA_INJECTION_INFOS[PLUGIN_APPLICATIFS_TYPE]['recursive']['input_type'] = 'yesno';
		}
	}
}

/**
 * Hook : options for one type
 *
 * @param $type of item
 *
 * @return array of string which describe the options
 */
function plugin_applicatifs_prefPDF($type) {
	global $LANG;

	$tabs=array();
	switch ($type) {
		case PLUGIN_APPLICATIFS_TYPE:
			$item = new PluginApplicatifs();
			$tabs = $item->defineTabs(1,'');
			break;
	}
	return $tabs;
}

/**
 * Hook to generate a PDF for a type
 *
 * @param $type of item
 * @param $tab_id array of ID
 * @param $tab of option to be printed
 * @param $page boolean true for landscape
 */
function plugin_applicatifs_generatePDF($type, $tab_id, $tab, $page=0) {
	$pdf = new simplePDF('a4', ($page ? 'landscape' : 'portrait'));

	$nb_id = count($tab_id);

	foreach($tab_id as $key => $ID)	{

		if (plugin_pdf_add_header($pdf,$ID,$type)) {
			$pdf->newPage();
		} else {
			// Object not found or no right to read
			continue;
		}

	switch($type){
		case PLUGIN_APPLICATIFS_TYPE:
			plugin_applicatifs_main_PDF($pdf,$ID);

			foreach($tab as $i)	{
				switch($i) { // See plugin_applicatif::defineTabs();
					case 1:
						plugin_applicatifs_showDevice_PDF($pdf,$ID);
						break;
					case 6:
						plugin_pdf_ticket($pdf,$ID,$type);
						plugin_pdf_oldticket($pdf,$ID,$type);
						break;
					case 9:
						plugin_pdf_financial($pdf,$ID,$type);
						plugin_pdf_contract ($pdf,$ID,$type);
						break;
					case 10:
						plugin_pdf_document($pdf,$ID,$type);
						break;
					case 11:
						plugin_pdf_note($pdf,$ID,$type);
						break;
					case 12:
						plugin_pdf_history($pdf,$ID,$type);
						break;
					default:
						plugin_pdf_pluginhook($i,$pdf,$ID,$type);
				}
			}
			break;
		} // Switch type
	} // Each ID
	$pdf->render();
}

?>