<?php

namespace Navigation;

/**
 *
 * @author Michal Koutny
 */
class ExternalNode extends StaticNode {

    private $url;

    public function __construct($text, $url, $hierarchyDelimiter = false) {
        parent::__construct($text, "", $hierarchyDelimiter);
        $this->url = $url;
    }

    public function getMenuNode() {
        $node = parent::getMenuNode();
        $node->setLink($this->url);
        return $node;
    }

}
