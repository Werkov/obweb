<?php

namespace Feed;

use FeedUtils\SourceInfo;
use FeedUtils\DatabaseNewsfeed;

/**
 * Facotry for local database feeds
 */
class GroupedCommentFeed extends GroupingDatabaseProvider {

    protected function createFluent() {
        $fl = \dibi::select('c.*')
                ->from(":t:public_comment AS c")
                ->leftJoin(":t:public_article AS a")->on("a.id = c.article_id")
                ->orderBy("[c.posted] DESC");

        if (!$this->private) {
            $fl->where("a.public = 1");
        }
        \Model\Publication\Article::findAll()->fetchAll(); //preload
        return $fl;
    }

    public function groupToItem($group) {
        $first = $group[0];
        $article = \Model\Publication\Article::find($first->article_id);
        $item = new \FeedUtils\NewsItem();
        $item->author = $first->author;//$this->club['feedMail'] . ' (' . \Model\System\User::find($first->author_id)->getFullName(false) . ')';
        $item->datetime = $first->posted;
        $item->desc = $first->text;
        $item->id = 'comment-' . $article->id . ':' . $first->id;
        $item->isPermalink = false;
        $item->name = $article->title . ' – komentáře';
        $item->sourceInfo = $this->sourceInfo;
        $item->url = $this->presenter->link('//:Public:Publication:articleDetail', array("id" => $article->url));
        return $item;
    }
    
    protected function getGroupColumn() {
        return 'article_id';
    }

}

