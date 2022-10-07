<?php
/*
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Appliances plugin for GLPI.

 Appliances is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Appliances is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Appliances. If not, see <http://www.gnu.org/licenses/>.

 @package   appliances
 @author    Xavier CAILLAUD, Remi Collet, Nelly Mahu-Lasson
 @copyright Copyright (c) 2009-2022 Appliances plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/appliances
 @since     version 2.0
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


/**
 * Class PluginAppliancesOptvalue
**/
class PluginAppliancesOptvalue extends CommonDBTM {



   static function getTypeName($nb=0) {
      return __('Fields');
   }


   /**
    * Actions done when item is deleted from the database
    **/
    function cleanDBonPurge() {

      $temp = new PluginAppliancesOptvalue_Item();
      $temp->deleteByCriteria(['plugin_appliances_optvalues_id' => $this->fields['id']]);
   }


  /**
   * Display list of Optvalues for an appliance
   *
   * @param $appli PluginAppliancesAppliance instance
   *
   * @return nothing (display form)
   **/
   static function showForAppliance (Appliance $appli) {
      global $DB, $CFG_GLPI;

      if (!$appli->can($appli->fields['id'],READ)) {
         return false;
      }
      $canedit = $appli->can($appli->fields['id'],UPDATE);

      $rand = mt_rand();
      if ($canedit) {
         echo "<form method='post' name='optvalues_form$rand' id='optvalues_form$rand' action=\"".
               $CFG_GLPI["root_doc"]."/plugins/appliances/front/optvalue.form.php\">";
      }

      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>".__('User fields', 'appliances')."</th></tr>\n";

      $query_app = $DB->request(['FROM' => 'glpi_plugin_appliances_optvalues',
                                 'WHERE' => ['appliances_id' => $appli->fields['id']],
                                 'ORDER' => 'vvalues']);
      $number_champs = count($query_app);
      $number_champs++;

      for ($i=1 ; $i <= $number_champs ; $i++) {
         if ($data = $query_app->current()) {
            $champ    = $data["champ"];
            $ddefault = $data["ddefault"];
         } else {
            $champ    = '';
            $ddefault = '';
         }

         echo "<tr class='top tab_bg_1'>";

         if ($i == 1) {
            echo "<td rowspan='".$number_champs."'>"._n('Field', 'Fields', 1)."</td>";
         }
         echo "<td><input type='text' name='champ$i' value=\"".$champ."\" size='35'></td>\n";
         if ($i == 1) {
         echo "<td rowspan='".$number_champs."'>".__('Default', 'appliances')."</td>";
         }
         echo "<td><input type='text' name='ddefault$i' value=\"".$ddefault."\" size='35'></td></tr>\n";
         $query_app->next();
      }
      if ($canedit) {
         echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
         echo Html::hidden('id', ['value' => $appli->fields['id']]);
         echo Html::hidden('number_champs', ['value' => $number_champs]);
         echo Html::submit(_sx('button', 'Update'), ['name' => 'update_optvalues',
                                                     'class' => 'btn btn-primary']);
         echo "</td></tr>\n</table></div>";
         Html::closeForm();
      } else {
         echo "</table></div>";
      }
      return true;
   }


   /**
    * @param $pdf         Instance of plugin PDF
    * @param $appli       PluginAppliancesAppliance
    **/
    static function pdfForAppliance(PluginPdfSimplePDF $pdf, $appli) {
      global $DB;

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.__('User fields', 'appliances').'</b>');

      $query_app = $DB->request(['FIELDS' => ['champ', 'ddefault', 'vvalues'],
                                 'FROM'   => 'glpi_plugin_appliances_optvalues',
                                 'WHERE'  => ['appliances_id' => $appli->getID()],
                                 'ORDER' => 'vvalues']);

      $opts = [];
      foreach ($query_app as $data) {
         $opts[] = '<b>'.$data["champ"].'</b>'.($data["ddefault"] ? ' = '.$data["ddefault"] : '');
      }
      if (count($opts)) {
         $pdf->displayLine(implode(',<br />',$opts));
      } else {
         $pdf->displayLine(__('No item found'));
      }

      $pdf->displaySpace();
   }


   /**
    * Update the list of Optvalues defined for an appliance
    *
    * @param $input array of input data (form)
    *
    * @return bool
   **/
   function updateList($input) {
      global $DB;

     if (!isset($input['number_champs']) || !isset($input['id'])) {
         return false;
      }
      $number_champs = $input['number_champs'];

      for ($i=1 ; $i<=$number_champs ; $i++) {
         $champ    = "champ$i";
         $ddefault = "ddefault$i";

         $query_app = $DB->request(['SELECT' => 'id',
                                    'FROM'   => 'glpi_plugin_appliances_optvalues',
                                    'WHERE'  => ['appliances_id' => $input['id'],
                                                 'vvalues' => $i]]);

         if ($data = $query_app->current()) {
            // l'entrée existe déjà, il faut faire un update ou un delete
            if (empty($input[$champ])) {
               $this->delete($data);
            } else {
               $data['champ']    = $input[$champ];
               $data['ddefault'] = $input[$ddefault];
               $this->update($data);
            }
         } else if (!empty($input[$champ])) {
            // l'entrée n'existe pas
            // et la valeur saisie est non nulle -> on fait un insert
            $data = ['appliances_id'  => $input['id'],
                     'champ'          => $input[$champ],
                     'ddefault'       => $input[$ddefault],
                     'vvalues'        => $i];
            $this->add($data);
         }
      }
   }


   /**
    * @param $item   Appliance Object
    *
    * @return integer
    **/
    static function countForAppliance(Appliance $item) {

      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_appliances_optvalues',
                                        ['appliances_id' => $item->getID()]);
   }


   /**
    * Get Tab Name used for itemtype
    *
    * @see CommonGLPI getTabNameForItem()
    **/
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Appliance') {
         $nb = '';
         if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = self::countForAppliance($item);
         }
         return self::createTabEntry(_n('Field', 'Fields', 2), $nb);
      }
      return '';
   }


   /**
    * show Tab content
    *
    * @see CommonGLPI::displayTabContentForItem()
    **/
    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Appliance') {
         self::showForAppliance($item);
      }
      return true;
   }


   /**
    * show Tab content for PDF
    *
    * @param $pdf            Instance of plugin PDF
    * @param $item           CommonGLPI object
    * @param $tab
    *
    * @return bool
    **/
    static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType() == 'PluginAppliancesAppliance') {
         self::pdfForAppliance($pdf, $item);
      } else {
         return false;
      }
      return true;
   }

}
