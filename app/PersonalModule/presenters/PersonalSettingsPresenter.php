<?php

namespace PersonalModule;

use \Model\System\User;
use \Nette\Forms\Form;

/**
 * @generator MScaffolder
 */
final class PersonalSettingsPresenter extends \AuthenticatedPresenter {

// <editor-fold desc="Render views">
    public function renderDefault() {
        $this["settingsForm"]->setDefaults($this->getUser()->getIdentity());

        if ($this->getUser()->isInRole('registered')) {
            $this["settingsForm"]->setDefaults(array(
                "appliers_id" => $this->getUser()->getIdentity()->Appliers->fetchColumn("id"),
            ));
            if ($this->getUser()->getIdentity()->Member) {
                $this["settingsForm"]->setDefaults($this->getUser()->getIdentity()->Member->getValues());
            }
        }

        $tmp = \Model\System\TokenType::find(array("name" => "RSSnews"));
        $this->template->RSSnewsToken = \Model\System\Token::getToken($tmp, $this->getUser()->getIdentity(), false);
    }

    // </editor-fold>
// <editor-fold desc="Signals">
    public function formSubmitted(\Nette\Application\UI\Form $form) {
        $values = $form->getValues();


        //check password
        //$user = \Model\System\User::find($this->getUser()->getId());
//        $user = User::create($values);
//        $user->id = $this->getUser()->getId();
        $user = $this->getUser()->getIdentity();
        $user->setValues($values);
        if ($values["oldPassword"] != "") {
            try {
                if ($user->checkPassword($values["oldPassword"])) {
                    $user->password = $user->hashPassword($values["newPassword"]);

                    $user->save();
                } else {
                    throw new \Nette\Application\ApplicationException("Wrong password.");
                }
            } catch (\Exception $exc) {
                $this->flashMessage("Heslo nebylo změněno.", \BasePresenter::FLASH_ERROR);
            }
        }

        // update contact
        unset($values["oldPassword"]);
        unset($values["newPassword"]);
        unset($values["newPassword2"]);
        try {
            //\dibi::query('UPDATE  [:t:system_user] SET ', $values, 'WHERE [id] = %i', $user->id);
            $user->save();
            $this->flashMessage("Kontakty byly upraveny.");

            //update identity TODO: needs support in Nette
            $i = $this->getUser()->getIdentity();
            foreach ($values as $k => $v) {
                $i->{$k} = $v; //->setData($user);
            }
        } catch (\Exception $exc) {
            $this->flashMessage("Kontakty nebyly upraveny.", \BasePresenter::FLASH_ERROR);
        }

        //update appliers
        if ($this->getUser()->isInRole('registered')) {
            $user->Appliers = array_map(function ($id) {
                        return User::create(array("id" => $id));
                    }, $values["appliers_id"]);

            try {
                $user->save();
                if ($user->Member) {
                    $user->Member->setValues($values);
                    $user->Member->save();
                }
                $this->flashMessage("Informace k přihlašování upraveny.");
            } catch (\Exception $exc) {
                $this->flashMessage("Informace k přihlašování nebyly upraveny.", \BasePresenter::FLASH_ERROR);
            }
        }



        $this->redirect("default");
    }

    public function handleRegenerateRSSNews() {
        $tmp = \Model\System\TokenType::find(array("name" => "RSSnews"));
        \Model\System\Token::getToken($tmp, $this->getUser()->getIdentity(), true);

        if ($this->isAjax()) {
            $this->invalidateControl();
        } else {
            $this->redirect("this");
        }
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">

    protected function createComponentSettingsForm($name) {
        $form = new \Nette\Application\UI\Form($this, $name);

        if ($this->getUser()->isInRole('registered')) {
            $form->addGroup("Přihlašování na závody");

            $form->addMultipleTextSelect("appliers_id", new \Model\App\AllowedModel($this->getUser()->getId()), "Kdo mě může přihlašovat")
                    ->setUnknownMode(\OOB\MultipleTextSelect::N_IGNORE)
                    ->addRule(Form::VALID, "Jména nejsou správně.");


            if ($this->getUser()->getIdentity()->Member) {
                $form->addText("SI", "Číslo SI")
                        ->addRule(Form::MAX_LENGTH, null, 10);
            }
            
        }

        $form->addGroup("Kontaktní informace");

        $form->addText("phone", "Telefon")
                ->addRule(Form::MAX_LENGTH, null, 20);

        $form->addText("address", "Adresa")
                ->addRule(Form::MAX_LENGTH, null, 150);


        $form->addText("email", "E-mail")
                ->addRule(Form::MAX_LENGTH, null, 100)
                ->addCondition(Form::FILLED)->addRule(Form::EMAIL, "Neplatný email.");

        $form->addText("IM", "IM (ICQ, XMPP apod.)")
                ->addRule(Form::MAX_LENGTH, null, 30);

        $form->addTextArea("other", "Poznámka");

        $form->addCheckbox("public", "Údaje jsou veřejné");
        
        $form->addGroup("Změna hesla");

        $form->addPassword("oldPassword", "Staré heslo")
                ->addRule(Form::MAX_LENGTH, null, 32);

        $form->addPassword("newPassword", "Nové heslo")
                ->addRule(Form::MAX_LENGTH, null, 32)
                ->addConditionOn($form["oldPassword"], Form::FILLED)->addRule(Form::FILLED, "Je třeba nastavit nové heslo.");

        $form->addPassword("newPassword2", "Nové heslo - ověření")
                ->addRule(Form::MAX_LENGTH, null, 32)
                ->addRule(Form::EQUAL, 'Hesla musí být stejná.', $form['newPassword']);

        



        $form->setCurrentGroup();

        $form->addSubmit('save', 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        return $form;
    }

    // </editor-fold>
}

