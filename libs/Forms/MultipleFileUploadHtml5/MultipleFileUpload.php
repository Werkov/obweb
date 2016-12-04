<?php

namespace OOB;

use Nette\Forms\Controls\UploadControl;
use Nette\Http\FileUpload;
use Nette\Utils\Html;

/**
 * MultipleFileUpload input control.
 *
 * @author     Michal Koutny
 */
class MultipleFileUpload extends UploadControl {

    /**
     * Generates control's HTML element.
     * @return Html
     */
    public function getControl() {
        $control = parent::getControl();
        $control->name = $control->name . "[]";
        $control->multiple = "multiple";

        return $control;
    }

    public function setValue($value) {
        if ($value instanceof FileUpload) {
            /* This branch is just a sugar. */
            $this->value = array($value);
        } else if (!is_array($value)) {
            $this->value = array();
        } else {
            $first = reset($value);
            if ($first instanceof FileUpload) {
                $this->value = $value;
            } else if (is_array($first)) {
                $transposed = array();
                for ($i = 0; $i < count($first); ++$i) {
                    $item = array();
                    foreach (array('name', 'type', 'size', 'tmp_name', 'error') as $key) {
                        $item[$key] = $value[$key][$i];
                    }
                    $transposed[] = $item;
                }
                $this->value = array_map(function($data) {
                            return new FileUpload($data);
                        });
            }
        }

        return $this;
    }

}
