<?php
namespace ACL;
use Nette\Environment;
/**
 * Description of MenuModel
 *
 * @author Michal 
 */
class MenuModel {

   // --- Menu visibility callbacks ---
   public static function isRegistered() {
      return Environment::getUser()->isInRole("registered");
   }

   public static function isLogged() {
      return Environment::getUser()->isLoggedIn();
   }

   public static function Gallery() {
      return Environment::getUser()->isAllowed(\Model\Gallery\Gallery::create(), "list");
   }

   public static function Document() {
      return Environment::getUser()->isAllowed(\Model\Doc\Directory::create(), "list");
   }

   public static function Article() {
      return Environment::getUser()->isAllowed(\Model\Publication\Article::create(), "list");
   }

   public static function Brief() {
      return Environment::getUser()->isAllowed(\Model\Publication\Brief::create(), "list");
   }

   public static function Property() {
      return Environment::getUser()->isAllowed("property_property", "edit");
   }

   public static function Administration() {
      return self::Link() || self::Survey() || self::GalDirectory() ||self::CalEvent() ||
      self::StaticPage() || self::Forum() || self::People() || self::ApplicationsNode() || self::OrganizationAll();
   }

   public static function Link() {
      return Environment::getUser()->isAllowed(\Model\Link\Category::create(), "list");
   }
   
   public static function CalEvent() {
      return Environment::getUser()->isAllowed(\Model\Publication\Event::create(), "list");
   }

   public static function Survey() {
      return Environment::getUser()->isAllowed(\Model\Survey\Survey::create(), "list");
   }

   public static function GalDirectory() {
      return Environment::getUser()->isAllowed(\Model\Gallery\Directory::create(), "list");
   }

   public static function StaticPage() {
      return Environment::getUser()->isAllowed("con_staticPage", "list");
   }
   public static function Sideblock() {
      return Environment::getUser()->isAllowed("con_sideblock", "list");
   }
   public static function Content() {
      return self::StaticPage() || self::StaticPage();
   }

   public static function Forum() {
      return Environment::getUser()->isAllowed("forum_topic", "list");
   }

   public static function People() {
      return self::Role() || self::User() || self::Backer() || self::Member();
   }

   public static function Role() {
      return Environment::getUser()->isAllowed("system_role", "list");
   }

   public static function Acl() {
      return Environment::getUser()->isAllowed("system_role", "list");
   }

   public static function User() {
      return Environment::getUser()->isAllowed("system_user", "list");
   }

   public static function Backer() {
      return Environment::getUser()->isAllowed("system_user", "list");
   }

   public static function Member() {
      return Environment::getUser()->isAllowed("system_user", "list");
   }

   public static function ApplicationsNode() {
      return self::Race() || self::Account() || self::Tag(); // || self::Application();
   }

   public static function Race() {
      return Environment::getUser()->isAllowed("app_race", "list");
   }

   public static function Account() {
      return Environment::getUser()->isAllowed("app_account", "list");
   }

   public static function Tag() {
      return Environment::getUser()->isAllowed("app_tag", "list");
   }
   
   public static function RaceCategory() {
      return Environment::getUser()->isAllowed("app_category", "list");
   }

   public static function Application() {
      return Environment::getUser()->isAllowed("app_race", "applications");
   }

   public static function OrganizationAll() {
      return self::OrgEvent() || self::OrgInformation();
   }

   public static function OrgEvent() {
      return Environment::getUser()->isAllowed("org_event", "list");
   }

   public static function OrgInformation() {
      return Environment::getUser()->isAllowed("org_information", "list");
   }
   public static function AppPermission() {
      return Environment::getUser()->isAllowed("system_user", "changeAppliers");
   }

}

