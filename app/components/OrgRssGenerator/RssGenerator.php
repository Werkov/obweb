<?php


namespace OrganizationModule;
/**
 * Description of RssGenerator
 *
 * @author michal
 */
class RSSGenerator extends \FeedUtils\RSSGenerator {
    public function __construct($parent, $name, $aggregator, $URL, $event) {
        $club = $parent->context->parameters['club']['shortName'];                          
        parent::__construct($parent, $name, $aggregator, "{$event->name} ($club) – novinky", $URL, "Novinky pro událost {$event->name} pořádanou oddílem $club.");
    }
}

?>
