<?php

/*
 * @version $Id$
 -------------------------------------------------------------------------
 appliances - Appliances plugin for GLPI
 Copyright (C) 2003-2011 by the appliances Development Team.

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

// Original Author of file: Remi Collet
// ----------------------------------------------------------------------

class PluginAppliancesAppliancePDF extends PluginPdfCommon {


   function __construct(PluginAppliancesAppliance $obj=NULL) {

      $this->obj = ($obj ? $obj : new PluginAppliancesAppliance());
   }


   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      switch ($tab) {
         case '_main_' :
            $item->show_PDF($pdf);
            break;

         default :
            return false;
      }
      return true;
   }
}