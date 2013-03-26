<?php

/**
 *
 * @author michal
 */

namespace PublicModule;

use Model\Con\StaticPage;


final class StaticPagePresenter extends PublicPresenter {

   public function renderDefault($id) {
      if($id === null){
	 $id = "o-nas";
      }
      $page = StaticPage::find(array("url" => $id));
      if(!$page){
	 throw new \Nette\Application\BadRequestException("Neexistující stránka.", 404);
      }

      $this->template->page = $page;
   }   

}