<?php

namespace Model\Org;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table org_race
 * @hasMany(name = InformationValues, referencedEntity = \Model\Org\InformationValues, column = race_id)
 * @hasOne(name = Event, referencedEntity = \Model\Org\Event, column = event_id)
 */
class Race extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "org_race";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        $event = $acl->getQueriedResource()->Event;
        return $event->isEventAdmin($acl->getQueriedRole()->getIdentity());
    }

    protected function init() {
        $this->addBehavior(new \Ormion\Behavior\Sortable("order", "event_id"));
        $this->addBehavior(new \Model\CoolUrlBehavior());
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->event_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Event::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    private $mapIdToValue = null;

    public function getInformation($id) {
        if ($this->mapIdToValue == null) {
            $this->mapIdToValue = array();
            foreach ($this->InformationValues as $value) {
                $this->mapIdToValue[$value->information_id] = $value->value;
            }
        }

        return array_key_exists($id, $this->mapIdToValue) ? $this->mapIdToValue[$id] : null;
    }

}
