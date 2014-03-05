<?php

/**
 * Description of BasePresenter
 *
 * @author michal
 */
use Navigation\ExternalNode;
use Navigation\Navigation;
use Navigation\StaticNode;
use Navigation\DynamicNode;
use Navigation\CyclicNode;

abstract class BasePresenter extends Nette\Application\UI\Presenter {

    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    const FLASH_OK = "info";
    const FLASH_ERROR = "error";

    protected function createComponentNavigation($name) {
        $nav = new Navigation($this, $name);

        $nav->setupRoot("Homepage", ":Public:Homepage:default");

        //TODO: set parentizers
        //TODO: set visibility callbacks
        //-- Public part --
        $nav->addChild(new StaticNode("Archiv článků", ":Public:Publication:articlesArchive"))->setMenu(false)
                ->addChild(new DynamicNode("Detail článku", ":Public:Publication:articleDetail"))
                ->setParentizer('\Model\Publication\Article::menuParentInfoUrl');

        $nav->addChild(new StaticNode("Archiv novinek", ":Public:Publication:newsArchive"))->setMenu(false);

        $nav->addChild(new StaticNode("Pořádáme", ":Public:Organization:default"));

        $nav->addChild(new StaticNode("Chyba", ":Error:default"))
                ->setMenu(false);

        //-- Public applications --
        $appNode = $nav->addChild(new StaticNode("Závody", ":Application:Race:default", true));

        //$appNode->addChild(new StaticNode("Chystané závody", ":Application:Race:default"));
        $appNode->addChild(new DynamicNode("Závody", ":Application:Race:default"))
                ->setExpander(function() {
                            $ret = array();
                            $a = new stdClass();
                            $a->text = "Chystané závody";
                            $a->old = false;
                            $ret[] = $a;
                            $a = new stdClass();
                            $a->text = "Staré závody";
                            $a->old = true;
                            $ret[] = $a;
                            return $ret;
                        })
                ->setParentizer(function($data) {
                            return array(Navigation::PINFO_THIS => array(Navigation::PINFO_TEXT => isset($data["old"]) && $data["old"] ? "Staré závody" : "Chystané závody"),
                                Navigation::PINFO_PARAMS => null);
                        });
        $raceNode = $appNode->addChild(new DynamicNode("Detail závodu", ":Application:Race:detail"))
                ->setParentizer('\Model\App\Race::menuParentInfo')
                ->setLeafLabel("Závod")
                ->setMenu(false);
        $entryNode = $raceNode->addChild(new DynamicNode("Přihlášky", ":Application:Entry:plist"))
                ->setParentizer('\Model\App\Race::menuParentInfo3')
                ->setLeafLabel("Přihlášky")
                //->setTranslationTable(array("parent" => "id"))
                ->setMenu(false);
        /* $entryNode = $raceNode->addChild(new StaticNode("Přihlášky", ":Application:Entry:plist"))
          ->setInheritParams(true)
          ->setTranslationTable(array("parent" => "id"))
          ->setMenu(false); */
        $entryNode->addChild(new StaticNode("Nová přihláška", ":Application:Entry:padd"))
                ->setInheritParams(true)
                //->setTranslationTable(array("parent" => "id"))
                ->setMenu(false);
        $entryNode->addChild(new DynamicNode("Přihláška úpravy", ":Application:Entry:pedit"))
                ->setParentizer('\Model\App\Entry::menuParentInfo')
                ->setLeafLabel("Přihláška")
                ->setMenu(false);
        $entryNode->addChild(new DynamicNode("Přihláška detail", ":Application:Entry:pshow"))
                ->setParentizer('\Model\App\Entry::menuParentInfo')
                ->setLeafLabel("Přihláška")
                ->setMenu(false);
        $appNode->addChild(new StaticNode("Moje konto", ":Application:Account:default"))
                ->setVisible(callback("ACL\\MenuModel::isRegistered"));

        $appNode->addChild(new StaticNode("Moje přihlášky", ":Application:Entry:listMe"))
                ->setVisible(callback("ACL\\MenuModel::isRegistered"));

        $nav->addChild(new StaticNode("Členové", ":Public:Member:default"))
                ->addChild(new DynamicNode("Profil člena", ":Public:Member:profile"))
                ->setParentizer('\Model\System\User::menuParentInfoReg')
                ->setLeafLabel("Profil")
                ->setMenu(false);

        $nav->addChild(new StaticNode("Fotografie", ":Public:Gallery:default"))
                ->addChild(new DynamicNode("Složka galerií", ":Public:Gallery:directory"))
                ->setMenu(false)
                ->setLeafLabel("Galerie")
                ->setParentizer('Model\Gallery\Directory::menuParentByUrl')
                ->addChild(new DynamicNode("Fotografie v galerii", ":Public:Gallery:gallery"))
                ->setMenu(false)
                ->setLeafLabel("Fotografie")
                ->setParentizer('Model\Gallery\Photo::menuParentInfo2');


        $nav->addChild(new StaticNode("Dokumenty", ":Public:Document:default"))
                ->addChild(new CyclicNode("Složka dokumentů", ":Public:Document:directory"))
                ->setMenu(false)
                ->setParentizer('\Model\Doc\Directory::menuParentInfo3')
                ->setLeafLabel("Dokumenty")
                ->setExpander('\Model\Doc\Directory::menuExpand');

        $nav->addChild(new StaticNode("Odkazy", ":Public:Links:default"));

        $rp = $nav->addChild(new StaticNode("Fórum", ":Public:Forum:default"))
                ->addChild(new DynamicNode("Téma", ":Public:Forum:topic"))
                ->setLeafLabel("Diskuze")
                ->setMenu(false)
                ->setParentizer('\Model\Forum\Topic::menuInfoByUrl');
        $rp->addChild(new StaticNode("Založit vlákno", ":Public:Forum:newThread"))
                ->setInheritParams(true)
                ->setTranslationTable(array("id" => "id"));
        $rp->addChild(new DynamicNode("Vlákno", ":Public:Forum:thread"))
                ->setLeafLabel("Diskuze")
                ->setParentizer('\Model\Forum\Thread::menuParentInfo3');



        /* $nav->addChild(new StaticNode("O nás", ":Public:StaticPage:"), true)
          ->setParameters(array("id" => "o-nas")) */
        $nav->addChild(new CyclicNode("Statická stránka", ":Public:StaticPage:default"))
                ->setMenu(true)
                ->setParentizer('\Model\Con\StaticPage::menuParentInfo')
                ->setExpander('\Model\Con\StaticPage::menuExpand2');



        $nav->addChild(new StaticNode("Přihlášení", ":Public:Authentication:login"))->setMenu(false);




        //-- Personal part --
        $perNode = $nav->addChild(new StaticNode("Uživatelská sekce", ":Personal:Dashboard:default", true))
                ->setVisible(callback("ACL\\MenuModel::isLogged"));

        $perNode->addChild(new StaticNode("Osobní nastavení", ":Personal:PersonalSettings:default"));
        $perNode->addChild(new StaticNode("Ankety", ":Personal:Survey:default"));

        //Gallery
        $rp = $perNode->addChild(new StaticNode("Fotogalerie", ":Personal:Gallery:list"))
                ->setVisible(callback("ACL\\MenuModel::Gallery"));

        $rp->addChild(new StaticNode("Přidat galerii", ":Personal:Gallery:add"))
                ->setMenu(false);
        $subrp = $rp->addChild(new DynamicNode("Fotografie v galerii", ":Personal:Photo:list"))
                ->setParentizer('\Model\Gallery\Photo::menuParentInfo3')
                ->setLeafLabel("Fotografie")
                ->setMenu(false);

        /* $subrp->addChild(new StaticNode("Přidat fotografii", ":Personal:Photo:add"))
          ->setInheritParams(true); */
        $subrp->addChild(new DynamicNode("Upravit fotografii", ":Personal:Photo:edit"))
                ->setParentizer('\Model\Gallery\Photo::menuParentInfo')
                ->setLeafLabel("Úprava fotografie");
        $subrp->addChild(new StaticNode("Upravit galerii", ":Personal:Gallery:edit"))
                ->setInheritParams(true)
                ->setTranslationTable(array("id" => "parent"));
        $subrp->addChild(new StaticNode("Upload fotografií", ":Personal:Gallery:upload"))
                ->setInheritParams(true)
                ->setTranslationTable(array("id" => "parent"));

        //Documents
        $rp = $perNode->addChild(new CyclicNode("Dokumenty", ":Personal:DocDirectory:list"))
                ->setParentizer('\Model\Doc\Directory::menuParentInfo2')
                ->setExpander('\Model\Doc\Directory::menuExpand')
                ->setVisible(callback("ACL\\MenuModel::Document"))
                ->setLeafLabel("Výpis podsložek");

        $rp->addChild(new StaticNode("Přidat složku", ":Personal:DocDirectory:add"))
                ->setMenu(false);
        $subrp = $rp->addChild(new DynamicNode("Dokumenty ve složce", ":Personal:File:list"))
                ->setParentizer('\Model\Doc\Directory::menuParentInfo2')
                ->setLeafLabel("Výpis souborů")
                ->setMenu(false);

        $subrp->addChild(new StaticNode("Přidat dokument", ":Personal:File:add"))
                ->setInheritParams(true);
        $subrp->addChild(new DynamicNode("Upravit dokument", ":Personal:File:edit"))
                ->setParentizer('\Model\Doc\File::menuParentInfo')
                ->setLeafLabel("Úprava dokumentu");
        $subrp->addChild(new StaticNode("Upravit složku", ":Personal:DocDirectory:edit"))
                ->setInheritParams(true)
                ->setTranslationTable(array("id" => "parent"));


        // Articles
        $rp = $perNode->addChild(new StaticNode("Články", ":Personal:Article:list"))
                ->setVisible(callback("ACL\\MenuModel::Article"));
        $rp->addChild(new StaticNode("Přidat článek", ":Personal:Article:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit článek", ":Personal:Article:edit"))
                ->setParentizer('\Model\Publication\Article::menuParentInfo')
                ->setLeafLabel("Úprava článku")
                ->setMenu(false);

        //News
        $rp = $perNode->addChild(new StaticNode("Novinky", ":Personal:Brief:list"))
                ->setVisible(callback("ACL\\MenuModel::Brief"));
        $rp->addChild(new StaticNode("Přidat novinku", ":Personal:Brief:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit novinku", ":Personal:Brief:edit"))
                ->setParentizer('\Model\Publication\Brief::menuParentInfo')
                ->setLeafLabel("Úprava novinky")
                ->setMenu(false);

        //Trusteeship
        $perNode->addChild(new ExternalNode("Správa majetku", $this->context->parameters['propertyUrl']))
                ->setVisible(callback("ACL\\MenuModel::Property"));

        // Help
//        $perNode->addChild(new StaticNode("Dokumentace", ":Public:StaticPage:default"))
//                ->setParameters(array('id' => 'dokumentace'))
//                ->setMenu(true);
        //-- AdminModule --
        $admNode = $nav->addChild(new StaticNode("Administrace", ":Personal:Dashboard:default", true))
                ->setVisible(callback("ACL\\MenuModel::Administration")); //TODO: jinačí rozhodování o viditelnosti
        //Gallery
        $rp = $admNode->addChild(new StaticNode("Odkazy", ":Admin:LinkCategory:list"))
                ->setVisible(callback("ACL\\MenuModel::Link"));

        $rp->addChild(new StaticNode("Přidat kategorii", ":Admin:LinkCategory:add"))
                ->setMenu(false);
        $subrp = $rp->addChild(new DynamicNode("Odkazy", ":Admin:Link:list"))
                ->setParentizer('\Model\Link\Link::menuParentInfo2')
                ->setLeafLabel("Seznam odkazů")
                ->setMenu(false);

        $subrp->addChild(new StaticNode("Přidat odkaz", ":Admin:Link:add"))
                ->setInheritParams(true);
        $subrp->addChild(new DynamicNode("Upravit odkaz", ":Admin:Link:edit"))
                ->setParentizer('\Model\Link\Link::menuParentInfo')
                ->setLeafLabel("Úprava odkazu");
        $subrp->addChild(new StaticNode("Upravit kategorii", ":Admin:LinkCategory:edit"))
                ->setInheritParams(true)
                ->setTranslationTable(array("id" => "parent"));

        //Survey
        $rp = $admNode->addChild(new StaticNode("Ankety", ":Admin:Survey:list"))
                ->setVisible(callback("ACL\\MenuModel::Survey"));

        $rp->addChild(new StaticNode("Přidat anketu", ":Admin:Survey:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Statistiky", ":Admin:Survey:stats"))
                ->setParentizer('\Model\Survey\Survey::menuParentInfo')
                ->setLeafLabel("Statistiky")
                ->setMenu(false);
        $subrp = $rp->addChild(new DynamicNode("Odpovědi", ":Admin:Answer:list"))
                ->setParentizer('\Model\Survey\Answer::menuParentInfo2')
                ->setLeafLabel("Odpovědi")
                ->setMenu(false);

        $subrp->addChild(new StaticNode("Přidat odpověď", ":Admin:Answer:add"))
                ->setInheritParams(true);
        $subrp->addChild(new DynamicNode("Upravit odpověď", ":Admin:Answer:edit"))
                ->setParentizer('\Model\Survey\Answer::menuParentInfo')
                ->setLeafLabel("Úprava odpovědi");
        $subrp->addChild(new StaticNode("Upravit anketu", ":Admin:Survey:edit"))
                ->setInheritParams(true)
                ->setTranslationTable(array("id" => "parent"));

        //Gallery directories
        $rp = $admNode->addChild(new StaticNode("Složky galerií", ":Admin:GalDirectory:list"))
                ->setVisible(callback("ACL\\MenuModel::GalDirectory"));
        $rp->addChild(new StaticNode("Přidat složku", ":Admin:GalDirectory:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit složku", ":Admin:GalDirectory:edit"))
                ->setParentizer('\Model\Gallery\Directory::menuParentInfo')
                ->setLeafLabel("Úprava složky")
                ->setMenu(false);
        
        //Calendar
        $rp = $admNode->addChild(new StaticNode("Kalendář", ":Admin:Event:list"))
                ->setVisible(callback("ACL\\MenuModel::CalEvent"));
        $rp->addChild(new StaticNode("Přidat událost", ":Admin:Event:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit událost", ":Admin:Event:edit"))
                ->setParentizer('\Model\Publication\Event::menuParentInfo')
                ->setLeafLabel("Úprava události")
                ->setMenu(false);

        //Static content
        $st = $admNode->addChild(new StaticNode('Statický obsah', ":Admin:StaticPage:list"))
                ->setVisible(callback("ACL\\MenuModel::Content"));
        $st->addChild(new StaticNode('Postranní bloky', ':Admin:Sideblock:list'))
                ->setVisible(callback("ACL\\MenuModel::Sideblock"));
        $rp = $st->addChild(new CyclicNode("Statické stránky", ":Admin:StaticPage:list"))
                ->setParentizer('\Model\Con\StaticPage::menuParentInfo2')
                ->setExpander('\Model\Con\StaticPage::menuExpand')
                ->setVisible(callback("ACL\\MenuModel::StaticPage"))
                ->setMenu(true);
        $rp->addChild(new StaticNode("Přidat stránku", ":Admin:StaticPage:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit stránku", ":Admin:StaticPage:edit"))
                ->setParentizer('\Model\Con\StaticPage::menuParentInfo')
                ->setLeafLabel("Úprava stránky")
                ->setMenu(false);

        //Forum
        $rp = $admNode->addChild(new StaticNode("Fórum", ":Admin:Topic:list"))
                ->setVisible(callback("ACL\\MenuModel::Forum"));

        $rp->addChild(new StaticNode("Přidat téma", ":Admin:Topic:add"))
                ->setMenu(false);

        $rp->addChild(new DynamicNode("Úprava vlákna", ":Admin:Thread:edit"))
                ->setParentizer('\Model\Forum\Thread::menuInfoById')
                ->setLeafLabel("Úprava vlákna")
                ->setMenu(false);

        $subrp = $rp->addChild(new DynamicNode("Vlákna", ":Admin:Thread:list"))
                ->setParentizer('\Model\Forum\Topic::menuParentById')
                ->setLeafLabel("Příspěvky")
                ->setMenu(false);


        $subrp->addChild(new StaticNode("Upravit téma", ":Admin:Topic:edit"))
                ->setInheritParams(true)
                ->setTranslationTable(array("id" => "parent"));

        $subrp->addChild(new DynamicNode("Příspěvky", ":Admin:Post:list"))
                ->setParentizer('\Model\Forum\Post::menuParentInfo2');

        $subrp->addChild(new DynamicNode("Příspěvky", ":Admin:Post:edit"))
                ->setParentizer('\Model\Forum\Post::menuParentInfo');

        //- People -
        $peopNode = $admNode->addChild(new StaticNode("Lidé", ":Admin:User:list", true))
                ->setVisible(callback("ACL\\MenuModel::People"));

        //Role
        $rp = $peopNode->addChild(new StaticNode("Role", ":Admin:Role:list"))
                ->setVisible(callback("ACL\\MenuModel::Role"));
        $rp->addChild(new StaticNode("Přidat roli", ":Admin:Role:add"))
                ->setMenu(false);
        $role = $rp->addChild(new DynamicNode("Upravit roli", ":Admin:Role:edit"))
                ->setParentizer('\Model\System\Role::menuParentInfo')
                ->setLeafLabel("Úprava")
                ->setMenu(false);

        //ACL
        $rp = $role->addChild(new StaticNode("ACL", ":Admin:Acl:list"))
                ->setVisible(callback("ACL\\MenuModel::Acl"))
                ->setInheritParams(true)
                ->setTranslationTable(array("parent" => "id"));
        /* $role->addChild(new StaticNode("Přidat ACL", ":Admin:Acl:add"))
          ->setMenu(false)
          ->setInheritParams(true)
          ->setTranslationTable(array("parent" => "id"));
          $rp->addChild(new DynamicNode("Upravit ACL", ":Admin:Acl:edit"))
          ->setParentizer('\Model\System\Acl::menuParentInfo')
          ->setLeafLabel("Úprava")
          ->setMenu(false); */
        //User
        $rp = $peopNode->addChild(new StaticNode("Uživatelé", ":Admin:User:list"))
                ->setVisible(callback("ACL\\MenuModel::User"));
        $rp->addChild(new StaticNode("Přidat uživatele", ":Admin:User:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit uživatele", ":Admin:User:edit"))
                ->setParentizer('\Model\System\User::menuParentInfo')
                ->setLeafLabel("Úprava uživatele")
                ->setMenu(false);

        //Member
        $rp = $peopNode->addChild(new StaticNode("Členové", ":Admin:Member:list"))
                ->setVisible(callback("ACL\\MenuModel::Member"));
        $rp->addChild(new StaticNode("Přidat člena", ":Admin:Member:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit člena", ":Admin:Member:edit"))
                ->setParentizer('\Model\App\Member::menuParentInfo')
                ->setLeafLabel("Úprava člena")
                ->setMenu(false);

        //Backer
        $rp = $peopNode->addChild(new StaticNode("Příznivci", ":Admin:Backer:list"))
                ->setVisible(callback("ACL\\MenuModel::Backer"));
        $rp->addChild(new StaticNode("Přidat příznivce", ":Admin:Backer:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit příznivce", ":Admin:Backer:edit"))
                ->setParentizer('\Model\App\Member::menuParentInfo')
                ->setLeafLabel("Úprava příznivce")
                ->setMenu(false);

        //- Application -
        $appNode = $admNode->addChild(new StaticNode("Přihlášky", ":Application:Race:list", true))
                ->setVisible(callback("ACL\\MenuModel::ApplicationsNode"));

        //Race
        $rp = $appNode->addChild(new StaticNode("Závody", ":Application:Race:list"))
                ->setVisible(callback("ACL\\MenuModel::Race"));
        $rp->addChild(new StaticNode("Přidat závod", ":Application:Race:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit závod", ":Application:Race:edit"))
                ->setParentizer('\Model\App\Race::menuParentInfo')
                ->setLeafLabel("Úprava závodu")
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Ceny kategorií", ":Application:Race:categories"))
                ->setMenu(false)
                ->setParentizer('\Model\App\Race::menuParentInfo')
                ->setLeafLabel("Ceny kategorií");

        $rp->addChild(new DynamicNode("Poplatky", ":Application:CostOption:list"))
                ->setMenu(false)
                ->setParentizer('\Model\App\Race::menuParentInfo2')
                ->setLeafLabel("Poplatky");

        $app = $rp->addChild(new DynamicNode("Přihlášky", ":Application:Entry:list"))
                ->setParentizer('\Model\App\Race::menuParentInfo2')
                ->setLeafLabel("Přihlášky")
                ->setMenu(false);

        $app->addChild(new StaticNode("Nová přihláška", ":Application:Entry:add"))
                ->setInheritParams(true);
        $app->addChild(new DynamicNode("Upravit přihlášku", ":Application:Entry:edit"))
                ->setParentizer('\Model\App\Entry::menuParentInfo')
                ->setLeafLabel("Úprava přihlášky");
        $app->addChild(new DynamicNode("Přihláška detail", ":Application:Entry:show"))
                ->setParentizer('\Model\App\Entry::menuParentInfo')
                ->setLeafLabel("Přihláška");
        $app->addChild(new StaticNode("Export ČSOB", ":Application:Entry:exportCsob"))
                ->setInheritParams(true)
                ->setMenu(false);
        $app->addChild(new StaticNode("Export součty", ":Application:Entry:exportSums"))
                ->setInheritParams(true)
                ->setMenu(false);
        //Accounts
        $rp = $appNode->addChild(new StaticNode("Účty", ":Application:Account:list"))
                ->setVisible(callback("ACL\\MenuModel::Account"));
        $rp->addChild(new StaticNode("Přidat účet", ":Application:Account:add"))
                ->setMenu(false);
        $rp->addChild(new StaticNode("Přiřazení účtů", ":Application:Account:users"))
                ->setMenu(true);
        $rp->addChild(new DynamicNode("Upravit účet", ":Application:Account:edit"))
                ->setParentizer('\Model\App\Account::menuParentInfo')
                ->setLeafLabel("Úprava účtu")
                ->setMenu(false);

        $acc = $rp->addChild(new DynamicNode("Historie", ":Application:AccountTransaction:list"))
                ->setParentizer('\Model\App\AccountTransaction::menuParentInfo2')
                ->setLeafLabel("Historie účtu")
                ->setMenu(false);

        $acc->addChild(new StaticNode("Vklad", ":Application:AccountTransaction:deposit"))
                ->setMenu(false)
                ->setInheritParams(true);

        $acc->addChild(new StaticNode("Výběr", ":Application:AccountTransaction:withdrawal"))
                ->setMenu(false)
                ->setInheritParams(true);

        //Tags
        $rp = $appNode->addChild(new StaticNode("Příznaky", ":Application:Tag:list"))
                ->setVisible(callback("ACL\\MenuModel::Tag"));
        $rp->addChild(new StaticNode("Přidat příznak", ":Application:Tag:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit příznak", ":Application:Tag:edit"))
                ->setParentizer('\Model\App\Tag::menuParentInfo')
                ->setLeafLabel("Úprava příznaku")
                ->setMenu(false);

        //Categories
        $rp = $appNode->addChild(new StaticNode("Kategorie", ":Application:Category:list"))
                ->setMenu(true)
                ->setVisible(callback("ACL\\MenuModel::RaceCategory"));
        // Appliers
        $rp = $appNode->addChild(new StaticNode("Práva přihlašování", ":Application:Permission:default"))
                ->setMenu(true)
                ->setVisible(callback("ACL\\MenuModel::AppPermission"));


        //- Organization -
        $appNode = $admNode->addChild(new StaticNode("Pořádání", ":Organization:Event:list", true))
                ->setVisible(callback("ACL\\MenuModel::OrganizationAll"));

        //Event
        $rp = $appNode->addChild(new StaticNode("Události", ":Organization:Event:list"))
                ->setVisible(callback("ACL\\MenuModel::OrgEvent"));
        $rp->addChild(new StaticNode("Přidat událost", ":Organization:Event:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit událost", ":Organization:Event:edit"))
                ->setParentizer('\Model\Org\Event::menuParentInfo')
                ->setLeafLabel("Úprava události")
                ->setMenu(false);

        $rac = $rp->addChild(new DynamicNode("Závody", ":Organization:Race:list"))
                ->setParentizer('\Model\Org\Race::menuParentInfo2')
                ->setLeafLabel("Seznam závodů")
                ->setMenu(false);
        $rac->addChild(new StaticNode("Nový závod", ":Organization:Race:add"))
                ->setInheritParams(true);
        $rac->addChild(new DynamicNode("Upravit závod", ":Organization:Race:edit"))
                ->setParentizer('\Model\Org\Race::menuParentInfo')
                ->setLeafLabel("Úprava závodu");

        $rac->addChild(new DynamicNode("Upravit závod", ":Organization:InformationValues:list"))
                ->setParentizer('\Model\Org\InformationValues::menuParentInfo2')
                ->setLeafLabel("Informace");

        $rac->addChild(new DynamicNode("Upravit závod", ":Organization:InformationValues:details"))
                ->setParentizer('\Model\Org\InformationValues::menuParentInfo2')
                ->setLeafLabel("Sestavení rozpisu");

        $rac->addChild(new DynamicNode("Upravit závod", ":Organization:InformationValues:instructions"))
                ->setParentizer('\Model\Org\InformationValues::menuParentInfo2')
                ->setLeafLabel("Sestavení pokynů");

        $new = $rp->addChild(new DynamicNode("Novinky", ":Organization:Brief:list"))
                ->setParentizer('\Model\Org\Brief::menuParentInfo2')
                ->setLeafLabel("Seznam novinek")
                ->setMenu(false);
        $new->addChild(new DynamicNode("Nová novinka", ":Organization:Brief:add"))
                ->setParentizer('\Model\Org\Brief::menuParentInfo2')
                ->setLeafLabel("Nová novinka");
        $new->addChild(new DynamicNode("Upravit novinku", ":Organization:Brief:edit"))
                ->setParentizer('\Model\Org\Brief::menuParentInfo')
                ->setLeafLabel("Úprava novinky");

        //Information pieces
        $rp = $appNode->addChild(new StaticNode("Kousky informací", ":Organization:Information:list"))
                ->setVisible(callback("ACL\\MenuModel::OrgInformation"));
        $rp->addChild(new StaticNode("Přidat kousek informace", ":Organization:Information:add"))
                ->setMenu(false);
        $rp->addChild(new DynamicNode("Upravit kousek informace", ":Organization:Information:edit"))
                ->setParentizer('\Model\Org\Information::menuParentInfo')
                ->setLeafLabel("Úprava informací")
                ->setMenu(false);
    }

