<?php

namespace OOB;

use Model\Forum\Thread;
use Model\Forum\Post;

class PostViewer extends AbstractViewer {

    //<editor-fold desc="Variables">
    /**
     * @var boolean
     * @persistent
     */
    public $showForm;

    /**
     *
     * @var int
     * @persistent
     */
    public $reactionId;

    /**
     * 0: tree, 1: list
     * @var int
     * @persistent
     */
    public $mode = 0;

//</editor-fold>
    //<editor-fold desc="Getters & setters">
//</editor-fold>
//<editor-fold desc="Constructor">
    public function __construct($parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $session = $this->getSession();
        if (!isset($session->mode)) {
            $session->mode = $this->mode; //0
        }
    }

//</editor-fold>
//<editor-fold desc="Signals">
    public function handleShowForm($id) {
        $this->getComponent("frmNewPost")->getComponent("parent")->setValue($id);
        $this->showForm = true;
        $this->reactionId = $id;
    }

    public function formSubmitted($form) {
        $thread = Thread::find(array('url' => $this->getPresenter()->getParam("id")));
        $thread_id = $thread->id;

        if ($form['save']->isSubmittedBy()) {
            $values = $form->getValues();

            try {
                \dibi::begin();

                $texy = $this->getPresenter()->context->texyPublic;
                $post = Post::create($values["post"]);
                $post->text = $texy->process($post->text);
                $post->IP = \Nette\Environment::getHttpRequest()->getRemoteAddress();
                $post->datetime = new \DateTime();
                $post->thread_id = $thread_id;
                $post->parent = (int) $values["parent"] == "" ? null : (int) $values["parent"];

                $post->save();

                $this->getPresenter()->flashMessage("Příspěvek byl uložen.");

                \dibi::commit();
            } catch (DibiException $exp) {
                $form->addError("Chyba v ukládání.");
                return;
            }
        }
        $this->showForm = false;

        if ($this->getPresenter()->isAjax()) {
            $this->invalidateControl();
        } else {
            $this->getPresenter()->redirect(":Public:Forum:thread", array("id" => $thread->url));
        }
    }

    public function handleToggleView() {
        $session = $this->getSession();
        $session->mode = 1 - $session->mode;
    }

//</editor-fold>
    //<editor-fold desc="Rendering">

    protected function createTemplate($class = null) {
        $template = parent::createTemplate($class)->setFile(__DIR__ . "/viewer.latte");
        $template->registerHelperLoader("\OOB\Helpers::loader");
        return $template;
    }

    public function createComponentFrmNewPost($name) {
        $form = new \Nette\Application\UI\Form($this, $name);

        $form->addHidden("parent", null);



        $form["post"] = new PublicFormPost();


        $form->onSuccess[] = callback($this, "formSubmitted");

        $form->addSubmit('save', 'Odeslat');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);
        $form->getElementPrototype()->class($this->paginator->getAjaxClass());
        return $form;
    }

    protected function prepareData() {

        $this->mode = $this->getSession()->mode;

        $this->fluents["posts"]["fluent"]->orderBy(false);
        if ($this->mode == 0) {
            $this->fluents["posts"]["fluent"]->orderBy("lft");
        } elseif ($this->mode == 1) {
            $this->fluents["posts"]["fluent"]->orderBy("datetime");
        }

        parent::prepareData();
    }

//</editor-fold>

    protected function getSession() {
        return \Nette\Environment::getSession(__CLASS__ . "_" . $this->name);
    }

}

?>
