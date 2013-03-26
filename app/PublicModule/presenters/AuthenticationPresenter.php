<?php

/**
 * Description of AuthenticationPresenter
 *
 * @author michal
 */

namespace PublicModule;

use Nette\Application\UI\Form;
use Nette\Forms\Form as Form_;
use Nette\Security\AuthenticationException;

final class AuthenticationPresenter extends PublicPresenter {

    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    /** @persistent */
    public $backlink = '';

    public function actionLogout() {
        $a = $this->getUser()->getIdentity()->sex == 'F' ? "a" : "";
        $this->getUser()->logout(true); //clear identity

        $this->flashMessage("Byl$a jste odhlášen$a.");
        $this->redirect(":Public:Homepage:default");
    }

    public function actionLogin() {
        //TODO: udělat i restoreRequest, pokud je (je to bezpečné?)
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect(":Personal:Dashboard:default");
        }
    }

    /*     * ******************* components ****************************** */

    /**
     * Login form component factory.
     * @return mixed
     */
    protected function createComponentLoginForm() {
        $form = new Form;
        $form->addText('username', 'Přihlašovací jméno')
                ->addRule(Form_::FILLED, 'Zadejte přihlašovací jméno.');

        $form->addPassword('password', 'Heslo')
                ->addRule(Form_::FILLED, 'Zadejte heslo.');
        $form->addCheckbox('remember', 'Zapamatovat si přihlášení');

        $form->addSubmit('login', 'Přihlásit');

        $form->addProtection('Odešlete prosím formulář znovu. Vypršela jeho časová platnost nebo máte vypnuté cookies (tedy zapnput).');

        $form->onSuccess[] = callback($this, 'loginFormSubmitted');
        return $form;
    }

    public function loginFormSubmitted($form) {
        try {
            if ($form['remember']->value) {
                $this->user->setExpiration('+20 days', false);
            } else {
                $this->user->setExpiration(0, true);
            }
            $this->user->login($form['username']->value, $form['password']->value);
            $this->application->restoreRequest($this->backlink);
            $this->redirect(':Personal:Dashboard:default');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

}