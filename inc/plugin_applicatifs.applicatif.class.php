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

class PluginApplicatifs extends CommonDBTM {

	function __construct () {
		$this->table="glpi_plugin_applicatifs";
		$this->type=PLUGIN_APPLICATIFS_TYPE;
		$this->entity_assign=true;
		$this->may_be_recursive=true;
		$this->dohistory=true;
	}
		
	function cleanDBonPurge($ID) {
		global $DB;

		foreach ($DB->request("glpi_plugin_applicatifs_device",array("FK_applicatif"=>$ID)) as $data) {
			plugin_applicatifs_deleteDevice($data["ID"]);
		}
		
		$query = "DELETE FROM glpi_doc_device 
					WHERE FK_device = '$ID' 
					AND device_type= '".PLUGIN_APPLICATIFS_TYPE."' ";
		$DB->query($query);

		$query = "DELETE FROM `glpi_plugin_applicatifs_optvalues` 
				WHERE (`applicatif_ID` = '$ID')";
		$DB->query($query);				

		$query = "DELETE FROM `glpi_contract_device`
				WHERE (FK_device = '$ID' AND device_type='".PLUGIN_APPLICATIFS_TYPE."')";
		$DB->query($query);

		$query = "DELETE FROM `glpi_infocoms`
				WHERE (FK_device = '$ID' AND device_type='".PLUGIN_APPLICATIFS_TYPE."')";
		$result = $DB->query($query);
	}

	function cleanItems($ID,$type) {
	
		global $DB;
		
		$query = "DELETE FROM glpi_plugin_applicatifs_device 
					WHERE FK_device = '$ID' 
					AND device_type= '$type'";
		$DB->query($query);
	}
	
	function defineTabs($ID,$withtemplate){
		global $LANG;
		$ong[1]=$LANG['title'][26];
		if ($ID > 0){
			if (haveRight("show_all_ticket","1")) {
				$ong[6]=$LANG['title'][28];
			}
			if (haveRight("contract","r") || haveRight("infocom","r")) {
				$ong[9]=$LANG['Menu'][26];
			}
			if (haveRight("document","r")) {
				$ong[10]=$LANG['Menu'][27];
			}
			if (haveRight("notes","r")) {
				$ong[11]=$LANG['title'][37];
			}	
			$ong[12]=$LANG['title'][38];
		}
		return $ong;
	}

