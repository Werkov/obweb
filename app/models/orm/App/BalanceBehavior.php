<?php

namespace Model;

/**
 * Adds behavior to entities that automatically maintains column
 * with cool url index.
 *
 */
class CoolUrlBehavior implements \Ormion\Behavior\IBehavior {

    /**
     * Name of the column that is source of data for cool URL.
     * @var string|null
     */
    public $sourceColumn;

    /**
     * Name of the column that stores cool URLs.
     * @var string
     */
    public $urlColumn;

    public function __construct($sourceColumn = 'name', $urlColumn = 'url') {
        $this->sourceColumn = $sourceColumn;
        $this->urlColumn = $urlColumn;
    }

    public function setUp(\Ormion\IRecord $record) {
        $behavior = $this;
        $setUrl = function(\Ormion\IRecord $record) use($behavior) {
                    $record->{$behavior->urlColumn} = \Nette\Utils\Strings::truncate(\Nette\Utils\Strings::webalize($record->{$behavior->sourceColumn}), $record->getConfig()->getSize($behavior->urlColumn), '');
                };

        $record->onBeforeInsert[] = $setUrl;
        $record->onBeforeUpdate[] = $setUrl;
    }

}
