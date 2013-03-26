<?php

namespace Feed;

use FeedUtils\SourceInfo;
use FeedUtils\DatabaseNewsfeed;

/**
 * Facotry for local database feeds
 */
class GroupedPhotoFeed extends GroupingDatabaseProvider {
    const THUMB_COUNT = 3;
    const THUMB_SIZE = 140;

    protected function createFluent() {
        $fl = \dibi::select('p.*')
                ->from(":t:gallery_photo AS p")
                ->leftJoin(":t:gallery_gallery AS g")->on("g.id = p.gallery_id")
                ->orderBy("[p.published] DESC");

        if (!$this->private) {
            $fl->where("g.public = 1");
        }
        \Model\Gallery\Gallery::findAll()->fetchAll(); // preload
        \Model\System\User::findAll()->fetchAll(); // preload
        return $fl;
    }

    public function groupToItem($group) {
        $first = $group[0];
        $gallery = \Model\Gallery\Gallery::find($first->gallery_id);
        $item = new \FeedUtils\NewsItem();
        $item->author = $this->club['feedMail'] . ' (' . \Model\System\User::find($first->author_id)->getFullName(false) . ')';
        $item->datetime = $first->published;
        $item->url = $this->presenter->link('//:Public:Gallery:gallery', array("parent" => $gallery->url));
        $item->desc = '<p>' . $gallery->desc . '</p><p class="thumbnails">';
        for ($i = 0; $i < min(count($group), self::THUMB_COUNT); ++$i) {
            $photo = \Model\Gallery\Photo::create($group[$i]);
            $item->desc .= '<a href="' . $item->url . '"><img src="' . $photo->getThumbnailUrl(self::THUMB_SIZE) . '" alt="' . $photo->file . '"/></a>';
        }
        $item->desc .= '</p>';
        $item->id = 'gallery-' . $gallery->id;
        $item->isPermalink = false;
        $item->name = $gallery->name;
        $item->sourceInfo = $this->sourceInfo;
        return $item;
    }

    protected function getGroupColumn() {
        return 'gallery_id';
    }

}

