<?php

namespace Model\App;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table app_selectedOption
 * @@hasOne(name = Entry, referencedEntity = \Model\App\Entry, column = entry_id)
 * @hasOne(name = AdditionalCost, referencedEntity = \Model\App\AdditionalCost, column = cost_id)
 * @hasOne(name = AdditionalCostOption, referencedEntity = \Model\App\AdditionalCostOption, column = option_id)
 */
class SelectedOption extends \Navigation\Record implements \Nette\Security\IResource {

   public function getResourceId() {
      return "app_selectedOption";
   }

   public static function assertion(Permission $acl, $role, $resource, $privilege) {

   }

   public static function menuParentInfo($params = array()) {
      $row = \dibi::fetch("SELECT entry_id, entry_id FROM [:t:app_selectedOption] WHERE entry_id = %i", $params["id"]);

      $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->entry_id);
      $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->entry_id;

      return $res;
   }

   public static function menuParentInfo2($params = array()) {
      $row = \dibi::fetch("SELECT id, presentedCategory_id FROM [:t:app_entry] WHERE id = %i", $params["parent"]);

      $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->presentedCategory_id);
      $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

      return $res;
   }

}
