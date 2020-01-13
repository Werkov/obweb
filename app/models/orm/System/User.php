<?php

namespace Model\System;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table system_user
 * @manyToMany(name = Roles, referencedEntity = \Model\System\Role, connectingTable = system_user2role, referencedKey = role_id, localKey = user_id)
 * @@hasMany(name = AccountTransactions, referencedEntity = \Model\App\AccountTransaction, column = user_id)
 * @hasMany(name = Entrys, referencedEntity = \Model\App\Entry, column = racer_id)
 * @hasOne(name = Member, referencedEntity = \Model\App\Member, column = id)
 * @hasOne(name = Backer, referencedEntity = \Model\App\Backer, column = id)
 * @hasOne(name = Account, referencedEntity = \Model\App\Account, column = account_id)
 * @hasMany(name = Races, referencedEntity = \Model\App\Race, column = manager_id)
 * @hasMany(name = StaticPages, referencedEntity = \Model\Con\StaticPage, column = lastModeUser)
 * @hasMany(name = Files, referencedEntity = \Model\Doc\File, column = author_id)
 * @hasMany(name = Photos, referencedEntity = \Model\Gallery\Photo, column = author_id)
 * @hasMany(name = Briefs, referencedEntity = \Model\Org\Brief, column = author_id)
 * @hasMany(name = Articles, referencedEntity = \\Model\\Publication\\Article, column = author_id)
 * @hasMany(name = Briefs, referencedEntity = \\Model\\Publication\\Brief, column = author_id)
 * @hasMany(name = Survey2users, referencedEntity = \Model\Survey\Survey2user, column = user_id)
 * @hasMany(name = Cars, referencedEntity = \Model\Transport\Car, column = owner_id)
 * @hasMany(name = Demands, referencedEntity = \Model\Transport\Demand, column = customer_id)
 * @hasMany(name = Messages, referencedEntity = \Model\Transport\Message, column = author_id)
 * @hasMany(name = Supplys, referencedEntity = \Model\Transport\Supply, column = driver_id)
 * @manyToMany(name = Appliers, referencedEntity = \Model\System\User, connectingTable = app_allowed, referencedKey = applier_id, localKey = applicant_id)
 * @manyToMany(name = Applicants, referencedEntity = \Model\System\User, connectingTable = app_allowed, referencedKey = applicant_id, localKey = applier_id)
 */
class User extends \Navigation\Record implements \Nette\Security\IResource, \Nette\Security\IIdentity, \Serializable {

    public function getResourceId() {
        return "system_user";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->getFullname();

        return $res;
    }
    
    public static function menuParentInfoReg($params = array()) {
        $row = self::find(array('registration' => $params['id']));

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->getFullname();

        return $res;
    }

    public function delete() {
        $this->activeState = 0;
        $this->save();
    }

    /**
     *
     * @param string $password Plaintext password to check.
     * @return bool
     */
    public function checkPassword($password) {
        $compat = md5($password);
        return password_verify($compat, $this->password);
    }

    /**
     * Hashes the password using user data.
     *
     * @param string $password Password to hash
     * @return string
     */
    public function hashPassword($password) {
        $compat = md5($password);
        return password_hash($compat, PASSWORD_BCRYPT);
    }

    /**
     * Gets first free registration for each year of birth and each gender.
     * @return array of string
     */
    public static function getAvailableRegistrations() {
        $regs = \dibi::query("SELECT registration FROM :t:system_user WHERE NOT registration IS NULL")
                ->fetchPairs();

        $maxM = array();
        $maxF = array();
        for ($i = 0; $i < 100; ++$i) {
            $maxM[$i] = 0;
            $maxF[$i] = 50;
        }

        foreach ($regs as $reg) {
            $reg = (int) $reg;
            if ($reg == 0) {
                continue;
            }

            $year = floor($reg / 100);
            $order = $reg % 100;

            if ($order > 50)
                $maxF[$year] = max($maxF[$year], $order);
            else
                $maxM[$year] = max($maxM[$year], $order);
        }

        $res = array();
        for ($i = 0; $i < 100; ++$i) {
            $res[] = \sprintf("%02d%02d", $i, $maxF[$i] + 1);
            $res[] = \sprintf("%02d%02d", $i, $maxM[$i] + 1);
        }
        return $res;
    }

    public function getFullName($reg = true) {
        $r = $this->name . " " . $this->surname;
        if ($reg && $this->registration)
            $r .= " (" . $this->registration . ")";

        return $r;
    }

    // <editor-fold defaultstate="collapsed" desc="Components">
    /**
     * WARNING: right to apply to race is tested on logged user!!
     * @param User $user
     * @return bool true when instantiated user can apply given user
     */
    public function canApply(User $user) {
        if ($user->id == $this->id) {
            return true;
        }
        foreach ($this->Applicants->toArray() as $testUser) {
            if ($testUser->id == $user->id) {
                return true;
            }
        }
        return false;
    }

    protected $_id = null;

    public function getId() {
        if ($this->_id === null) {
            $this->_id = $this->id;
        }
        return $this->_id;
    }

    protected $roles = null;

    public function getRoles() {
        //$identity = new \Nette\Security\Identity($user->id, null, $user);
        if ($this->roles === null) {
            $roleNames = $this->Roles->fetchColumn("name");
            $identity = $this;
            $this->roles = \array_map(function($name) use($identity) {
                        return new \ACL\Role($name, $identity);
                    }, $roleNames); //, \array_fill(0, count($roleNames), $this->id));
            if (($this->Member && $this->Member->active) || ($this->Backer && $this->Backer->active)) {
                $this->roles[] = new \ACL\Role("registered", $identity);
            }

            //$identity->setRoles($roles);
        }

        return $this->roles;
    }

    public function serialize() {
        return (string) $this->getId();
    }

    public function unserialize($serialized) {
        parent::__construct((int) $serialized);
    }

    //</editor-fold>
}
