<?php

namespace Navigation;

/**
 * Description of MenuNode
 *
 * @author Michal
 */
class PathNode extends \Nette\Object {

	protected $link = "";
	protected $text;
	protected $title;
	protected $tag;
	protected $action;
	protected $parameters = array();

	public function __construct($text, $action = "", $parameters = array(), $title = "", $tag = null)
	{
		$this->text = $text;
		$this->action = $action;
		$this->parameters = $parameters;
		$this->title = $title;
		$this->tag = $tag;
	}

	// <editor-fold desc="Getters and setters">
	public function getLink()
	{
	   //\dump($this->parameters);
		if($this->action !== "" && $this->link === "")
			$this->link = \Nette\Environment::getApplication()->getPresenter()->link($this->action, $this->parameters);


		return $this->link;
	}

	public function getText()
	{
		return $this->text;
	}

	public function setText($value)
	{
		$this->text = $value;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setTitle($value)
	{
		$this->title = $value;
	}

	public function getTag()
	{
		return $this->tag;
	}

	public function setTag($value)
	{
		$this->tag = $value;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function setAction($action)
	{
		$this->action = $action;
		$this->link = "";
	}

	public function getParameters()
	{
		return $this->parameters;
	}

	public function setParameters($parameters)
	{
		$this->parameters = $parameters;
		$this->link = "";
	}

	// </editor-fold>
}
