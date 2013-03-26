<?php

namespace Feed;

use FeedUtils\DatabaseCalendarFeed;

/**
 * Facotry for local database feeds
 */
class LocalCalendarFactory {

    public static function createRaceFeed() {
        $pres = \Nette\Environment::getApplication()->getPresenter();
        $idPrefix = "race";
        //$itemName = "";

        $siteUrl = $pres->context->httpRequest->getUrl()->getBaseUrl();

        $fl = \dibi::select("CONCAT(%s, r.id, '@', %s) AS uid", $idPrefix, $siteUrl)
                ->select("name AS summary")
                ->select("place AS location")
                ->select("begin AS dtstart")
                ->select("end AS dtend")
                ->select("NOW() as dtstamp")
                ->select("r.id AS url")
                ->from(":t:app_race AS r")
                ->where("YEAR(NOW()) - YEAR(begin) <= 2")
                ->where("status > 0")
                ->orderBy("[begin] DESC");

        $dbcf = new DatabaseCalendarFeed($fl);
        $dbcf->setWithTime(false);
        $dbcf->setLinkCallback(function($id) use($pres) {
                    return $pres->link(":Application:Race:detail", array("id" => $id));
                });

        return $dbcf;
    }
    
    public static function createDeadlineFeed() {

        $pres = \Nette\Environment::getApplication()->getPresenter();
        $idPrefix = "appDeadline";
        $itemName = " – uzávěrka přihlášek";
        $siteUrl = $pres->context->httpRequest->getUrl()->getBaseUrl();

        $fl = \dibi::select("CONCAT(%s, r.id, '@', %s) AS uid", $idPrefix, $siteUrl)
                ->select("CONCAT(name, %s) AS summary", $itemName)
                //->select("place AS location")
                ->select("deadline AS dtstart")
                //->select("end AS dtend")
                ->select("NOW() as dtstamp")
                ->select("r.id AS url")
                ->from(":t:app_race AS r")
                ->where("YEAR(NOW()) - YEAR(deadline) <= 2")
                ->where("status > 0")
                ->orderBy("[deadline] DESC");

        $dbcf = new DatabaseCalendarFeed($fl);
        $dbcf->setWithTime(false);

        $dbcf->setLinkCallback(function($id) use($pres) {
                    return $pres->link(":Application:Race:detail", array("id" => $id));
                });

        return $dbcf;
    }

    public static function createOrganizedFeed() {

        $pres = \Nette\Environment::getApplication()->getPresenter();
        $idPrefix = "org";
        $itemName = " – pořádáme";
        $siteUrl = $pres->context->httpRequest->getUrl()->getBaseUrl();

        $fl = \dibi::select("DISTINCT CONCAT(%s, e.id, '@', %s) AS uid", $idPrefix, $siteUrl)
                ->select("CONCAT(e.name, %s) AS summary", $itemName)
                //->select("place AS location")
                ->select("start AS dtstart")
                ->select("end AS dtend")
                ->select("NOW() as dtstamp")
                ->select("e.url AS url")
                ->from(":t:org_event AS e")
                ->leftJoin(":t:org_event2user AS eu")->on("eu.event_id = e.id")
                ->orderBy("[start] DESC");
        \Model\Org\Event::SqlVisibility($pres->getUser(), $fl);

        $dbcf = new DatabaseCalendarFeed($fl);
        $dbcf->setWithTime(false);

        $dbcf->setLinkCallback(function($id) use($pres) {
                    return $pres->link(":Organization:Homepage:default", array(\OrganizationModule\SitePresenter::URL_PARAM => $id));
                });

        return $dbcf;
    }
    
    public static function createEventFeed() {
        $pres = \Nette\Environment::getApplication()->getPresenter();
        $idPrefix = "event";
        //$itemName = "";

        $siteUrl = $pres->context->httpRequest->getUrl()->getBaseUrl();

        $fl = \dibi::select("CONCAT(%s, r.id, '@', %s) AS uid", $idPrefix, $siteUrl)
                ->select("summary")
                ->select("location")
                ->select("start AS dtstart")
                ->select("end AS dtend")
                ->select("NOW() as dtstamp")
                ->select("url")
                ->from(":t:public_event AS r")
                ->where("YEAR(NOW()) - YEAR(start) <= 2")
                ->orderBy("[start] DESC");

        $dbcf = new DatabaseCalendarFeed($fl);
        $dbcf->setWithTime(false);

        return $dbcf;
    }

}

