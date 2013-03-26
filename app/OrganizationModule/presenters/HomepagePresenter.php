<?php

/**
 * Description of HomepagePresenter
 *
 * @author michal
 */

namespace OrganizationModule;

use FeedUtils\SimpleFeedAggregator;
use FeedUtils\RemoteRSSFeed;
use Feed\LocalNewsFactory;
use Feed\LocalCalendarFactory;
use FeedUtils\NewsReader;
use FeedUtils\CalendarReader;

final class HomepagePresenter extends SitePresenter {

    public function actionDefault() {
        
    }

    public function renderDefault() {
        
    }

    public function renderRSS() {
        
    }

    public function createComponentRssGenerator($name) {
        $this->context->createOrgRssGenerator($this, $name, $this->template->baseUrl, $this->event);
        
    }

    public function createComponentNewsReader($name) {
        $this->getPresenter()->context->createOrgRssReader($this, $name, $this->event);
    }

}