<?php

namespace FeedUtils;

/**
 * Component showing aggregated feeds in one.
 */
class RemoteRSSFeed implements IFeedProvider {

    /**
     *
     * @var string from where to read
     */
    protected $url;

    /**
     *
     * @var bool lazy loading
     */
    protected $loaded;

    /**
     *
     * @var array of FeedItem
     */
    protected $items;

    /**
     *
     * @var SourceInfo
     */
    protected $sourceInfo;

    /**
     * Second from caching
     * @var int
     */
    protected $expiration = 300;

    public function __construct($url) {
        $this->url = $url;
        $this->loaded = false;
    }

    public function count() {
        $this->load();

        return count($this->items);
    }

    public function getItems() {
        $this->load();
        return $this->items;
    }

    public function getIterator() {
        $this->load();
        return new \ArrayIterator($this->items);
    }

    public function getSourceInfo() {
        $this->load();
        return $this->sourceInfo;
    }

    public function getExpiration() {
        return $this->expiration;
    }

    public function setExpiration($expiration) {
        $this->expiration = $expiration;
    }

    protected function load() {
        if ($this->loaded)
            return;

        $cache = \Nette\Environment::getCache('RSSFeed');
        $this->items = $cache->load($this->url);
        if ($this->items) {
            $this->loaded = true;
            return;
        }

        $this->items = array();

        $doc = new \DOMDocument();


        \set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
                });

        try {
            $doc->load($this->url);
        } catch (\ErrorException $exc) {
            $this->loaded = true;
        }

        \restore_error_handler();

        if ($this->loaded)
            return;


        $channels = $doc->getElementsByTagName("channel");
        //we take only the first channel
        $channel = $channels->item(0);
        $this->sourceInfo = new SourceInfo();
        $this->sourceInfo->remote = true;
        $this->sourceInfo->URL = $channel->getElementsByTagName("link")->item(0) ? $channel->getElementsByTagName("link")->item(0)->firstChild->textContent : null;
        $this->sourceInfo->feedURL = $this->url;
        $this->sourceInfo->name = $channel->getElementsByTagName("title")->item(0) ? $channel->getElementsByTagName("title")->item(0)->firstChild->textContent : null;
        $this->sourceInfo->description = $channel->getElementsByTagName("desciption")->item(0) ? $channel->getElementsByTagName("description")->item(0)->firstChild->textContent : null;


        foreach ($channel->getElementsByTagName("item") as $RSSitem) {

            $item = new NewsItem(array(
                        "id" => $RSSitem->getElementsByTagName("guid")->item(0) ? $RSSitem->getElementsByTagName("guid")->item(0)->firstChild->textContent : null,
                        "name" => $RSSitem->getElementsByTagName("title")->item(0) ? $RSSitem->getElementsByTagName("title")->item(0)->firstChild->textContent : null,
                        "datetime" => new \DateTime($RSSitem->getElementsByTagName("pubDate")->item(0) ? $RSSitem->getElementsByTagName("pubDate")->item(0)->firstChild->textContent : "now"),
                        "desc" => $RSSitem->getElementsByTagName("description")->item(0) ? $RSSitem->getElementsByTagName("description")->item(0)->firstChild->textContent : null,
                        "url" => $RSSitem->getElementsByTagName("link")->item(0) ? $RSSitem->getElementsByTagName("link")->item(0)->firstChild->textContent : null,
                        "author" => $RSSitem->getElementsByTagName("author")->item(0) ? $RSSitem->getElementsByTagName("author")->item(0)->firstChild->textContent : null,
                    ));
            if ($RSSitem->getElementsByTagName("guid")->item(0)) {
                $item->isPermalink = \strcmp($RSSitem->getElementsByTagName("guid")->item(0)->getAttribute("isPermaLink"), "true") == 0;
            }
            $item->sourceInfo = $this->sourceInfo;

            $this->items[] = $item;
        }

        \usort($this->items, function($a, $b) {
                    return $b->getSortingKey() - $a->getSortingKey();
                });

        $this->loaded = true;
        $cache->save($this->url, $this->items, array(\Nette\Caching\Cache::EXPIRATION => time() + $this->getExpiration()));
    }

}

