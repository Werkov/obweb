<?php

namespace OOB;

/**
 * Retrieve resultset with N rows with N/windowSize queries.
 * 
 * @author michal
 */
class WindowFluentIterator implements \Iterator {

    private $fluent;
    private $windowSize;

    public function __construct(\DibiFluent $data, $windowSize = 100) {
        $this->fluent = $data;
        $this->windowSize = $windowSize;
    }

    public function current() {
        return $this->windowIterator->current();
    }

    public function key() {
        return $this->windowIndex * $this->windowSize + $this->windowIterator->key();
    }

    public function next() {
        $this->windowIterator->next();
    }

    public function rewind() {
        $this->windowIndex = 0;
        $this->loadWindow();
    }

    public function valid() {
        if (!$this->windowIterator->valid()) {
            $this->windowIndex += 1;
            $this->loadWindow();
            return $this->windowIterator->valid();
        } else {
            return true;
        }
    }

    private function loadWindow() {
        $this->fluent->offset($this->windowIndex * $this->windowSize);
        $this->fluent->limit($this->windowSize);
        $this->windowIterator = $this->fluent->execute()->getIterator();
        $this->windowIterator->rewind();
    }

}

?>
