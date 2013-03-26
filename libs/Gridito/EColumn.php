<?php

namespace Gridito;

/**
 * Grid column
 *
 * @author Jan Marek, Michal Koutny
 * @license MIT
 */
class EColumn extends Column {
    // <editor-fold defaultstate="collapsed" desc="variables">

    /** form control for column
     * @var \Nette\Forms\Controls\BaseControl|callback
     */
    protected $control = null;

    /**
     * name of the field from result that is used as a value for control
     * @var string
     */
    protected $field;

    /**
     * Callback obtains as arguments form element and current row data.
     * @var callback|null
     */
    protected $valueSetter;

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="getters & setters">

    /**
     * @return \Nette\Forms\Controls\BaseControl|callback
     */
    public function getControl() {
        return $this->control;
    }

    /**
     *
     * @param \Nette\Forms\Controls\BaseControl|callback $control
     * @return EColumn
     */
    public function setControl($control) {
        $this->control = $control;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getField() {
        return $this->field;
    }

    /**
     *
     * @param string $field
     * @return EColumn
     */
    public function setField($field) {
        $this->field = $field;
        return $this;
    }

    public function getValueSetter() {
        return $this->valueSetter;
    }

    public function setValueSetter($valueSetter) {
        $this->valueSetter = $valueSetter;
    }

    // </editor-fold>

    /**
     * Render cell
     * @param mixed record
     */
    public function renderCell($record) {
        $grid = $this->parent->parent;
        if ($this->control !== null && (($record == null && $grid->editId == -1) || $grid->editId == $grid->GetModel()->GetUniqueId($record))) {
            if ($record != null) {
                if (is_callable($this->valueSetter)) {
                    call_user_func_array($this->valueSetter, array($grid["gridForm"][$this->getField()], $record));
                } else {
                    $grid["gridForm"][$this->getField()]->setValue($record->{$this->getField()});
                }
            }
            echo $grid["gridForm"][$this->getField()]->control;
        } else {
            if ($record != null)
                call_user_func($this->renderer ? : array($this, "defaultCellRenderer"), $record, $this);
        }
    }

}