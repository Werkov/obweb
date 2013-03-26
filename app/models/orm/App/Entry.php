<?php

namespace Model\App;

use Nette\Security\Permission;
use Nette\Forms\Form;

/**
 * @generator MScaffolder
 *
 * @table app_entry
 * @hasOne(name = Category, referencedEntity = \Model\App\Race2category, column = presentedCategory_id)
 * @hasOne(name = Racer, referencedEntity = \Model\System\User, column = racer_id)
 * @hasOne(name = Account, referencedEntity = \Model\App\Account, column = account_id)
 * @hasMany(name = SelectedOptions, referencedEntity = \Model\App\SelectedOption, column = entry_id)
 */
class Entry extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "app_entry";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        if (isset($acl->getQueriedResource()->racer_id)) {
            if ($acl->getQueriedResource()->racer_id == $acl->getQueriedRole()->getIdentity()->id) {
                return true;
            } else {
                $applicant = \Model\System\User::create(array("id" => $acl->getQueriedResource()->racer_id));
                return $acl->getQueriedRole()->getIdentity()->canApply($applicant);
            }
        } else {
            return true;
        }
    }

    public static function menuParentInfo($params = array()) {

        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->getRace()->id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->racerName ? $row->racerName : $row->Racer->getFullname();

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Race2category::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->getRace()->id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

        return $res;
    }

    public function setValues($data) {

        //set correct inner data from form data, names must be compatible with names from the form
        $set = array();
        if (isset($data["anonymous"])) {
            if ($data["anonymous"]) {
                $this->racerName = $data["racerName"];
                $this->racer_id = null;
            } else {
                $this->racerName = null;
                $this->racer_id = $data["racer_id"];
            }
            $set[] = "racerName";
            $set[] = "racer_id";
        }


        if (isset($data["cost"])) {
            $entry = $this;

            $this->SelectedOptions = array();
            foreach ($data['cost'] as $key => $option) {
                $c = ($entry->getState() == Entry::STATE_EXISTING) ? SelectedOption::find(array("entry_id" => $entry->id, "cost_id" => $key, "option_id" => $option)) : false;
                if (!$c)
                    $c = SelectedOption::create(array("cost_id" => $key, "option_id" => $option));
                $this->SelectedOptions[] = $c;
            }
        }




        foreach ($data as $key => $value) {
            if (\array_search($key, $set) !== false)
                continue;
            $this->__set($key, $value);
        }

        return $this;
    }

    public function getPrice() {
        if (!$this->Category)
            throw new EntryException("Incomplete entry. Missing category.");
        if ($this->Category->Race->AdditionalCostOptions->count() > 0 && !$this->SelectedOptions)
            throw new EntryException("Incomplete entry. Missing cost options.");

        $price = $this->Category->price;
        foreach ($this->SelectedOptions as $option) {
            $price += $option->AdditionalCostOption->price;
        }

        return $price;
    }

    /**
     *
     * @param Account $account
     * @return bool true when application can be paid, false otherwise
     */
    public function isPayable(Account $account) {
        if ($this->getState() == self::STATE_NEW) {
            return $account->getBalance() >= $this->getPrice();
        } else if ($this->getState() == self::STATE_EXISTING) {
            $original = self::find($this->id);
            if ($original->Account->id == $account->id) {
                return ($this->getPrice() - $original->getPrice()) <= $account->getBalance();
            } else {
                return $account->getBalance() >= $this->getPrice();
            }
        }
    }

    protected function init() {
        parent::init();
        $this->addBehavior(new BalanceBehavior(null, "account_id"));
    }

    protected $race = null;

    /**
     * @return Race
     */
    public function getRace() {
        if ($this->getState() == self::STATE_EXISTING) {
            if ($this->race == null) {
                $this->race = $this->Category->Race;
            }
            return $this->race;
        } else {
            return null;
        }
    }

}

class EntryException extends \Nette\Application\ApplicationException {
    
}
