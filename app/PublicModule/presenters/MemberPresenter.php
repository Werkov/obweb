<?php

/**
 * Description of AuthenticationPresenter
 *
 * @author michal
 */

namespace PublicModule;

final class MemberPresenter extends PublicPresenter {

   public function actionProfile($id) {
      $user = \Model\System\User::find(array('registration' => $id));
      if(!$id || !$user || $user->active == false){
	 throw new \Nette\Application\BadRequestException("Neexistující uživatel.", 404);
      }
      
      $this->getComponent("userProfile")->setUser($user);
      $this->getComponent("userProfile")->setFull($user->public || $this->getUser()->isLoggedIn());
      $this->template->listedUser = $user;
   }



   /*    * ******************* components ****************************** */

   /**
    * @return mixed
    */
   protected function createComponentGrdMembers($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(
			      \dibi::select("m.user_id AS muser_id, CONCAT(u.surname, ' ', u.name) AS name, u.registration AS registration, m.licence AS licence")
			      ->from(":t:app_member AS m")
			      ->leftJoin(":t:system_user AS u")->on("u.id = m.user_id")
			      ->where("m.active = 1") //user should be active implicitely from administration procedures
      ));

      $grid->getModel()->setPrimaryKey("m.user_id");


      // columns
      $grid->addColumn("name", "Jméno")->setSortable(true);
      $grid->addColumn("registration", "Registrace")->setSortable(true);
      $grid->addColumn("licence", "Licence")->setSortable(true);

      $grid->getModel()->setSorting("name", "ASC");

      // buttons
      $pres = $this;

      $grid->addButton("profile", "Profil")->setLink(function ($row) use($pres) {
		 return $pres->link("profile", array("id" => $row->registration));
	      });


      //settings
      $grid->setItemsPerPage(1000); //TODO solve this


      return $grid;
   }

   protected function createComponentGrdBackers($name) {

      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(
			      \dibi::select("m.user_id AS muser_id, CONCAT(u.surname, ' ', u.name) AS name, u.registration AS registration")
			      ->from(":t:app_backer AS m")
			      ->leftJoin(":t:system_user AS u")->on("u.id = m.user_id")
			      ->where("m.active = 1") //user should be active implicitely from administration procedures
      ));

      $grid->getModel()->setPrimaryKey("m.user_id");


      // columns
      $grid->addColumn("name", "Jméno")->setSortable(true);
      $grid->addColumn("registration", "Kvaziregistrace")->setSortable(true);      

      $grid->getModel()->setSorting("name", "ASC");

      // buttons
      $pres = $this;

      $grid->addButton("profile", "Profil")->setLink(function ($row) use($pres) {
		 return $pres->link("profile", array("id" => $row->registration));
	      });


      //settings
      $grid->setItemsPerPage(1000); //TODO solve this


      return $grid;
   }

   protected function createComponentUserProfile($name) {
      $profile = new \OOB\UserProfile($this, $name);
      return $profile;
   }

}