	function showForm ($target,$ID,$withtemplate='') {

		GLOBAL $CFG_GLPI, $LANG;

		GLOBAL $DB;

		if (!plugin_applicatifs_haveRight("applicatifs","r")) return false;
		
		$spotted = false;

		if ($ID>0){
			if($this->can($ID,'r')){
				$spotted = true;
			}
		}else{
			if($this->can(-1,'w')){
				$spotted = true;
				$this->getEmpty();
			}
		}
		
				
		if ($spotted){
			
			$this->showTabs($ID, $withtemplate,$_SESSION['glpi_tab']);
			
			$canedit=$this->can($ID,'w');
			$canrecu=$this->can($ID,'recursive');
			
			echo "<form method='post' name=form action=\"$target\">";		
			if (empty($ID)||$ID<0){
					echo "<input type='hidden' name='FK_entities' value='".$_SESSION["glpiactive_entity"]."'>";
				}
			echo "<div class='center' id='tabsbody'>";
			echo "<table class='tab_cadre_fixe'>";
			
			$this->showFormHeader($ID,'',2);

			echo "<tr><td class='tab_bg_1' valign='top'>";

			echo "<table cellpadding='2' cellspacing='2' border='0'>\n";

			echo "<tr><td>".$LANG['plugin_applicatifs'][8].":	</td>";
			echo "<td>";
			autocompletionTextField("name","glpi_plugin_applicatifs","name",$this->fields["name"],20,$this->fields["FK_entities"]);		
			echo "</td></tr>";
			
			echo "<tr><td>".$LANG['plugin_applicatifs'][3].":      </td><td>";
			dropdownValue("glpi_dropdown_plugin_applicatifs_environment", "environment", $this->fields["environment"]);
			echo "</td></tr>";
	
			echo "<tr><td>".$LANG['plugin_applicatifs'][20].":	</td><td>";
			dropdownValue("glpi_dropdown_plugin_applicatifs_type", "type", $this->fields["type"],1,$this->fields["FK_entities"]);
			echo "</td></tr>";

			echo "<tr><td>".$LANG['plugin_applicatifs'][21].":	</td><td>";
			if ($canedit) {
				dropdownAllUsers("FK_users", $this->fields["FK_users"],1,$this->fields["FK_entities"]);	
			} else {
				echo getUsername($this->fields["FK_users"]);
			}
			echo "</td></tr>";
			
			echo "<tr><td>".$LANG['common'][35].":	</td><td>";
			if ($canedit){
				dropdownValue("glpi_groups", "FK_groups", $this->fields["FK_groups"],1,$this->fields["FK_entities"]);
			}else{
				echo getdropdownname("glpi_groups", $this->fields["FK_groups"]);
			}
			echo "</td></tr>";
			
			echo "<tr><td>".$LANG['plugin_applicatifs'][2].":	</td><td>";
			if ($canedit) {
				dropdownValue("glpi_dropdown_locations", "location", $this->fields["location"],1,$this->fields["FK_entities"]);
			} else {
				echo getdropdownname("glpi_dropdown_locations",$this->fields["location"]);
			}
			
			echo "</td></tr>";
			
			echo "<tr><td>" . $LANG['software'][46] . ":</td><td>";
			dropdownYesNo('helpdesk_visible',$this->fields['helpdesk_visible']);
			echo "</td></tr>";
			
			// dropdown relationtype added
			echo "<tr><td>".$LANG['plugin_applicatifs'][22].":  </td><td>";
			if ($canedit && !($ID && countElementsInTable("glpi_plugin_applicatifs_relation, glpi_plugin_applicatifs_device", 
				"glpi_plugin_applicatifs_relation.FK_applicatifs_device=glpi_plugin_applicatifs_device.ID AND glpi_plugin_applicatifs_device.FK_applicatif=$ID"))) {
				plugin_applicatifs_dropdownrelationtype("relationtype",$this->fields["relationtype"]);
			} else {
				echo plugin_applicatifs_getrelationtypename($this->fields["relationtype"]);
				$rand=mt_rand();
				$comment=$LANG['common'][84];
				$image="/pics/lock.png";
				echo "&nbsp;<img alt='' src='".$CFG_GLPI["root_doc"].$image."' onmouseout=\"cleanhide('comments_relationtype$rand')\" onmouseover=\"cleandisplay('comments_relationtype$rand')\">";
				echo "<span class='over_link' id='comments_relationtype$rand'>$comment</span>";
			}
			//dropdownValue("glpi_dropdown_plugin_applicatifs_relationtype", "relationtype",$this->fields["relationtype"],1);
			echo "</td></tr>";

			echo "</table>";
			echo "</td>";

			if($ID){	
				echo "<td class='tab_bg_1' valign='top'>";
				echo "<table cellpadding='2' cellspacing='2' border='0'>";
				
				echo "<tr><td>".$LANG['plugin_applicatifs'][24]."</td></tr>\n";
				echo "<tr><td>".$LANG['plugin_applicatifs'][25]."</td>\n";
				echo "<td>".$LANG['plugin_applicatifs'][26]."</td></tr>\n";
	
				$query_app = "SELECT champ, ddefault 
							FROM glpi_plugin_applicatifs_optvalues 
							WHERE applicatif_ID = '".$ID."' 
							ORDER BY vvalues";
				$result_app = $DB->query($query_app);
				$number_champs = $DB->numrows($result_app);
				$number_champs++;
				echo "<input type='hidden' name='number_champs' value='$number_champs'>\n";
				$i=1;
				while ($i<=$number_champs) {
				  if ($data=$DB->fetch_array($result_app)){
					 $champ=$data["champ"];
					 $ddefault=$data["ddefault"];
				  } else {
					 $champ='';
					 $ddefault='';
				  }
					echo "<tr>\n<td><input type='text' name='champ$i' value='$champ'></td>\n<td>\n<input type='text' name='ddefault$i' value='$ddefault'></td></tr>\n";
				  $i=$i+1;
				}
	
				echo "</td>";
				echo "</tr>";
				echo "</table>";
				
				echo "</td>";	
			
				echo "<td colspan='2' class='tab_bg_1' valign='top'>";
			}else{
				echo "<td colspan='3' class='tab_bg_1' valign='top'>";
			}

			echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>\n";
			echo $LANG['plugin_applicatifs'][12].":	</td></tr>\n";
			echo "<tr><td align='center'><textarea cols='35' rows='4' name='comments' >".$this->fields["comments"]."</textarea><br><br>\n";
			$datestring = $LANG['common'][26].": ";
			$date = convDateTime($this->fields["date_mod"]);
			
			echo $datestring.$date."</td></tr></table>\n";

			echo "</td>";
			echo "</tr>";

			if ($canedit) {
			
				if (empty($ID)||$ID<0){
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='4'>";
					echo "<div align='center'><input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" class='submit'></div>";
					echo "</td>";
					echo "</tr>";
	
				} else {
	
					echo "<tr>";
					echo "<td class='tab_bg_2'  colspan='4' valign='top'><div align='center'>";
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >";
					if ($this->fields["deleted"]=='0'){
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"".$LANG['buttons'][6]."\" class='submit'></div>";
					}else {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='restore' value=\"".$LANG['buttons'][21]."\" class='submit'>";

						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"".$LANG['buttons'][22]."\" class='submit'></div>";
					}
					echo "</td>";
					echo "</tr>";
				}	
			}
			echo "</table></div></form>";
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";
			
		} else {
			echo "<div align='center'><b>".$LANG['plugin_applicatifs'][4]."</b></div>";
			return false;

		}
		return true;
	}

