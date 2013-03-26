<?php

/**
 * Description of RSSPresenter
 *
 * @author michal
 */

namespace PublicModule;

use FeedUtils\SimpleFeedAggregator;
use Feed\LocalNewsFactory;
use FeedUtils\RSSGenerator;
use FeedUtils\RemoteRSSFeed;

final class RssPresenter extends PublicPresenter {

    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    public function actionPrivate() {
        $token = $this->getParam("token");
        $user = \Model\System\Token::checkToken($token);
        if (!$user) {
            throw new \Nette\Application\BadRequestException("Neplatný token.", 403);
        }
    }

    public function createComponentRssGenerator($name) {
        $this->context->createMainRssGenerator($this, $name, $this->getBaseUrl());
    }

    public function createComponentForumRssGenerator($name) {
        $this->context->createForumRssGenerator($this, $name,$this->getBaseUrl() );
    }

    public function createComponentPrivateRssGenerator($name) {
        $this->context->createPrivateRssGenerator($this, $name,$this->getBaseUrl());
    }

}