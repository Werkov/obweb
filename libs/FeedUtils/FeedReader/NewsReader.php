<?php

namespace FeedUtils;

/**
 * Component showing aggregated feeds in one list.
 */
class NewsReader extends FeedReader {

    private $showPaginator = true;

    public function getShowPaginator() {
        return $this->showPaginator;
    }

    public function setShowPaginator($showPaginator) {
        $this->showPaginator = $showPaginator;
    }

    /**
     * Create template
     * @return Template
     */
    protected function createTemplate($class = null) {

        if (!$this->lazyLoad || $this->isLoaded) {
            $template = parent::createTemplate($class)->setFile(__DIR__ . "/templates/News/list.latte");
        } else {
            $template = parent::createTemplate($class)->setFile(__DIR__ . "/templates/News/unloaded.latte");
        }
        $template->registerHelperLoader("\OOB\Helpers::loader");
        return $template;
    }

//</editor-fold>
}

