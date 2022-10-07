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
 * Class PluginAppliancesAppliance_Item
**/
class PluginAppliancesAppliance_Item extends CommonDBRelation {


   /**
    * show for PDF the applicatif associated with a device
    *
    * @param $pdf       instance of plugin PDF
    * @param $item      CommonGLPI object
   **/
   static function pdfForItem(PluginPdfSimplePDF $pdf, CommonGLPI $item){
      global $DB;

      $dbu = new DbUtils();

      $ID       = $item->getField('id');
      $itemtype = get_class($item);

      $pdf->setColumnsSize(100);
      $pdf->displayTitle("<b>".__('Associated appliances', 'appliances')."</b>");

      $query = ['FIELDS'    => ['glpi_appliances_items.id AS entID',
                                'glpi_appliances.*',
                                'itemtype'],
                'FROM'      => 'glpi_appliances_items',
                'LEFT JOIN' => ['glpi_appliances_items_relations'
                                => ['FKEY' => ['glpi_appliances_items'       => 'id',
                                               'glpi_appliances_items_relations'
                                                     => 'appliances_items_id']]],
                'LEFT JOIN' => ['glpi_appliances'
                                => ['FKEY' => ['glpi_appliances'       => 'id',
                                               'glpi_appliances_items' => 'appliances_id']],
                               'glpi_entities'
                                => ['FKEY' => ['glpi_entities'   => 'id',
                                               'glpi_appliances' => 'entities_id']]],
                'WHERE'     => ['glpi_appliances_items.items_id' => $ID,
                                'glpi_appliances_items.itemtype' => $itemtype]
                               + getEntitiesRestrictCriteria('glpi_appliances', 'entities_id',
                                                             $item->getEntityID(), true)];
      $result = $DB->request($query);
      $number = count($result);

      if (!$number) {
         $pdf->displayLine(__('No item found'));
      } else {
         if (Session::isMultiEntitiesMode()) {
            $pdf->setColumnsSize(30,30,20,20);
            $pdf->displayTitle('<b><i>'.__('Name'), __('Entity'), __('Group'), __('Type').'</i></b>');
         } else {
            $pdf->setColumnsSize(50,25,25);
            $pdf->displayTitle('<b><i>'.__('Name'), __('Group'),__('Type').'</i></b>');
         }

         foreach ($result as $data) {
            $appliancesID = $data["id"];
            if (Session::isMultiEntitiesMode()) {
               $pdf->setColumnsSize(30,30,20,20);
               $pdf->displayLine($data["name"],
                                 Toolbox::stripTags(Dropdown::getDropdownName("glpi_entities",
                                                                              $data['entities_id'])),
                                 Toolbox::stripTags(Dropdown::getDropdownName("glpi_groups",
                                                                              $data["groups_id"])),
                                 $data['itemtype']);
            } else {
               $pdf->setColumnsSize(50,25,25);
               $pdf->displayLine($data["name"],
                                 Toolbox::stripTags(Dropdown::getDropdownName("glpi_groups",
                                                                              $data["groups_id"])),
                                 $data['itemtype']);
            }
            PluginAppliancesOptvalue_Item::showList_PDF($pdf, $ID, $appliancesID);
         }
      }
      $pdf->displaySpace();
   }


    /**
     * @param $pdf      instance of plugin PDF
     * @param $appli    PluginAppliancesAppliance object
     *
     * @return bool
    **/
    static function pdfForAppliance(PluginPdfSimplePDF $pdf,  $appli) {
      global $DB;

      $instID = $appli->fields['id'];

      if (!$appli->can($instID, READ)) {
         return false;
      }

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'._n('Associated item', 'Associated items',2).'</b>');

      $result = $DB->request(['SELECT'    => 'itemtype',
                              'DISTINCT'  => true,
                              'FROM'      => 'glpi_appliances_items',
                              'WHERE'     => ['appliances_id' => $instID]]);
      $number = count($result);

      if (Session::isMultiEntitiesMode()) {
         $pdf->setColumnsSize(12,27,25,18,18);
         $pdf->displayTitle('<b><i>'.__('Type'), __('Name'), __('Entity'), __('Serial number'),
                                     __('Inventory number').'</i></b>');
      } else {
         $pdf->setColumnsSize(25,31,22,22);
         $pdf->displayTitle('<b><i>'.__('Type'), __('Name'), __('Serial number'),
                                     __('Inventory number').'</i></b>');
      }

      if (!$number) {
         $pdf->displayLine(__('No item found'));
      } else {
         $dbu = new DbUtils();
         foreach ($result as $id => $row) {
            $type = $row['itemtype'];
            if (!($item = $dbu->getItemForItemtype($type))) {
               continue;
            }

            if ($item->canView()) {
               $column = "name";
               if ($type == 'Ticket') {
                  $column = "id";
               }
               if ($type == 'KnowbaseItem') {
                  $column = "question";
               }

               $query = ['FIELDS'   => [$item->getTable().'.*',
                                        'glpi_appliances_items.id AS IDD',
                                        'glpi_entities.id AS entity'],
                        'FROM'      => 'glpi_appliances_items',
                        'LEFT JOIN' => [$item->getTable()
                                        => ['FKEY' => [$item->getTable() => 'id',
                                                       'glpi_appliances_items'
                                                                         => 'items_id'],
                                                      ['glpi_appliances_items.itemtype'
                                                           => $type]],
                                        'glpi_entities'
                                        => ['FKEY' => ['glpi_entities'   => 'id',
                                                       $item->getTable() => 'entities_id']]],
                         'WHERE'    => ['glpi_appliances_items.appliances_id'
                                          => $instID]
                                       + getEntitiesRestrictCriteria($item->getTable())];

               if ($item->maybeTemplate()) {
                  $query['WHERE'][$item->getTable().'.is_template'] = 0;
               }
               $query['ORDER'] = ['glpi_entities.completename', $item->getTable().'.'.$column];

               if ($result_linked = $DB->request($query)) {
                  if (count($result_linked)) {
                     foreach ($result_linked as $id => $data) {
                        if (!$item->getFromDB($data['id'])) {
                           continue;
                        }

                        if ($type == 'Ticket') {
                           $data["name"] = sprintf(__('%1$s %2$s'), __('Ticket'), $data["id"]);
                        }
                        if ($type == 'KnowbaseItem') {
                           $data["name"] = $data["question"];
                        }
                        $name = $data["name"];
                        if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                           $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
                        }

                        if (Session::isMultiEntitiesMode()) {
                           $pdf->setColumnsSize(12,27,25,18,18);
                           $pdf->displayLine($item->getTypeName(1), $name,
                                             Dropdown::getDropdownName("glpi_entities",
                                                                       $data['entities_id']),
                                             (isset($data["serial"])? $data["serial"] :"-"),
                                             (isset($data["otherserial"])?$data["otherserial"]:"-"));
                        } else {
                           $pdf->setColumnsSize(25,31,22,22);
                           $pdf->displayTitle($item->getTypeName(1), $name,
                                              (isset($data["serial"])?$data["serial"]:"-"),
                                              (isset($data["otherserial"])?$data["otherserial"]:"-"));
                        }

                        PluginAppliancesOptvalue_Item::showList_PDF($pdf, $data["id"], $instID);
                     }
                  }
               }
            }
         }
      }
      $pdf->displaySpace();
   }

}
