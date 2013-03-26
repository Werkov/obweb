<?php

namespace Model\App;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table app_accountTransaction
 * @hasOne(name = Account, referencedEntity = \Model\App\Account, column = account_id)
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = user_id)
 */
class AccountTransaction extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "app_account";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = AccountTransaction::find($params['id']);
        
        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->account_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Account::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public function save($oldHash = null, $level = 0) {
        parent::save($oldHash, $level);
    }

    protected function init() {
        parent::init();
        $this->addBehavior(new BalanceBehavior("amount", "account_id"));
    }

}

class BalanceException extends \Nette\Application\ApplicationException {
    
}
