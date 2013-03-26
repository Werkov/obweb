<?php

/**
 *
 * @author michal
 */

namespace PublicModule;

use Model\Publication\Article;
use Model\Publication\Comment;

final class PublicationPresenter extends PublicPresenter {

    public function renderArticleDetail($id) {

        $article = Article::find(array('url' => $id));
        if (!$article) {
            throw new \Nette\Application\BadRequestException("Neexistující článek.", 404);
        }

        if (!$article->public && !$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        }

        $this->template->article = $article;

        $this->getComponent("commentViewer")->addFluent("comments", $article->Comments->toFluent()->orderBy("posted"));
    }

    public function renderArticlesArchive($id) {

        if (!$this->getUser()->isLoggedIn()) {
            $where = array('public' => 1);
        } else {
            $where = null;
        }

        $tag = null;
        if ($this->getParameter('tag')) {
            $tag = \Model\Publication\Tag::find(array('url' => $this->getParameter('tag')));
        }

        $articles = Article::findAll($where)->toFluent();
        if ($tag) {
            $articles->leftJoin('[:t:public_article2tag] AS at')->on('[:t:public_article].id = at.article_id')
                    ->where('at.tag_id = %i', $tag->id);
        }

        $this->getComponent('articleViewer')->addFluent('articles', $articles->orderBy("published DESC"));

        $tags = \Model\Publication\Tag::findAll()->toFluent();
        $tags->rightJoin('[:t:public_article2tag] AS at')->on('[:t:public_tag].id = at.tag_id');
        if (!$this->getUser()->isLoggedIn()) {
            $tags->leftJoin('[:t:public_article] AS a')->on('at.article_id = a.id')
                    ->where('a.public = 1');
        }
        $tags->removeClause('select');
        $tags->select('[:t:public_tag].*, COUNT(1) AS frequency')
                ->groupBy('[:t:public_tag].id');
        $tags->orderBy('frequency DESC, name');

        $this->template->tags = $tags->fetchAll();
        $this->template->currentTag = $tag;
        $this->invalidateControl();
    }

    protected function createComponentCommentViewer($name) {
        $viewer = new \OOB\CommentViewer($this, $name);
        return $viewer;
    }

    protected function createComponentArticleViewer($name) {
        $viewer = new \OOB\ArticleViewer($this, $name);
        $viewer->setItemsPerPage(20); //TODO
        return $viewer;
    }

    protected function createComponentFrmNewComment($name) {
        $form = new \Nette\Application\UI\Form($this, $name);


        $form["post"] = new \OOB\PublicFormPost();



        $form->onSuccess[] = callback($this, "frmNewCommentSubmitted");

        $form->addSubmit('save', 'Odeslat');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);
        return $form;
    }

    public function frmNewCommentSubmitted($form) {
        $article = Article::find(array('url' => $this->getParameter('id')));
        $article_id = $article->id;
        if ($form['save']->isSubmittedBy()) {

            $values = $form->getValues();

            try {
                \dibi::begin();

                $texy = $this->context->texyPublic;
                $post = Comment::create($values["post"]);
                $post->text = $texy->process($post->text);
                //$post->IP = $this->getHttpRequest()->getRemoteAddress();
                $post->posted = new \DateTime();
                $post->article_id = $article_id;


                $post->save();

                $this->flashMessage("Komentář uložen..");

                \dibi::commit();
            } catch (DibiException $exp) {
                $form->addError("Chyba při ukládání komentáře.");
                return; //not redirect
            }
        }

        $this->redirect(":Public:Publication:articleDetail", array("id" => $article->url));
    }

}