<?php

namespace OOB;

use \Nette\Forms\Form;

class PublicFormPost extends \Nette\Forms\Container {

    public function __construct() {
        parent::__construct();

        $user = \Nette\Environment::getUser();

        $txt = $this->addText("author", "Jméno")
                ->addRule(Form::FILLED, "Jméno je povinné");

        $f = new \OOB\Texyla('Text příspěvku');
        $this->addComponent($f, 'text');
        $f->addRule(\Nette\Forms\Form::FILLED);
        $f->setTexyConfiguration('texyPublic');


        if ($user->isLoggedIn()) {
            $txt->setDefaultValue($user->getIdentity()->getFullname(false));
        } else {
            $this->addCaptcha('captcha', "Zkouška")
                    ->addRule(Form::FILLED, "Opište prosím text z obrázku.")
                    ->addRule($this["captcha"]->getValidator(), 'Zkuste opsání znovu.');
        }
    }

    protected static $texy = null;

}

class PublicFormRenderer extends \Nette\Forms\Rendering\DefaultFormRenderer {

    public function __construct() {
        
    }

    public function render(Form $form, $mode = NULL) {

        $template = new \Nette\Templating\FileTemplate(dirname(__FILE__) . DIRECTORY_SEPARATOR . "template.latte");
        $template->registerFilter(new \Nette\Latte\Engine());

        $template->form = parent::render($form, $mode);

        return (string) $template;
    }

}