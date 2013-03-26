<?php
use Nette\Templating\FileTemplate as Template;
use Nette\Latte\Engine;
class MFUTemplate extends Template {

	function  __construct() {
		parent::__construct();
		$this->onPrepareFilters[] = callback($this, "registerFilters");
	}

	function registerFilters() {
		$this->registerFilter(new Engine());
	}

}