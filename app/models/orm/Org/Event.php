<?php

namespace Model\Org;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table org_event
 * @hasMany(name = Briefs, referencedEntity = \Model\Org\Brief, column = event_id)
 * @hasMany(name = Races, referencedEntity = \Model\Org\Race, column = event_id)
 * @hasOne(name = Manager, referencedEntity = \Model\System\User, column = manager_id)
 * @manyToMany(name = Administrators, referencedEntity = \Model\System\User, connectingTable = org_event2user, referencedKey = user_id, localKey = event_id)
 */
class Event extends \Navigation\Record implements \Nette\Security\IResource {
    const VISIBILITY_ALL = 'all';
    const VISIBILITY_LOGGED = 'logged';
    const VISIBILITY_MANAGER = 'manager';

    public function getResourceId() {
        return "org_event";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        switch ($privilege) {
            case "add":
                return false;
                break;
            case "edit":
            case "delete":
                return $acl->getQueriedResource()->isEventAdmin($acl->getQueriedRole()->getIdentity());
                break;
            case "list":
                return self::isAnyEventAdmin($acl->getQueriedRole()->getIdentity());
            case "view":
                switch ($acl->getQueriedResource()->visibility) {
                    case self::VISIBILITY_ALL:
                        return true;
                        break;
                    case self::VISIBILITY_LOGGED:
                        return $role != "guest"; //TODO test
                        break;
                    case self::VISIBILITY_MANAGER:
                        return $acl->getQueriedResource()->isEventAdmin($acl->getQueriedRole()->getIdentity());
                        break;
                }
                break;
            default:
                return false;
                break;
        }
    }

    public static function SqlVisibility(\Nette\Http\User $user, \DibiFluent &$fluent) {
        if ($user->isLoggedIn()) {
            $uid = $user->getIdentity()->getId();
            $fluent->where("(e.visibility = %s", self::VISIBILITY_ALL, " OR (e.visibility = %s AND (e.manager_id = %i OR eu.user_id = %i))", self::VISIBILITY_MANAGER, $uid, $uid, "OR e.visibility = %s)", self::VISIBILITY_LOGGED);
        } else {
            $fluent->where("(e.visibility = %s)", self::VISIBILITY_ALL);
        }
    }

    public static function isAnyEventAdmin(\Model\System\User $user) {
        $query = \dibi::select('COUNT(1)')
                ->from('[:t:org_event] AS e')
                ->leftJoin('[:t:org_event2user] AS eu')->on('eu.event_id = e.id')
                ->where('e.manager_id = %i OR eu.user_id = %i', $user->id, $user->id);
        return $query->fetchSingle() > 0;
    }

    public function isEventAdmin(\Model\System\User $user) {
        return $this->manager_id == $user->id
                || (array_search($user->id, $this->Administrators->fetchColumn("id")) !== false);
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

}