    /**
     * Texyla loader factory
     * @return TexylaLoader
     */
    protected function createComponentTexyla() {
        $texyla = new TexylaLoader;

        $texyla->filters[] = new WebLoader\VariablesFilter(array(
                    "baseUri" => \Nette\Environment::getVariable("baseUrl") . '/',
                    "previewPath" => $this->link(":Texyla:preview"),
//                    "filesPath" => $this->link(":Texyla:listFiles"),
//                    "filesUploadPath" => $this->link(":Texyla:upload"),
//                    "filesMkDirPath" => $this->link(":Texyla:mkDir"),
//                    "filesRenamePath" => $this->link(":Texyla:rename"),
//                    "filesDeletePath" => $this->link(":Texyla:delete"),
                ));

        $texyla->addFile(WWW_DIR . "/js/texyla/js/texyla-init.js");

        return $texyla;
    }

    protected function loginRedirect() {
        if ($this->user->logoutReason === Nette\Http\User::INACTIVITY) {
            $this->flashMessage('Byl(a) jste příliš dlouho neaktivní a pro jistotu Vás systém odhlásil.');
        }else{
            $this->flashMessage('Musíte se přihlásit k přístupu na požadovanou stránku.');
        }
        $backlink = $this->application->storeRequest();
        $this->redirect(':Public:Authentication:login', array('backlink' => $backlink));
    }

    protected function beforeRender() {
        parent::beforeRender();
        if ($this->hasFlashSession() || $this->getComponent("flashMessages", false)) {
            $this->invalidateControl("flashMessages");
        }

        $club = $this->context->parameters['club'];
        $this->template->sitename = $club["fullName"];
        $this->template->sitetitle = $club["shortName"];
    }

    protected function createTemplate($class = null) {
        $template = parent::createTemplate($class);
        $template->registerHelperLoader('\OOB\Helpers::loader');
        return $template;
    }

    protected function getBaseUrl() {
        return \Nette\Environment::getConfig('baseUrl');
    }

}
