<?php

namespace Model\App;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table app_race
 * @hasMany(name = AdditionalCostOptions, referencedEntity = \Model\App\AdditionalCostOption, column = race_id)
 * @hasOne(name = Manager, referencedEntity = \Model\System\User, column = manager_id)
 * @hasOne(name = Tag, referencedEntity = \Model\App\Tag, column = tag_id)
 * @hasMany(name = Categories, referencedEntity = \Model\App\Race2category, column = race_id)
 */
class Race extends \Navigation\Record implements \Nette\Security\IResource {
    const STATUS_EDIT = 0;
    const STATUS_APP = 1;
    const STATUS_CLOSED = 2;

    public function getApplications() {
        if ($this->getState() != self::STATE_EXISTING)
            return array();

        return Entry::findAll("presentedCategory_id IN (" .
                        \dibi::select('id')->from(':t:app_race2category')->where(array('race_id' => $this->id)) .
                        ")");
    }

    public function getResourceId() {
        return "app_race";
    }

    protected function init() {
        parent::init();
        $this->onAfterInsert[] = function($record) {
                    \dibi::query("UPDATE :t:app_race2category AS rc SET rc.price = " .
                            "(SELECT defaultPrice FROM :t:app_category AS c WHERE c.id = rc.category_id) " .
                            "WHERE rc.race_id = %i", $record->id
                    );
                };
        $this->addBehavior(new \Model\CoolUrlBehavior());
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = Race::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Race::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo3($params = array()) {
        $row = Race::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array('id' => $params['parent']);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function updateStatus() {
        \dibi::query("UPDATE :t:app_race SET status = %i WHERE ADDDATE(deadline, INTERVAL 1 DAY) < NOW()", self::STATUS_CLOSED);
    }

}
