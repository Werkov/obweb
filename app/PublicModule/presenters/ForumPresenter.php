<?php

/**
 *
 * @author michal
 */

namespace PublicModule;

use Model\Forum\Topic;
use Model\Forum\Thread;
use Model\Forum\Post;

final class ForumPresenter extends PublicPresenter {

    public function renderDefault() {

        $inner = \dibi::select("MAX(x.datetime) AS maximum") //select only maximal row for each topic
                ->from("forum_post AS x")
                ->leftJoin("forum_thread AS y")->on("x.thread_id = y.id")
                ->where("y.topic_id = `to`.id");
        if (!$this->getUser()->isLoggedIn()) {
            $inner->where("y.public = 1");
        }

        $fl = \dibi::select("to.id, to.name, to.url, to.desc, th.id AS thread_id, th.url AS thread_url, th.name AS thread, p.datetime, p.author")
                ->from("forum_topic AS [to]")
                ->leftJoin("forum_thread AS th")->on("th.topic_id = to.id")
                ->leftJoin("forum_post AS p")->on("p.thread_id = th.id") //gets great table with all posts
                ->where("p.datetime = (" . $inner->__toString() . ")")
                ->or("(p.datetime IS NULL AND th.id IS NULL)")
                ->groupBy("[to.id]") //it could happen that there are more posts in the same topic with same time, we undeterministically choose one
                ->orderBy("[to.name]");

        if (!$this->getUser()->isLoggedIn()) {
            $fl->where("th.public = 1 OR (p.datetime IS NULL AND th.public IS NULL)");
        }



        $this->getComponent("topicViewer")->addFluent("topics", $fl);
    }

    public function renderTopic($id) {
        $topic = Topic::find(array('url' => $id));
        if (!$topic) {
            throw new \Nette\Application\BadRequestException("Neexistující téma.", 404);
        }


        $fl = \dibi::select("th.id, th.url, th.name, p.datetime, p.author")
                ->from("forum_thread AS th")
                ->leftJoin("forum_post AS p")->on("p.thread_id = th.id") //gets great table with all posts
                ->where("th.topic_id = %i", $topic->id)
                ->where("(p.datetime IS NULL OR p.datetime = (" . \dibi::select("MAX(x.datetime) AS maximum") //select only maximal row for each topic
                        ->from("forum_post AS x")
                        ->where("x.thread_id = th.id")->__toString() . "))")
                ->groupBy("[th.id]") //error //it could happen that there are more posts in the same thread with same time, we undeterministically choose one
                ->orderBy("p.datetime DESC");


        if (!$this->getUser()->isLoggedIn()) {
            $fl->where("th.public = 1 OR (p.datetime IS NULL AND th.public IS NULL)");
        }

        $this->getComponent("threadViewer")->addFluent("threads", $fl);
        $this->template->topic = $topic;
    }

    public function renderThread($id) {
        $thread = Thread::find(array('url' => $id));
        if (!$thread) {
            throw new \Nette\Application\BadRequestException("Neexistující vlákno.", 404);
        }

        if (!$thread->public && !$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        }


        $fl = $thread->Posts->toFluent();
        $fl->orderBy("datetime DESC"); //anebo ltr



        $this->getComponent("postViewer")->addFluent("posts", $fl, 'Model\Forum\Post');
        //$this->getComponent("postViewer")->setItemsPerPage(4);
        $this->template->thread = $thread;
    }

    public function actionNewThread($id) {
        $topic = Topic::find(array('url' => $id));
        if (!$topic) {
            throw new \Nette\Application\BadRequestException("Neexistující téma.", 404);
        }

        $this->template->topic = $topic;
    }

//<editor-fold desc="Components">
    protected function createComponentTopicViewer($name) {
        $viewer = new \OOB\TopicViewer($this, $name);
        return $viewer;
    }

    protected function createComponentThreadViewer($name) {
        $viewer = new \OOB\ThreadViewer($this, $name);
        return $viewer;
    }

    protected function createComponentPostViewer($name) {
        $viewer = new \OOB\PostViewer($this, $name);
        return $viewer;
    }

    protected function createComponentFrmNewThread($name) {
        $form = new \Nette\Application\UI\Form($this, $name);
        $form->addText("name", "Název vlákna")
                ->addRule(\Nette\Forms\Form::FILLED, "Název je nutno vyplnit.");

        if ($this->getUser()->isLoggedIn()) {
            $form->addCheckbox("public", "Veřejné vlákno")->setDefaultValue(true);
        }

        $form["post"] = new \OOB\PublicFormPost();



        $form->onSuccess[] = callback($this, "frmNewThreadSubmitted");

        $form->addSubmit('save', 'Odeslat');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);
        return $form;
    }

//</editor-fold>
//<editor-fold desc="Signals">
    public function frmNewThreadSubmitted($form) {
        $topic = Topic::find(array('url' => $this->getParameter('id')));
        $topic_id = $topic->id;
        if ($form['save']->isSubmittedBy()) {

            $values = $form->getValues();

            try {
                \dibi::begin();
                $thread = Thread::create();
                $thread->topic_id = $topic_id;
                $thread->name = $values["name"];
                if ($this->getUser()->isLoggedIn()) {
                    $thread->public = $values["public"];
                } else {
                    $thread->public = true;
                }

                $thread->created = new \DateTime();
                $thread->save();

                $texy = $this->context->texyPublic;
                $post = Post::create($values["post"]);
                $post->text = $texy->process($post->text);
                $post->IP = $this->getHttpRequest()->getRemoteAddress();
                $post->datetime = new \DateTime();
                $post->thread_id = $thread->id;
                $post->parent = null;

                $post->save();

                $this->flashMessage("Vlákno '" . $thread->name . "' bylo založeno.");
                $this->redirect(":Public:Forum:Thread", array("id" => $thread->url));
                \dibi::commit();
            } catch (DibiException $exp) {
                $form->addError("Vlákno '" . $thread->name . "' se nepovedlo založit.");
                return; //not redirect
            }
        } else {
            $this->redirect(":Public:Forum:Topic", array("id" => $topic->url));
        }
    }

//</editor-fold>
}