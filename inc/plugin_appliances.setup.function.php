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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

function plugin_appliances_installing($version) {
   global $DB,$LANG;

   $DB_file = GLPI_ROOT ."/plugins/appliances/inc/plugin_appliances-$version-empty.sql";
   $DBf_handle = fopen($DB_file, "rt");
   $sql_query = fread($DBf_handle, filesize($DB_file));
   fclose($DBf_handle);

   foreach (explode(";\n", "$sql_query") as $sql_line) {
      if (get_magic_quotes_runtime()) {
         $sql_line = stripslashes_deep($sql_line);
      }
      $DB->query($sql_line);
   }

   $query = "INSERT INTO
             `glpi_plugin_appliances_relationtypes`
             (`id`, `name`, `comment`)
             VALUES (1 , '".$LANG['common'][15]."', NULL),
                    (2 , '".$LANG['help'][27]."', NULL),
                    (3 , '".$LANG['help'][31]."', NULL)";
   $DB->query($query) or die($DB->error());
}


function plugin_appliances_update($name,$version) {
   global $DB;

   $DB_file = GLPI_ROOT ."/plugins/appliances/inc/plugin_$name-$version-update.sql";
   $DBf_handle = fopen($DB_file, "rt");
   $sql_query = fread($DBf_handle, filesize($DB_file));
   fclose($DBf_handle);

   foreach ( explode(";\n", "$sql_query") as $sql_line) {
      if (get_magic_quotes_runtime()) {
         $sql_line = stripslashes_deep($sql_line);
      }
      $DB->query($sql_line);
   }
}


function plugin_appliances_updatev14() {
   global $DB,$LANG;

   $DB_file = GLPI_ROOT ."/plugins/appliances/inc/plugin_applicatifs-1.4-update.sql";
   $DBf_handle = fopen($DB_file, "rt");
   $sql_query = fread($DBf_handle, filesize($DB_file));
   fclose($DBf_handle);

   foreach ( explode(";\n", "$sql_query") as $sql_line) {
      if (get_magic_quotes_runtime()) {
         $sql_line = stripslashes_deep($sql_line);
      }
      $DB->query($sql_line);
   }

   $query="INSERT
           INTO `glpi_dropdown_plugin_applicatifs_relationtype`
           (`ID`, `name`, `comments`)
           VALUES (1 , '".$LANG['common'][15]."', NULL),
                  (2 , '".$LANG['help'][27]."', NULL),
                  (3 , '".$LANG['help'][31]."', NULL)";
   $DB->query($query) or die($DB->error());
}

?>