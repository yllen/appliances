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

/** 
 * Show the Device associated with an applicatif
 * 
 * Called from the applicatif form
 * 
 * @param $instID ID of the applicatif
 * 
 */
function plugin_applicatifs_showDevice($instID) {
	global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE;

	if (!plugin_applicatifs_haveRight("applicatifs","r"))	return false;
	$rand=mt_rand();
	$PluginApplicatifs=new PluginApplicatifs();
	if ($PluginApplicatifs->getFromDB($instID)){
		
		$canedit=$PluginApplicatifs->can($instID,'w');
		
		$query = "SELECT DISTINCT device_type 
					FROM glpi_plugin_applicatifs_device 
					WHERE FK_applicatif = '$instID' 
					ORDER BY device_type";
		$result = $DB->query($query);
		$number = $DB->numrows($result);

		$i = 0;
		
		if (isMultiEntitiesMode()) {
			$colsup=1;
		}else {
			$colsup=0;
		}
		
		echo "<form method='post' name='applicatifs_form$rand' id='applicatifs_form$rand'  action=\"".$CFG_GLPI["root_doc"]."/plugins/applicatifs/front/plugin_applicatifs.form.php\">";
	
		echo "<div class='center'><table class='tab_cadrehov'>";
		echo "<tr><th colspan='".($canedit?(6+$colsup):(5+$colsup))."'>".$LANG['plugin_applicatifs'][7].":</th></tr><tr>";
		if ($canedit) {
			echo "<th>&nbsp;</th>";
		}
		echo "<th>".$LANG['common'][17]."</th>";
		echo "<th>".$LANG['common'][16]."</th>";
		if (isMultiEntitiesMode())
			echo "<th>".$LANG['entity'][0]."</th>";
		if ($PluginApplicatifs->fields["relationtype"]) {
			echo "<th>".$LANG['plugin_applicatifs'][22].
				"<br>".$LANG['plugin_applicatifs'][24]."</th>";
		}
		echo "<th>".$LANG['common'][19]."</th>";
		echo "<th>".$LANG['common'][20]."</th>";
		echo "</tr>";
	
		$ci=new CommonItem();
		while ($i < $number) {
			$type=$DB->result($result, $i, "device_type");
			if (haveTypeRight($type,"r")){
				$column="name";
				if ($type==TRACKING_TYPE) $column="ID";
				if ($type==KNOWBASE_TYPE) $column="question";

				$query = "SELECT ".$LINK_ID_TABLE[$type].".*, glpi_plugin_applicatifs_device.ID AS IDD, glpi_entities.ID AS entity "
				." FROM glpi_plugin_applicatifs_device, ".$LINK_ID_TABLE[$type]
				." LEFT JOIN glpi_entities ON (glpi_entities.ID=".$LINK_ID_TABLE[$type].".FK_entities) "
				." WHERE ".$LINK_ID_TABLE[$type].".ID = glpi_plugin_applicatifs_device.FK_device 
					AND glpi_plugin_applicatifs_device.device_type='$type' 
					AND glpi_plugin_applicatifs_device.FK_applicatif = '$instID' "
				. getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',isset($CFG_GLPI["recursive_type"][$type])); 

				if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
					$query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
				}
				$query.=" ORDER BY glpi_entities.completename, ".$LINK_ID_TABLE[$type].".$column";
				
				
				if ($result_linked=$DB->query($query))
					if ($DB->numrows($result_linked)){
						initNavigateListItems($type,$LANG['plugin_applicatifs']['title'][1]." = ".$PluginApplicatifs->fields['name']);
						
						while ($data=$DB->fetch_assoc($result_linked)){
							$ci->getFromDB($type,$data["ID"]);
							addToNavigateListItems($type,$data["ID"]);
							
							$ID="";
							if ($type==TRACKING_TYPE) $data["name"]=$LANG['job'][38]." ".$data["ID"];
							if ($type==KNOWBASE_TYPE) $data["name"]=$data["question"];
							
							if($_SESSION["glpiview_ID"]||empty($data["name"])) $ID= " (".$data["ID"].")";
							$name= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type]."?ID=".$data["ID"]."\">"
								.$data["name"]."$ID</a>";
	
							echo "<tr class='tab_bg_1'>";

							if ($canedit){
								echo "<td width='10'>";
								$sel="";
								if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
								echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
								echo "</td>";
							}
							echo "<td class='center'>".$ci->getType()."</td>";
							
							echo "<td class='center' ".(isset($data['deleted'])&&$data['deleted']?"class='tab_bg_2_2'":"").">".$name."</td>";
							if (isMultiEntitiesMode()) {
								echo "<td class='center'>".getDropdownName("glpi_entities",$data['entity'])."</td>";
							}

							if ($PluginApplicatifs->fields["relationtype"]) {
								echo "<td align='center'>". plugin_applicatifs_getrelationtypename($PluginApplicatifs->fields["relationtype"]).":&nbsp;";		
								plugin_applicatifs_showRelation($PluginApplicatifs->fields["relationtype"], $data["IDD"], $ci->obj->fields["FK_entities"], false);
								plugin_applicatifs_showOptions($data["ID"], $instID, false);
								echo "</td>";
							}

							echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
							echo "<td class='center'>".(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
							
							echo "</tr>";
						}
					}
			}
			$i++;
		}
	
		if ($canedit)	{
			echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";
	
			echo "<input type='hidden' name='conID' value='$instID'>";			
			dropdownAllItems("item",0,0,($PluginApplicatifs->fields['recursive']?-1:$PluginApplicatifs->fields['FK_entities']),plugin_applicatifs_getTypes());
			echo "</td>";
			echo "<td colspan='3' class='center' class='tab_bg_2'>";
			echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
			echo "</td></tr>";
			echo "</table></div>" ;
			
			echo "<div class='center'>";
			echo "<table width='80%' class='tab_glpi'>";
			echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('applicatifs_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$instID&amp;select=all'>".$LANG['buttons'][18]."</a></td>";
		
			echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('applicatifs_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$instID&amp;select=none'>".$LANG['buttons'][19]."</a>";
			echo "</td><td align='left' width='80%'>";
			echo "<input type='submit' name='deleteitem' value=\"".$LANG['buttons'][6]."\" class='submit'>";
			echo "</td>";
			echo "</table>";
		
			echo "</div>";


		}else{
	
			echo "</table></div>";
		}
		echo "</form>";
	}

}

function plugin_applicatifs_showDevice_PDF($pdf, $instID) {
	global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE;

	if (!plugin_applicatifs_haveRight("applicatifs","r"))	return false;

	$PluginApplicatifs=new PluginApplicatifs();
	if (!$PluginApplicatifs->getFromDB($instID)) return false;

	$pdf->setColumnsSize(100);
	$pdf->displayTitle('<b>'.$LANG['plugin_applicatifs'][7].'</b>');
		
	$query = "SELECT DISTINCT device_type 
				FROM glpi_plugin_applicatifs_device 
				WHERE FK_applicatif = '$instID' 
				ORDER BY device_type";
	$result = $DB->query($query);
	$number = $DB->numrows($result);

	if (isMultiEntitiesMode()) {
		$pdf->setColumnsSize(12,27,25,18,18);
		$pdf->displayTitle(
			'<b><i>'.$LANG['common'][17],
			$LANG['common'][16],
			$LANG['entity'][0],
			$LANG['common'][19],
			$LANG['common'][20].'</i></b>'
			);
	} else {
		$pdf->setColumnsSize(25,31,22,22);
		$pdf->displayTitle(
			'<b><i>'.$LANG['common'][17],
			$LANG['common'][16],
			$LANG['common'][19],
			$LANG['common'][20].'</i></b>'
			);
	}

	$ci=new CommonItem();
	if (!$number) {
		$pdf->displayLine($LANG['search'][15]);						
	} else { 
		for ($i=0 ; $i < $number ; $i++) {
			$type=$DB->result($result, $i, "device_type");

			if (haveTypeRight($type,"r")){
				$column="name";
				if ($type==TRACKING_TYPE) $column="ID";
				if ($type==KNOWBASE_TYPE) $column="question";

				$query = "SELECT ".$LINK_ID_TABLE[$type].".*, glpi_plugin_applicatifs_device.ID AS IDD, glpi_entities.ID AS entity "
				." FROM glpi_plugin_applicatifs_device, ".$LINK_ID_TABLE[$type]
				." LEFT JOIN glpi_entities ON (glpi_entities.ID=".$LINK_ID_TABLE[$type].".FK_entities) "
				." WHERE ".$LINK_ID_TABLE[$type].".ID = glpi_plugin_applicatifs_device.FK_device 
					AND glpi_plugin_applicatifs_device.device_type='$type' 
					AND glpi_plugin_applicatifs_device.FK_applicatif = '$instID' "
				. getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',isset($CFG_GLPI["recursive_type"][$type])); 

				if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
					$query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
				}
				$query.=" ORDER BY glpi_entities.completename, ".$LINK_ID_TABLE[$type].".$column";
				
				if ($result_linked=$DB->query($query))
					if ($DB->numrows($result_linked)){
						
						while ($data=$DB->fetch_assoc($result_linked)){
							if (!$ci->getFromDB($type,$data["ID"])) continue;
							
							$ID="";
							if ($type==TRACKING_TYPE) $data["name"]=$LANG['job'][38]." ".$data["ID"];
							if ($type==KNOWBASE_TYPE) $data["name"]=$data["question"];
							
							if($_SESSION["glpiview_ID"]||empty($data["name"])) $ID= " (".$data["ID"].")";
							$name = $data["name"].$ID;
	
							if (isMultiEntitiesMode()) {
								$pdf->setColumnsSize(12,27,25,18,18);
								$pdf->displayLine(
									$ci->getType(),
									$name,
									getDropdownName("glpi_entities",$data['entity']),
									(isset($data["serial"])? "".$data["serial"]."" :"-"),
									(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")
									);
							} else {
								$pdf->setColumnsSize(25,31,22,22);
								$pdf->displayTitle(
									$ci->getType(),
									$name,
									(isset($data["serial"])? "".$data["serial"]."" :"-"),
									(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")
									);
							}
							
							plugin_applicatifs_showRelation_PDF($pdf,$PluginApplicatifs->fields["relationtype"], $data["IDD"]);
							plugin_applicatifs_showOptions_PDF($pdf,$data["ID"], $instID);
							
						} // Each device
					} // numrows device
			} // type right
		} // each type
	} // numrows type
}

function plugin_applicatifs_showTickets($ID) {
	global $DB, $LANG, $LINK_ID_TABLE, $INFOFORM_PAGES;
	
	$PluginApplicatifs=new PluginApplicatifs();
	if ($PluginApplicatifs->getFromDB($ID)){
		echo "<div class='center'><br><table class='tab_cadre_fixe'>";
		echo "<tr><th colspan='10'>".$LANG['plugin_applicatifs'][30].":</th></tr>\n";
		commonTrackingListHeader(HTML_OUTPUT,$_SERVER['PHP_SELF'],"ID=$ID","","",true);
		initNavigateListItems(TRACKING_TYPE,$LANG['plugin_applicatifs']['title'][1]." = ".$PluginApplicatifs->fields['name']);
		
		$sql = "SELECT DISTINCT device_type FROM glpi_plugin_applicatifs_device	WHERE FK_applicatif = '$ID'";
		$nb=0;
		foreach ($DB->request($sql) as $data) {
			$type=$data['device_type'];
			if (!haveTypeRight($type,"r")) {
				continue;
			}
			if ($type==TRACKING_TYPE) {
				continue;
			}
			$table=$LINK_ID_TABLE[$type];
			$sql = "SELECT ".getCommonSelectForTrackingSearch().		
				" FROM glpi_tracking
				LEFT JOIN $table ON (glpi_tracking.computer=$table.ID)".
				getCommonLeftJoinForTrackingSearch().		
				"WHERE   glpi_tracking.device_type=$type
					AND glpi_tracking.computer IN 
						(SELECT DISTINCT FK_device FROM glpi_plugin_applicatifs_device 
						 WHERE device_type=$type AND FK_applicatif=$ID)		
					AND glpi_tracking.status IN ('new','assign','plan','waiting')".
					getEntitiesRestrictRequest(" AND ",'glpi_tracking'); 		
			
			foreach ($DB->request($sql) AS $data) {
				addToNavigateListItems(TRACKING_TYPE,$data["ID"]);
				showJobShort($data, 0);
				$nb++;
			} // each ticket		
		} // each type

		if (!$nb) {
			echo "<tr class='tab_bg_1'><td colspan='10' class='center'>".$LANG['joblist'][8]."</td></tr>\n";
		}
		echo "</table></div>";
	}
}
/** 
 * Show the applicatif associated with a device
 * 
 * Called from the device form (applicatif tab)
 * 
 * @param $device_type : type of the device
 * @param $ID of the device
 * @param $withtemplate : not used, always empty
 * 
 */
function plugin_applicatifs_showAssociated($device_type,$ID,$withtemplate=''){

	GLOBAL $DB,$CFG_GLPI, $LANG;
	
	$ci=new CommonItem(); 
	$ci->getFromDB($device_type,$ID); 
	$canread=$ci->obj->can($ID,'r'); 
	$canedit=$ci->obj->can($ID,'w');
	
	$query = "SELECT glpi_plugin_applicatifs_device.ID AS entID,glpi_plugin_applicatifs.* "
	." FROM glpi_plugin_applicatifs_device,glpi_plugin_applicatifs "
	." LEFT JOIN glpi_entities ON (glpi_entities.ID=glpi_plugin_applicatifs.FK_entities) "
	." WHERE glpi_plugin_applicatifs_device.FK_device = '".$ID."' 
		AND glpi_plugin_applicatifs_device.device_type = '".$device_type."' 
		AND glpi_plugin_applicatifs_device.FK_applicatif=glpi_plugin_applicatifs.ID "
	. getEntitiesRestrictRequest(" AND ","glpi_plugin_applicatifs",'','',isset($CFG_GLPI["recursive_type"][PLUGIN_APPLICATIFS_TYPE]));
	
	$result = $DB->query($query);
	$number = $DB->numrows($result);

	//if ($withtemplate!=2) echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/applicatifs/front/plugin_applicatifs.form.php\">";

	$query_app = "SELECT ID 
					FROM glpi_plugin_applicatifs_device "
					."WHERE FK_device = '".$ID."'";
	$result_app = $DB->query($query_app);
	$number_app = $DB->numrows($result_app);

	if ($number_app>0) {
		$colsup=1;
	} else {
		$colsup=0;
	}
	
	if (isMultiEntitiesMode()) {
		$colsup+=1;
	}
	
	echo "<div align='center'><table class='tab_cadre_fixe'>";
	echo "<tr><th colspan='".(5+$colsup)."'>".$LANG['plugin_applicatifs'][9].":</th></tr>";
	echo "<tr><th>".$LANG['plugin_applicatifs'][8]."</th>";
	if (isMultiEntitiesMode())
		echo "<th>".$LANG['entity'][0]."</th>";
	echo "<th>".$LANG['common'][35]."</th>";
	echo "<th>".$LANG['plugin_applicatifs'][20]."</th>";
	if ($number_app>0) {
		echo "<th>".$LANG['plugin_applicatifs'][22]."</th>";
	}
	echo "<th>".$LANG['plugin_applicatifs'][12]."<br>".$LANG['plugin_applicatifs'][24]."</th>";

	if($canedit){
		if ($withtemplate<2)echo "<th>&nbsp;</th>";
	}
	echo "</tr>";
	$used=array();
	while ($data=$DB->fetch_array($result)){
		$applicatifsID=$data["ID"];
		$used[]=$applicatifsID;


		echo "<tr class='tab_bg_1".($data["deleted"]=='1'?"_2":"")."'>";
		if ($withtemplate!=3 && $canread && (in_array($data['FK_entities'],$_SESSION['glpiactiveentities']) || $data["recursive"])){
			echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/applicatifs/front/plugin_applicatifs.form.php?ID=".$data["ID"]."'>".$data["name"];
			if ($_SESSION["glpiview_ID"]) echo " (".$data["ID"].")";
			echo "</a></td>";
		} else {
			echo "<td class='center'>".$data["name"];
			if ($_SESSION["glpiview_ID"]) echo " (".$data["ID"].")";
			echo "</td>";
		}
		if ($_SESSION["glpiview_ID"]) echo " (".$data["ID"].")";
		echo "</b></a></td>";
		if (isMultiEntitiesMode())
			echo "<td class='center'>".getDropdownName("glpi_entities",$data['FK_entities'])."</td>";
		
		echo "<td align='center'>".getdropdownname("glpi_groups",$data["FK_groups"])."</td>";

		echo "<td align='center'>".getdropdownname("glpi_dropdown_plugin_applicatifs_type",$data["type"])."</td>";
		if ($number_app>0) {

		// add or delete a relation to an applicatifs
		echo "<td align='center'>";
		plugin_applicatifs_showRelation ($data["relationtype"], $data["entID"],$ci->obj->fields["FK_entities"],$canedit);
		echo "</td>";

		}

		echo "<td align='center'>".$data["comments"];
		plugin_applicatifs_showOptions($ID, $applicatifsID, $canedit);
		echo "</td>";


		if ($canedit) {
			echo "<td align='center' class='tab_bg_2'><a href='".$CFG_GLPI["root_doc"]."/plugins/applicatifs/front/plugin_applicatifs.form.php?deleteapplicatifs=deleteapplicatifs&amp;ID=".$data["entID"]."'><b>".$LANG['buttons'][6]."</b></a></td>";
		}
		echo "</tr>";


	}

	if ($canedit){
		
		$ci=new CommonItem();
		$entities=""; 
		if ($ci->getFromDB($device_type,$ID) && isset($ci->obj->fields["FK_entities"])) {                
		
			if (isset($ci->obj->fields["recursive"]) && $ci->obj->fields["recursive"]) { 
				$entities = getEntitySons($ci->obj->fields["FK_entities"]); 
			} else { 
				$entities = $ci->obj->fields["FK_entities"]; 
			} 
		} 
		$limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_applicatifs",'',$entities,true);
		
		$q="SELECT count(*) 
			FROM glpi_plugin_applicatifs 
			WHERE deleted='0' $limit";
		$result = $DB->query($q);
		$nb = $DB->result($result,0,0);

		if ($withtemplate<2&&$nb>count($used)){
			//if(plugin_applicatifs_haveRight("applicatifs","w")){
				echo "<tr class='tab_bg_1'>";
				echo "<td align='right' colspan=5>";

				// needed to use the button "additem"
				echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/applicatifs/front/plugin_applicatifs.form.php\">";
				echo "<input type='hidden' name='item' value='$ID'><input type='hidden' name='type' value='$device_type'>";
				plugin_applicatifs_dropdownapplicatifs("conID",$entities,$used);

				echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
				echo "</form>";

				echo "</td>";
				//if ($number_app>0)
				echo "<td align='right' colspan='".($colsup)."'></td>";
	
				echo "</tr>";
			//}
		}
	}

	echo "</table></div>";

}

/** 
 * show for PDF the applicatif associated with a device
 * 
 * @param $ID of the device
 * @param $device_type : type of the device
 * 
 */
function plugin_applicatifs_showAssociated_PDF($pdf, $ID, $device_type){

	GLOBAL $DB,$CFG_GLPI, $LANG;
	
	$pdf->setColumnsSize(100);
	$pdf->displayTitle('<b>'.$LANG['plugin_applicatifs'][9].'</b>');

	$ci=new CommonItem(); 
	$ci->getFromDB($device_type,$ID); 
	
	$query = "SELECT glpi_plugin_applicatifs_device.ID AS entID,glpi_plugin_applicatifs.* "
	." FROM glpi_plugin_applicatifs_device,glpi_plugin_applicatifs "
	." LEFT JOIN glpi_entities ON (glpi_entities.ID=glpi_plugin_applicatifs.FK_entities) "
	." WHERE glpi_plugin_applicatifs_device.FK_device = '".$ID."' 
		AND glpi_plugin_applicatifs_device.device_type = '".$device_type."' 
		AND glpi_plugin_applicatifs_device.FK_applicatif=glpi_plugin_applicatifs.ID "
	. getEntitiesRestrictRequest(" AND ","glpi_plugin_applicatifs",'','',isset($CFG_GLPI["recursive_type"][PLUGIN_APPLICATIFS_TYPE]));
	
	$result = $DB->query($query);
	$number = $DB->numrows($result);

	if (!$number) {
		$pdf->displayLine($LANG['search'][15]);				
	} else {
		if (isMultiEntitiesMode()) {
			$pdf->setColumnsSize(30,30,20,20);
			$pdf->displayTitle(
				'<b><i>'.$LANG['plugin_applicatifs'][8],
				$LANG['entity'][0],
				$LANG['common'][35],
				$LANG['plugin_applicatifs'][20].'</i></b>'
				);
		} else {
			$pdf->setColumnsSize(50,25,25);
			$pdf->displayTitle(
				'<b><i>'.$LANG['plugin_applicatifs'][8],
				$LANG['common'][35],
				$LANG['plugin_applicatifs'][20].'</i></b>'
				);
		}
		while ($data=$DB->fetch_array($result)){
			$applicatifsID=$data["ID"];
	
			if (isMultiEntitiesMode()) {
				$pdf->setColumnsSize(30,30,20,20);
				$pdf->displayLine(
					$data["name"],
					html_clean(getDropdownName("glpi_entities",$data['FK_entities'])),
					html_clean(getDropdownName("glpi_groups",$data["FK_groups"])),
					html_clean(getDropdownName("glpi_dropdown_plugin_applicatifs_type",$data["type"]))
					);
			} else {
				$pdf->setColumnsSize(50,25,25);
				$pdf->displayLine(
					$data["name"],
					html_clean(getDropdownName("glpi_groups",$data["FK_groups"])),
					html_clean(getDropdownName("glpi_dropdown_plugin_applicatifs_type",$data["type"]))
					);
			}
			plugin_applicatifs_showRelation_PDF($pdf,$data["relationtype"], $data["entID"]);			
			plugin_applicatifs_showOptions_PDF($pdf,$ID, $applicatifsID);
		}		
	}

}

/** 
 * Show the relation for a device/applicatif
 * 
 * Called from plugin_applicatifs_showDevice and plugin_applicatifs_showAssociated
 * 
 * @param $drelation_type : type of the relation
 * @param $relID ID of the relation
 * @param $entity, ID of the entity of the device
 * @param $canedit, if user is allowed to edit the relation
 * 	- canedit the device if called from the device form
 * 	- must be false if called from the applicatif form
 * 
 */
function plugin_applicatifs_showRelation ($relationtype, $relID, $entity, $canedit) {

	GLOBAL $DB,$CFG_GLPI, $LANG;

	if (!$relationtype) return false;
	
	// selects all the attached relations
	$tablename=plugin_applicatifs_getrelationtypetable($relationtype);
	$title=plugin_applicatifs_getrelationtypename($relationtype);

	if (in_array($tablename,$CFG_GLPI["dropdowntree_tables"])) {
		$sql_loc = "SELECT r.ID, completename AS dispname ";
	} else {
		$sql_loc = "SELECT r.ID, name AS dispname ";
	}
	$sql_loc.= "FROM `".$tablename."` l,glpi_plugin_applicatifs_relation r, glpi_plugin_applicatifs_device d " .
			" WHERE	l.ID=r.FK_relation 
			AND r.FK_applicatifs_device=d.ID 
			AND d.ID='$relID' ";
	$result_loc = $DB->query($sql_loc);
	$number_loc = $DB->numrows($result_loc);

	if ($canedit) {

		echo "<form method='post' name='relation' action='".$CFG_GLPI["root_doc"]."/plugins/applicatifs/front/plugin_applicatifs.form.php'>";	
		echo "<br><input type='hidden' name='deviceID' value='$relID'>";
	
		$i=0;
		$itemlist="";
		$used=array();
	
		if ($number_loc>0){
			echo "<table>";
			while ($i<$number_loc){
				$res=$DB->fetch_array($result_loc);		
				echo "<tr><td valign=top>";
				// when the value of the checkbox is changed, the corresponding hidden variable value
				// is also changed by javascript
				echo "<input type='checkbox' name='itemrelation[" . $res["ID"] . "]' value='1'></td><td>";
				echo $res["dispname"];
				echo "</td></tr>";
				$i++;
			}
			echo "</table>";
		   	echo "<input type='submit' name='dellieu' value='".$LANG['buttons'][6]."' class='submit'><br><br>";
		}
	
		echo "$title&nbsp;:&nbsp;";

		dropdownValue($tablename,"tablekey[" . $relID . "]","",1,$entity,"",$used);
		echo "&nbsp;&nbsp;&nbsp;<input type='submit' name='addlieu' value=\"".$LANG['buttons'][8]."\" class='submit'><br>&nbsp;";

		echo "</form>";
	} else if ($number_loc>0) {
		while ($res=$DB->fetch_array($result_loc)){
			echo $res["dispname"]."<br>";
		}
	} else {
		echo "&nbsp;";
	}
}

/** 
 * Show for PDF the relation for a device/applicatif
 * 
 * @param $pdf object for the output
 * @param $drelation_type : type of the relation
 * @param $relID ID of the relation
 * 
 */
function plugin_applicatifs_showRelation_PDF ($pdf, $relationtype, $relID) {

	GLOBAL $DB,$CFG_GLPI, $LANG;

	if (!$relationtype) return false;
	
	// selects all the attached relations
	$tablename=plugin_applicatifs_getrelationtypetable($relationtype);
	$title=plugin_applicatifs_getrelationtypename($relationtype);

	if (in_array($tablename,$CFG_GLPI["dropdowntree_tables"])) {
		$sql_loc = "SELECT r.ID, completename AS dispname ";
	} else {
		$sql_loc = "SELECT r.ID, name AS dispname ";
	}
	$sql_loc.= "FROM `".$tablename."` l,glpi_plugin_applicatifs_relation r, glpi_plugin_applicatifs_device d " .
			" WHERE	l.ID=r.FK_relation 
			AND r.FK_applicatifs_device=d.ID 
			AND d.ID='$relID' ";
	$result_loc = $DB->query($sql_loc);

	$opts=array();
	while ($res=$DB->fetch_array($result_loc)){
		$opts[]=$res["dispname"];
	}
	$pdf->setColumnsSize(100);
	$pdf->displayLine("<b><i>".$LANG['plugin_applicatifs'][22].":</i> $title:</b> ".implode(', ',$opts));
	
}

/**
 * Show the optional value for a device / applicatif
 * 
 * @param $ID of the relation
 * @param $applicatifsID, ID of the applicatif
 * @param $canedit, if user is allowed to edit the values
 * 	- canedit the device if called from the device form
 * 	- must be false if called from the applicatif form
 */
function plugin_applicatifs_showOptions ($ID, $applicatifsID, $canedit) {
	
	global $DB, $CFG_GLPI, $LANG;

	$query_app_opt = "SELECT ID,champ,ddefault 
					FROM glpi_plugin_applicatifs_optvalues 
					WHERE applicatif_ID = '".$applicatifsID."' 
					ORDER BY vvalues";
	$result_app_opt = $DB->query($query_app_opt);
	$number_champs = $DB->numrows($result_app_opt);

	if ($canedit)  {
		echo "<form method='post' action='".$CFG_GLPI["root_doc"]."/plugins/applicatifs/front/plugin_applicatifs.form.php'>";
		echo "<input type='hidden' name='number_champs' value='$number_champs'>";		
	}
	echo "<table>";

	for ($i=1 ; $i<=$number_champs; $i++) {
	  	if ($data_opt=$DB->fetch_array($result_app_opt)){

			$query_val = "SELECT vvalue 
					FROM glpi_plugin_applicatifs_optvalues_machines 
					WHERE optvalue_ID = '".$data_opt["ID"]."' 
					AND machine_ID = '$ID' ";
			$result_val = $DB->query($query_val);
			$data_val=$DB->fetch_array($result_val);
			$vvalue = ($data_val ? $data_val['vvalue'] : "");
			if (empty($vvalue) && !empty($data_opt['ddefault'])) {
			   $vvalue = $data_opt['ddefault'];
			}

		    echo "<tr><td>".$data_opt['champ'].": </td><td>";
		    if ($canedit) {
			    echo "<input type='hidden' name='opt_id$i' value='".$data_opt["ID"]."'>";
			    echo "<input type='hidden' name='ddefault$i' value='".$data_opt["ddefault"]."'>";
				echo "<input type='text' name='vvalue$i' value='".$vvalue."'>";		    	
		    } else {
		    	echo $vvalue;
		    }
			echo "</td></tr>";
	  } else {
	  		echo "<input type='hidden' name='opt_id$i' value='-1'>"; 
	  }
	} // For
	
	echo "</table>";

	if ($canedit)  {
		echo "<input type='hidden' name='item' value='$ID'>";
		echo "<input type='submit' name='add_opt_val' value='".$LANG['buttons'][7]."' class='submit'>";
		echo "</form>";
	}
}

/**
 * Show for PDF the optional value for a device / applicatif
 * 
 * @param $pdf object for the output
 * @param $ID of the relation
 * @param $applicatifsID, ID of the applicatif
 */
function plugin_applicatifs_showOptions_PDF ($pdf, $ID, $applicatifsID) {
	
	global $DB, $CFG_GLPI, $LANG;

	$query_app_opt = "SELECT ID,champ,ddefault 
					FROM glpi_plugin_applicatifs_optvalues 
					WHERE applicatif_ID = '".$applicatifsID."' 
					ORDER BY vvalues";
	$result_app_opt = $DB->query($query_app_opt);
	$number_champs = $DB->numrows($result_app_opt);

	if (!$number_champs) return;
	
	$opts=array();
	for ($i=1 ; $i<=$number_champs; $i++) {
	  	if ($data_opt=$DB->fetch_array($result_app_opt)){

			$query_val = "SELECT vvalue 
					FROM glpi_plugin_applicatifs_optvalues_machines 
					WHERE optvalue_ID = '".$data_opt["ID"]."' 
					AND machine_ID = '$ID' ";
			$result_val = $DB->query($query_val);
			$data_val=$DB->fetch_array($result_val);
			$vvalue = ($data_val ? $data_val['vvalue'] : "");
			if (empty($vvalue) && !empty($data_opt['ddefault'])) {
			   $vvalue = $data_opt['ddefault'];
			}

			$opts[]=$data_opt['champ'].($vvalue?"=".$vvalue:'');
	  	}
	} // For
	
	$pdf->setColumnsSize(100);
	$pdf->displayLine("<b><i>".$LANG['plugin_applicatifs'][24].": </i></b>".implode(', ',$opts));
}

/**
 * Show for PDF an applicatif
 * 
 * @param $pdf object for the output
 * @param $ID of the applicatif
 */
function plugin_applicatifs_main_PDF ($pdf, $ID) {
	global $LANG, $DB;
	
	$item=new PluginApplicatifs();
	if (!$item->getFromDB($ID)) return false;
	
	$pdf->setColumnsSize(50,50);
	$col1 = '<b>'.$LANG["common"][2].' '.$item->fields['ID'].'</b>';
	if (isset($item->fields["date_mod"])) {
		$col2 = $LANG["common"][26].' : '.convDateTime($item->fields["date_mod"]);
	} else {
		$col2 = '';
	}
	$pdf->displayTitle($col1, $col2);
	
	$pdf->displayLine(
		'<b><i>'.$LANG["common"][16].' :</i></b> '.$item->fields['name'],
		'<b><i>'.$LANG['plugin_applicatifs'][20].' :</i></b> '.html_clean(getDropdownName('glpi_dropdown_plugin_applicatifs_type',$item->fields['type'])));
	$pdf->displayLine(		
		'<b><i>'.$LANG["common"][10].' :</i></b> '.getUserName($item->fields['FK_users']),
		'<b><i>'.$LANG["common"][35].' :</i></b> '.html_clean(getDropdownName('glpi_groups',$item->fields['FK_groups'])));
	$pdf->displayLine(
		'<b><i>'.$LANG["common"][15].' :</i></b> '.html_clean(getDropdownName('glpi_dropdown_locations',$item->fields['location'])),
		'<b><i>'.$LANG['plugin_applicatifs'][22].' :</i></b> '.html_clean(plugin_applicatifs_getrelationtypename($item->fields["relationtype"])));
	
	$query_app = "SELECT champ, ddefault 
				FROM glpi_plugin_applicatifs_optvalues 
				WHERE applicatif_ID = '".$ID."' 
				ORDER BY vvalues";
	$result_app = $DB->query($query_app);

	$opts=array();
	while ($data=$DB->fetch_array($result_app)) {
		$opts[]=$data["champ"].($data["ddefault"] ? '='.$data["ddefault"] : '');
	}
	$pdf->setColumnsSize(100);
	$pdf->displayLine("<b><i>".$LANG['plugin_applicatifs'][24].": </i></b>".implode(', ',$opts));
	
			
	$pdf->displayText('<b><i>'.$LANG["common"][25].' :</i></b>', $item->fields['comments']);
	
	$pdf->displaySpace();
}
?>