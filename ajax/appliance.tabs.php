<?php
/*
 * @version $Id: computer.tabs.php 7152 2008-07-29 12:27:18Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
header_nocache();

Plugin::load('appliances',true);

if (!isset($_POST["id"])) {
   exit();
}

if (!isset($_POST["sort"])) {
   $_POST["sort"] = "";
}

if (!isset($_POST["order"])) {
   $_POST["order"] = "";
}

$appliance = new PluginAppliancesAppliance();
$optvalue  = new PluginAppliancesOptvalue();

if ($_POST["id"] >0
    && $appliance->getFromDB($_POST["id"])
    && $appliance->can($_POST["id"],'r')) {

   switch($_POST['glpi_tab']) {
      case -1 :
         $appliance->showItem();
         // $appliance->showDevice();

         Infocom::showForItem($appliance);
         Contract::showAssociated($appliance);
         Document::showAssociated($appliance);
         Plugin::displayAction($appliance, $_POST['glpi_tab']);
         break;

      case 2 :
         $optvalue->showList($appliance);
         break;

      case 6 :
         Ticket::showListForItem('PluginAppliancesAppliance', $_POST["id"]);
         break;

      case 9 :
         Infocom::showForItem($appliance);
         Contract::showAssociated($appliance);
         break;

      case 10 :
         Document::showAssociated($appliance);
         break;

      case 11 :
         showNotesForm($_POST['target'], 'PluginAppliancesAppliance', $_POST["id"]);
         break;

      case 12 :
         Log::showForItem($appliance);
         break;

      default :
         if (!Plugin::displayAction($appliance, $_POST['glpi_tab'])) {
            $appliance->showItem();
         }
   }
   ajaxFooter();
}

?>