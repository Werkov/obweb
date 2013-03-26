<?php

namespace Feed;

use FeedUtils\SourceInfo;
use FeedUtils\DatabaseNewsfeed;

/**
 * Facotry for local database feeds
 */
class LocalNewsFactory {

    public static function createNewsFeed($limit) {
        $club = \Nette\Environment::getConfig("club");
        $sourceInfo = new SourceInfo();
        $sourceInfo->name = "Novinky " . $club["shortName"];
        $sourceInfo->remote = false;
        //local provider doesn't need contain following infaromation
        $sourceInfo->feedURL = null;
        $sourceInfo->URL = null;
        $sourceInfo->description = null;

        $idPrefix = "brief";
        $itemName = "Krátké oznámení";
        $defaultMail = $club["feedMail"];
        $siteHostname = \Nette\Environment::getConfig('domain');

        $fl = \dibi::select("CONCAT(%s, b.id, '@', %s) AS id", $idPrefix, $siteHostname)
                ->select("%s AS name", $itemName)
                ->select("text AS [desc]")
                ->select("CONCAT(%s, ' ', '(', u.name, ' ', u.surname, ')') AS author", $defaultMail)
                ->select("published AS [datetime]")
                ->from(":t:public_brief AS b")
                ->leftJoin(":t:system_user AS u")->on("u.id = b.author_id")
                ->orderBy("[datetime] DESC");



        $dbnf = new DatabaseNewsfeed($sourceInfo, $fl);
        
        $dbnf->setLimit($limit);

        return $dbnf;
    }

    public static function createArticleFeed($private = false, $limit = null) {
        $club = \Nette\Environment::getConfig("club");
        $sourceInfo = new SourceInfo();
        $sourceInfo->name = "Články " . $club["shortName"];
        $sourceInfo->remote = false;
        //local provider doesn't need contain following infaromation
        $sourceInfo->feedURL = null;
        $sourceInfo->URL = null;
        $sourceInfo->description = null;

        $idPrefix = "article";
        //$itemName = "Krátké oznámení";
        $defaultMail = $club["feedMail"];
        $siteHostname = \Nette\Environment::getConfig('domain');

        $fl = \dibi::select("CONCAT(%s, a.id, '@', %s) AS id", $idPrefix, $siteHostname)
                ->select("a.title AS name")
                ->select("perex AS [desc]")
                ->select("CONCAT(%s, ' ', '(', u.name, ' ', u.surname, ')') AS author", $defaultMail)
                ->select("published AS [datetime]")
                ->select("a.url AS url")
                ->from(":t:public_article AS a")
                ->leftJoin(":t:system_user AS u")->on("u.id = a.author_id")
                ->orderBy("[datetime] DESC");

        if (!$private) {
            $fl->where("a.public = 1");
        }


        $dbnf = new DatabaseNewsfeed($sourceInfo, $fl);
        
        $dbnf->setLimit($limit);


        $pres = \Nette\Environment::getApplication()->getPresenter();
        $dbnf->setFormatCallback(function($item) use($pres) {
                    $item->url = $pres->link("//:Public:Publication:articleDetail", array("id" => $item->url));
                    return $item;
                });

        return $dbnf;
    }
    


    public static function createPhotoFeed($private = false, $limit = null) {
        $sourceInfo = new SourceInfo();
        $sourceInfo->name = "Fotografie";
        $sourceInfo->remote = false;
        //local provider doesn't need contain following infaromation
        $sourceInfo->feedURL = null;
        $sourceInfo->URL = null;
        $sourceInfo->description = null;

        $feed = new GroupedPhotoFeed($sourceInfo, $private);

        return $feed;
    }
    public static function createCommentFeed($private = false, $limit = null) {
        $sourceInfo = new SourceInfo();
        $sourceInfo->name = "Komentáře";
        $sourceInfo->remote = false;
        //local provider doesn't need contain following infaromation
        $sourceInfo->feedURL = null;
        $sourceInfo->URL = null;
        $sourceInfo->description = null;

        $feed = new GroupedCommentFeed($sourceInfo, $private);

        return $feed;
    }

