<?php

namespace Feed;

use FeedUtils\SourceInfo;
use FeedUtils\DatabaseNewsfeed;

/**
 * Facotry for local database feeds
 */
abstract class GroupingDatabaseProvider implements \FeedUtils\IFeedProvider {

    private $fluent;
    protected $sourceInfo;
    protected $private;
    protected $club;
    protected $domain;
    protected $presenter;

    public function getSourceInfo() {
        return $this->sourceInfo;
    }

    public function setSourceInfo($sourceInfo) {
        $this->sourceInfo = $sourceInfo;
    }

    public function __construct(SourceInfo $sourceInfo, $private = false) {        
        $this->sourceInfo = $sourceInfo;
        $this->private = $private;
        $this->fluent = $this->createFluent();
        $this->club = \Nette\Environment::getConfig('club');
        $this->domain = \Nette\Environment::getConfig('domain');
        $this->presenter = \Nette\Environment::getApplication()->getPresenter();
    }

    public function getIterator() {
        return new \Nette\Iterators\Mapper(
                        new \OOB\Functional\GroupingIterator($this->getGroupColumn(), new \OOB\WindowFluentIterator($this->fluent)),
                        array($this, 'groupToItem'));
    }

    abstract protected function createFluent();
    abstract public function groupToItem($group);
    abstract protected function getGroupColumn();

}

