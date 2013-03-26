<?php

namespace OOB\Functional;

/**
 * Group adjacent object into array groups according to
 * equality on given attribute.
 * 
 * @author michal
 */
class GroupingIterator implements \Iterator {

    private $groupAttribute;

    /**
     *
     * @var \Iterator
     */
    private $innerIterator;
    private $key;
    private $valid;

    /**
     *
     * @var array currently grouped elements
     */
    private $group;

    /**
     * @param string $groupAttribute
     * @param \Iterator $data 
     */
    public function __construct($groupAttribute, \Iterator $data) {
        $this->innerIterator = $data;
        $this->groupAttribute = $groupAttribute;
    }

    public function current() {
        return $this->group;
    }

    public function key() {
        return $this->key;
    }

    public function next() {
        $this->loadGroup();
    }

    public function rewind() {
        $this->innerIterator->rewind();
        $this->valid = true;
        $this->loadGroup();
    }

    public function valid() {
        return $this->valid;
    }

    private function loadGroup() {
        if (!$this->innerIterator->valid()) {
            $this->valid = false;
            return;
        }
        $first = $this->innerIterator->current();
        $this->key = $first->{$this->groupAttribute};
        $this->group = array($first);

        $this->innerIterator->next();

        while ($this->innerIterator->valid()) {
            $record = $this->innerIterator->current();
            if ($record->{$this->groupAttribute} == $this->key) {
                $this->group[] = $record;
                $this->innerIterator->next();
            } else {
                break;
            }
        }
    }

}

?>
