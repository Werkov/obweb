<?php

/**
 * Description of HomepagePresenter
 *
 * @author michal
 */

namespace PublicModule;

use FeedUtils\SimpleFeedAggregator;
use FeedUtils\RemoteRSSFeed;
use Feed\LocalNewsFactory;
use Feed\LocalCalendarFactory;
use FeedUtils\NewsReader;
use FeedUtils\CalendarReader;

final class HomepagePresenter extends PublicPresenter {

    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    public function actionDefault($id) {
        if ($this->getParameter('subdomain') != null) {
            $this->redirect(301, 'this');
        }
        
        // legacy addresses
        if ($this->getParameter('q') != null) {
            $parameters = $this->getParameter();
            $id = $this->getHttpRequest()->getQuery('id', $id);
            switch ($parameters['q']) {
                case 'zavody':
                    $this->redirect(301, ':Application:Race:default');
                    break;
                case 'zavody/stare':
                    $this->redirect(301, ':Application:Race:default', array('old' => true));
                    break;
                case 'zavody/info':
                    //$race = \Model\App\Race::find($id);
                    $this->redirect(301, ':Application:Race:detail', array('id' => $id));
                    break;
                case 'zavody/poradame':
                    //$race = \Model\App\Race::find($id);
                    $this->redirect(301, ':Public:Organization:detail');
                    break;
                case 'galerie':
                    $this->redirect(301, ':Public:Gallery:default');
                    break;
                case 'galerie/detail':
                    $gallery = \Model\Gallery\Gallery::find($id);
                    $this->redirect(301, ':Public:Gallery:gallery', array('parent' => ($gallery ? $gallery->url : null)));
                    break;
                case 'clenove':
                    $this->redirect(301, ':Public:Member:default');
                    break;
                case 'clenove/detail':
                    $user = \Model\System\User::find($id);
                    $this->redirect(301, ':Public:Member:profile', array('id' => ($user ? $user->registration : null)));
                    break;
                case 'clanky/detail':
                    $article = \Model\Publication\Article::find($id);
                    $this->redirect(301, ':Public:Publication:articleDetail', array('id' => ($article ? $article->url : null)));
                    break;
                case 'clanky/rss':
                    $this->redirect(301, ':Public:Rss:default');
                    break;
                case 'forum/rss':
                    $this->redirect(301, ':Public:Rss:forum');
                    break;
                case 'dokumenty':
                    $this->redirect(301, ':Public:Document:default');
                    break;
                case 'dokumenty/soubor':
                    $this->redirect(301, ':Public:Document:download', array('id' => $id));
                    break;
                case 'mcr11':
                    $this->redirectUrl('http://mcr11.oobtrebic.cz/', 301);
                    break;
                case 'odkazy':
                    $this->redirect(301, ':Public:Links:default');
                    break;
                default:
                    $this->redirect(301, ':Public:Homepage:default');
                    break;
            }
        }
    }

    public function renderDefault() {
        
    }

    public function renderRSS() {
        
    }

    public function createComponentSideblockColumn($name) {
        return new \OOB\SideblockColumn($this, $name);
    }

    protected function createComponent($name) {
        $result = parent::createComponent($name);
        if (!$result) {
            $factoryMethod = 'create' . ucfirst($name);
            if (method_exists($this->context, $factoryMethod)) {
                return call_user_func_array(array($this->context, $factoryMethod), array($this, $name));
            }
        } else {
            return $result;
        }
    }

}
