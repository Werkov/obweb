<?php

namespace Gridito;

use Nette\ComponentModel\Container, Nette\Environment, Nette\Utils\Paginator;

/**
 * Grid
 *
 * @author Jan Marek, Michal Koutny
 * @license MIT
 */
class EGrid extends Grid {

   /**
    * Primary key of currently editted row or -1 for new record.
    * @var int
    */
   public $editId;
   /**
    *
    * @var bool
    */
   public $showForm = false;
   /**
    *
    * @var callback
    */
   protected $submitCallback;
   /**
    * @var bool
    */
   protected $showAdd = true;
   /**
    * @var bool
    */
   protected $showEdit = true;
   /**
    * @var callback
    */
   protected $updateForm;

   /**
    *
    * @return bool
    */
   public function showAdd() {
      return $this->showAdd;
   }

   /**
    *
    * @return bool
    */
   public function showEdit() {
      return $this->showEdit;
   }

   /**
    * @param bool $value
    * @return EGrid
    */
   public function setShowEdit($value) {
      $this->showEdit = $value;
      return $this;
   }

   /**
    * @param bool $value
    * @return EGrid
    */
   public function setShowAdd($value) {
      $this->showAdd = $value;
      return $this;
   }

   public function setSubmitCallback($callback) {
      $this->submitCallback = $callback;
      return $this;
   }

   public function setUpdateForm($callback) {
      $this->updateForm = $callback;
      return $this;
   }

   /**
    * Add column
    * @param string name
    * @param string label
    * @param array options
    * @return EColumn
    */
   public function addColumn($name, $label = null, array $options = array()) {
      $column = new EColumn($this["columns"], $name);
      $column->setLabel($label);
      $this->setOptions($column, $options);
      return $column;
   }

   /**
    * Create template
    * @return Template
    */
   protected function createTemplate($class = null) {
      return parent::createTemplate($class)->setFile(__DIR__ . "/templates/egrid.phtml");
   }

   public function handleEdit($id) {
      $this->editId = $id;
      $this->showForm = true;

      if ($this->presenter->isAjax()) {
	 $this->invalidateControl();
      }
   }

   public function handleAdd() {
      $this->editId = -1;
      $this->showForm = true;

      if ($this->presenter->isAjax()) {
	 $this->invalidateControl();
      }
   }

   protected function createComponentGridForm($name) {
      $f = new \Nette\Application\UI\Form($this, $name);
      $f->addProtection();
      foreach ($this["columns"]->getComponents() as $column) {
	 if ($column->getControl() !== null) {
	    $f->addComponent($column->getControl(), $column->getField());
	 }
      }

      $f->addHidden("editId", $this->editId);


      $f->addSubmit('save', 'Uložit');
      $f->addSubmit('cancel', 'Storno')->setValidationScope(NULL);
      $f->onSuccess[] = callback($this, 'gridFormSubmitted');
      $f->getElementPrototype()->class('ajax');


      if (\is_callable($this->updateForm))
	 call_user_func($this->updateForm, $f, $f["editId"]->getValue());
      return $f;
   }

   public function gridFormSubmitted(\Nette\Application\UI\Form $form) {
      if ($form['save']->isSubmittedBy()) {
	 try {
	    if (\is_callable($this->submitCallback))
	       call_user_func($this->submitCallback, $form);

	    //$this->flashMessage("OK", \BasePresenter::FLASH_OK); //overriden with actuall callback
	    $this->paginator->getPaginator()->setItemCount($this->getModel()->count(true)); //refresh number of items
	    $this->showForm = false;
	    $this->editId = false;
	 } catch (\ModelException $e) {
	    $this->flashMessage("Chyba: " . $e->getMessage(), \BasePresenter::FLASH_ERROR);

	    if ($this->presenter->isAjax())
	       $this->invalidateControl();
	    return;
	 }
      }
      if ($this->presenter->isAjax()) {
	 $this->invalidateControl();
      } else {
	 $this->redirect("this");
      }
   }

   public function getFirstField() {
      foreach ($this["columns"]->getComponents() as $column) {
	 if ($column->getControl() !== null) {
	    return $column->getField();
	 }
      }
   }

}