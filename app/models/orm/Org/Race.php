<?php

namespace Model\Org;

use Model\CoolUrlBehavior;
use Navigation\Navigation;
use Navigation\Record;
use Nette\Security\IResource;
use Nette\Security\Permission;
use Ormion\Behavior\Sortable;

/**
 * @generator MScaffolder
 *
 * @table org_race
 * @hasMany(name = InformationValues, referencedEntity = \Model\Org\InformationValues, column = race_id)
 * @hasOne(name = Event, referencedEntity = \Model\Org\Event, column = event_id)
 */
class Race extends Record implements IResource {

    public function getResourceId() {
        return "org_race";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        $resource = $acl->getQueriedResource();
        if ($resource instanceof InformationValues) {
            $event = $resource->Race->Event;
        } else if ($resource instanceof Race) {
            $event = $resource->Event;
        } else {
            return false;
        }
        return $event->isEventAdmin($acl->getQueriedRole()->getIdentity());
    }

    protected function init() {
        $this->addBehavior(new Sortable("order", "event_id"));
        $this->addBehavior(new CoolUrlBehavior());
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[Navigation::PINFO_PARAMS] = array("parent" => $row->event_id);
        $res[Navigation::PINFO_THIS][Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Event::find($params['parent']);

        $res[Navigation::PINFO_PARAMS] = array();
        $res[Navigation::PINFO_THIS][Navigation::PINFO_TEXT] = $row->name;

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