    public static function createDocumentFeed($private = false, $limit = null) {
        $club = \Nette\Environment::getConfig("club");
        $sourceInfo = new SourceInfo();
        $sourceInfo->name = "Dokumenty " . $club["shortName"];
        $sourceInfo->remote = false;
        //local provider doesn't need contain following infaromation
        $sourceInfo->feedURL = null;
        $sourceInfo->URL = null;
        $sourceInfo->description = null;

        $idPrefix = "document";
        //$itemName = "";
        $defaultMail = $club["feedMail"];
        $siteHostname = \Nette\Environment::getConfig('domain');

        $fl = \dibi::select("CONCAT(%s, d.id, '@', %s) AS id", $idPrefix, $siteHostname)
                ->select("CONCAT(d.name, '.', t.extension) AS name")
                ->select("size AS [desc]")
                ->select("CONCAT(%s, ' ', '(', u.name, ' ', u.surname, ')') AS author", $defaultMail)
                ->select("published AS [datetime]")
                ->select("d.id AS url")
                ->from(":t:doc_file AS d")
                ->leftJoin(":t:system_user AS u")->on("u.id = d.author_id")
                ->leftJoin(":t:doc_directory AS dd")->on("dd.id = d.directory_id")
                ->leftJoin(":t:doc_filetype AS t")->on("t.id = d.filetype_id")
                ->orderBy("[datetime] DESC");

        if (!$private) {
            $fl->where("dd.public = 1");
        }


        $dbnf = new DatabaseNewsfeed($sourceInfo, $fl);
        
        $dbnf->setLimit($limit);

        $pres = \Nette\Environment::getApplication()->getPresenter();
        $dbnf->setFormatCallback(function($item) use($pres) {
                    $item->desc = "Velikost: " . \Nette\Templating\DefaultHelpers::bytes($item->desc);
                    $item->url = $pres->link("//:Public:Document:download", array("id" => $item->url));
                    return $item;
                });


        return $dbnf;
    }

    public static function createForumFeed($private = false, $limit = null) {
        $club = \Nette\Environment::getConfig("club");
        $sourceInfo = new SourceInfo();
        $sourceInfo->name = "Diskuze " . $club["shortName"];
        $sourceInfo->remote = false;
        //local provider doesn't need contain following infaromation
        $sourceInfo->feedURL = null;
        $sourceInfo->URL = null;
        $sourceInfo->description = null;

        $idPrefix = "post";
        //$itemName = "";
        //$defaultMail = $club["feedMail"];
        $siteHostname = \Nette\Environment::getConfig('domain');

        $fl = \dibi::select("CONCAT(%s, p.id, '@', %s) AS id", $idPrefix, $siteHostname)
                ->select("t.name AS name")
                ->select("p.text AS [desc]")
                //->select("CONCAT(%s, ' ', '(', u.name, ' ', u.surname, ')') AS author", $defaultMail)
                ->select("p.author AS author")
                ->select("[datetime] AS [datetime]")
                ->select("t.url AS url")
                ->from(":t:forum_post AS p")
                ->leftJoin(":t:forum_thread AS t")->on("t.id = p.thread_id")
                ->orderBy("[datetime] DESC");

        if (!$private) {
            $fl->where("t.public = 1");
        }


        $dbnf = new DatabaseNewsfeed($sourceInfo, $fl);

        $pres = \Nette\Environment::getApplication()->getPresenter();
        $dbnf->setFormatCallback(function($item) use($pres) {
                    $item->url = $pres->link("//:Public:Forum:thread", array("id" => $item->url));
                    return $item;
                });

        return $dbnf;
    }

    public static function createOrgNewsFeed(\Model\Org\Event $orgEvent, $limit = null) {
        $club = \Nette\Environment::getConfig("club");
        $sourceInfo = new SourceInfo();
        $sourceInfo->name = $orgEvent->name . " – novinky";
        $sourceInfo->remote = false;
        //local provider doesn't need contain following infaromation
        $sourceInfo->feedURL = null;
        $sourceInfo->URL = null;
        $sourceInfo->description = null;

        $idPrefix = $orgEvent->url . "_brief";
        $itemName = "Krátké oznámení";
        $defaultMail = $club["feedMail"];
        $siteHostname = $orgEvent->url . '.' . \Nette\Environment::getConfig('domain');

        $fl = \dibi::select("CONCAT(%s, b.id, '@', %s) AS id", $idPrefix, $siteHostname)
                ->select("%s AS name", $itemName)
                ->select("text AS [desc]")
                ->select("CONCAT(%s, ' ', '(', u.name, ' ', u.surname, ')') AS author", $defaultMail)
                ->select("published AS [datetime]")
                ->from(":t:org_brief AS b")
                ->leftJoin(":t:system_user AS u")->on("u.id = b.author_id")
                ->where("b.event_id = %i", $orgEvent->id)
                ->orderBy("[datetime] DESC");



        $dbnf = new DatabaseNewsfeed($sourceInfo, $fl);
        
        $dbnf->setLimit($limit);

        return $dbnf;
    }

}