	function post_updateItem($input,$updates,$history=1) {
		global $DB;

		$number_champs=$input["number_champs"];

		for ($i=1 ; $i<=$number_champs ; $i++){
		  	$champ = "champ$i";
		  	$ddefault = "ddefault$i";
	
		  	$query_app = "SELECT champ 
						FROM glpi_plugin_applicatifs_optvalues 
						WHERE applicatif_ID = '".$input["ID"]."' AND vvalues = '".$i."'";
		  	$result_app = $DB->query($query_app);
		  	if ($data=$DB->fetch_array($result_app)){
		     	// l'entrée existe déjà, il faut faire un update ou un delete
		    	if (empty($input[$champ])){
					// la valeur saisie est nulle -> on fait un delete
					$query_app_del = "DELETE FROM glpi_plugin_applicatifs_optvalues 
							WHERE applicatif_ID = '".$input["ID"]."' AND vvalues = '".$i."'";
					$result_app_del = $DB->query($query_app_del);
		     	} else {
					// la valeur saisie est non nulle -> on fait un update
					$query_app_upd = "UPDATE glpi_plugin_applicatifs_optvalues 
							SET champ = '".$input[$champ]."', ddefault = '".$input[$ddefault]."' 
							WHERE applicatif_ID = '".$input["ID"]."' 
							AND vvalues = '".$i."'";
					$result_app_upd = $DB->query($query_app_upd);
		     	}
		  	} else {
				// l'entrée n'existe pas
				if (!empty($input[$champ])){
			   		// et la valeur saisie est non nulle -> on fait un insert
	                $query_app_ins = "INSERT INTO glpi_plugin_applicatifs_optvalues(applicatif_ID,vvalues,champ,ddefault) 
										VALUES('".$input["ID"]."',$i,'".$input[$champ]."','".$input[$ddefault]."')";
			   		$result_app_ins = $DB->query($query_app_ins);
				}
		  	}
		} // for
	} 
}

?>