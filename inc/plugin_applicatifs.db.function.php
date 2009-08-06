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

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

function plugin_applicatifs_addDevice($conID,$ID,$type){

	GLOBAL $DB;
	
	$query="INSERT INTO glpi_plugin_applicatifs_device (FK_applicatif,FK_device,device_type) 
			VALUES ('$conID','$ID','$type');";
	$result = $DB->query($query);
}

function plugin_applicatifs_deleteDevice($ID){

	GLOBAL $DB;

	// CNAMTS BEGIN
	// delete the data when applicatif unlinked from a machine
	$query_app_id = "SELECT FK_applicatif AS app, FK_device AS computer 
					FROM glpi_plugin_applicatifs_device 
					WHERE ID = '$ID'";
	$result_app_id = $DB->query($query_app_id);
	$data_app_id = $DB->fetch_array($result_app_id);
	$applicatif_id = $data_app_id['app'];
	$computer_id = $data_app_id['computer'];

	$sql="DELETE FROM glpi_plugin_applicatifs_optvalues_machines 
		WHERE optvalue_ID IN(
		SELECT ID 
		FROM glpi_plugin_applicatifs_optvalues 
		WHERE applicatif_ID='$applicatif_id') 
		AND machine_ID='$computer_id' ";
	$res = $DB->query($sql);
	// CNAMTS END
	
	// delete the relation if the device is deleted
	$sql="DELETE FROM glpi_plugin_applicatifs_relation
		WHERE FK_applicatifs_device='".$ID."' ";
	$res = $DB->query($sql);
	
	$query="DELETE FROM glpi_plugin_applicatifs_device 
			WHERE ID= '$ID';";
	$result = $DB->query($query);
}

function plugin_applicatifs_transferDropdown($ID,$entity){

	global $DB;
			
		if ($ID>0){
			/*if (isset($already_transfer['type'][$ID])){
				return $already_transfer['type'][$ID];
			} else { // Not already transfer*/
				// Search init item
				$query="SELECT * 
						FROM glpi_dropdown_plugin_applicatifs_type 
						WHERE ID='$ID'";
				if ($result=$DB->query($query)){
					if ($DB->numrows($result)){
						$data=$DB->fetch_array($result);
						$data=addslashes_deep($data);
						// Search if the location already exists in the destination entity
							$query="SELECT ID 
									FROM glpi_dropdown_plugin_applicatifs_type 
									WHERE FK_entities='".$entity."' 
									AND name='".$data['name']."'";
							if ($result_search=$DB->query($query)){
								// Found : -> use it
								if ($DB->numrows($result_search)>0){
									$newID=$DB->result($result_search,0,'ID');
									//$this->addToAlreadyTransfer('type',$ID,$newID);
									return $newID;
								}
							}
							// Not found : 
							$input=array();
							$input['tablename']='glpi_dropdown_plugin_applicatifs_type';
							$input['FK_entities']=$entity;
							$input['value']=$data['name'];
							$input['comments']=$data['comments'];
							$input['type']="under";
							$input['value2']=0; // parentID
							// if parentID>0 : transfer parent ID
							/*if ($data['parentID']>0){
								$input['value2']=$this->transferDropdownLocation($data['parentID']);
							}*/
							// add item
							$newID=addDropdown($input);
							//$this->addToAlreadyTransfer('type',$ID,$newID);
							return $newID;
					} 
				}
			//}
		}
		return 0;
	}

function plugin_applicatifs_addRelation($device,$relation){
	global $DB;

	// check if the relation already exists
	if (!countElementsInTable('glpi_plugin_applicatifs_relation', 
		"FK_applicatifs_device='$device' AND FK_relation='$relation'")) {

		$sql="INSERT INTO glpi_plugin_applicatifs_relation(FK_applicatifs_device,FK_relation)
			VALUES('$device','$relation')";
		$res = $DB->query($sql);		
	}			
}

function plugin_applicatifs_delRelation($ID){
	global $DB;

	$sql="DELETE FROM glpi_plugin_applicatifs_relation WHERE ID='$ID'";
	$res = $DB->query($sql);		
}	

function plugin_applicatifs_updateOptValues($input) {
	global $DB;

	$number_champs=$input["number_champs"];
	for ($i=1 ; $i<=$number_champs ; $i++){
	    $opt_id = "opt_id$i";
	    $vvalue = "vvalue$i";
	    $ddefault = "ddefault$i";

	    $query_app = "SELECT ID
						FROM glpi_plugin_applicatifs_optvalues_machines 
						WHERE optvalue_ID = '".$input[$opt_id]."'
						AND machine_ID = '".$input['item']."'";
	    $result_app = $DB->query($query_app);
		
	    if ($data=$DB->fetch_array($result_app)){
			// l'entrée existe déjà, il faut faire un update ou un delete
			if (empty($input[$vvalue]) || $input[$vvalue]==$input[$ddefault]) {
			  	// la valeur saisie est nulle ou a la valeur par défaut -> on fait un delete
			  	$query_app_del = "DELETE FROM glpi_plugin_applicatifs_optvalues_machines 
								WHERE ID='" . $data["ID"]."'"; 
			  	$result_app_del = $DB->query($query_app_del);
			} else {
			   	// la valeur saisie est non nulle -> on fait un update
			   	$query_app_upd = "UPDATE glpi_plugin_applicatifs_optvalues_machines
								SET vvalue='".$input[$vvalue]."' 
								WHERE ID='" . $data["ID"]."'";
			   	$result_app_upd = $DB->query($query_app_upd);
			}
	    } else {
		// l'entrée n'existe pas
			if (!empty($input[$vvalue]) && $input[$vvalue]!=$input[$ddefault]) {
			   	// et la valeur saisie est non nulle -> on fait un insert
			   	$query_app_ins = "INSERT INTO glpi_plugin_applicatifs_optvalues_machines(optvalue_ID,machine_ID,vvalue)
								VALUES('".$input[$opt_id]."','".$input['item']."','".$input[$vvalue]."')";
			   	$result_app_ins = $DB->query($query_app_ins);
			}
	    }
	} // For
}	
?>
