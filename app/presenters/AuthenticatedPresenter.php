<?php

/**
 *
 * @author michal
 */
abstract class AuthenticatedPresenter extends BasePresenter {

   /**
    * @var bool When set to true suppresses default authentication test. You MUST perform your own test.
    */
   protected $suppressAuth = false;

   protected function startup() {
      //AUTH OFF
      parent::startup();
      
      if (!$this->suppressAuth && !$this->user->isLoggedIn()) {
	 $this->loginRedirect();
      }      
   }
   
   
   protected function beforeRender() {
       parent::beforeRender();       
       $this->context->getService('frecency')->log($this);
   }

}