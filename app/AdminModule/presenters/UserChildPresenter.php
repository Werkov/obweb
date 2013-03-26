<?php

namespace AdminModule;

use \Model\System\User;
use \Model\App\Backer;
use Nette\Forms\Form;

/**
 */
abstract class UserChildPresenter extends \RecordPresenter {

    // <editor-fold defaultstate="collapsed" desc="Components">



    // </editor-fold>

    abstract public function toggleActivity($id);
    
    public function actionAdd($id, $parent) {
        if (Backer::getPossibleUsers()->count() == 0) {
            throw new \Nette\Application\ApplicationException("Nelze tvořit člena/příznivce, nejsou uživatelské účty.");
        }
        $this->template->autocompleteOptions = User::getAvailableRegistrations();
        parent::actionAdd($id, $parent);
    }

    public function renderEdit($id, $parent) {
        parent::renderEdit($id, $parent);

        $this->template->autocompleteOptions = User::getAvailableRegistrations();

        $form = $this["editForm"];
        $form->setDefaults(array(
            "registration" => $this->currentRecord->User->registration,
        ));
        $form['user']->setDefaults(
                $this->currentRecord->User->getValues()
        );
        UserPresenter::setRolesValue($this->currentRecord->User, $form['user']);
    }

    protected function setRelations(\Nette\Application\UI\Form $form) {
        if ($this->getAction() == 'add') {
            $this->currentRecord->active = 1;
        }
        $values = $form->getValues();
        $this->currentRecord->User->registration = $values["registration"];
        if ($this->currentRecord->getState() == \Ormion\Record::STATE_EXISTING) {
            $this->currentRecord->User->setValues($form['user']->getValues());
            UserPresenter::setUserBeforeSave($this->currentRecord->User, $form['user']);
        }
        parent::setRelations($form);
    }

    protected function saveRecord(\Nette\Application\UI\Form $form) {
        $this->currentRecord->User->save(); // user roles won't be saved because they're 2nd order association
        parent::saveRecord($form);
    }

}

