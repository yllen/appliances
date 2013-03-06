<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 appliances - Appliances plugin for GLPI
 Copyright (C) 2003-2013 by the appliances Development Team.

 https://forge.indepnet.net/projects/appliances
 -------------------------------------------------------------------------

 LICENSE

 This file is part of appliances.

 appliances is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 appliances is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with appliances. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

$plugin = new Plugin();

if ($plugin->isActivated("environment")) {
   Html::header(_n('Appliance', 'Appliances', 2, 'appliances'), $_SERVER['PHP_SELF'], "plugins",
                "environment", "appliances");
} else {
   Html::header(_n('Appliance', 'Appliances', 2, 'appliances'), $_SERVER['PHP_SELF'], "plugins",
                "appliances");
}

if (plugin_appliances_haveRight("appliance","r")
    || Session::haveRight("config","w")) {
   Search::show('PluginAppliancesAppliance');

} else {
   Html::displayRightError();
}
Html::footer();
?>