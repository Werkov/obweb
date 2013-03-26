<?php

/**
 * Description of RSSPresenter
 *
 * @author michal
 */

namespace PublicModule;

use FeedUtils\SimpleFeedAggregator;
use Feed\LocalCalendarFactory;
use FeedUtils\iCalGenerator;

final class CalendarPresenter extends PublicPresenter {

    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    public function createComponentIcalGenerator($name) {
        $this->context->createMainIcalGenerator($this, $name, $this->getBaseUrl());
    }

}