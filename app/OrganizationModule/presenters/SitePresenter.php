<?php

/**
 * Description of HomepagePresenter
 *
 * @author michal
 */

namespace OrganizationModule;

abstract class SitePresenter extends \PublicModule\PublicPresenter {

    const URL_PARAM = 'subdomain';
    const PREVIEW_PARAM = 'preview';

    /** @persistent */
    public $subdomain;

    /** @persistent */
    public $preview;

    /**
     * @var \Model\Org\Event
     */
    protected $event;

    /**
     * @var \Model\Org\Race
     */
    protected $currentRace;

    /**
     * Formats layout template file names.
     * @return array
     */
    public function formatLayoutTemplateFiles() {
        $name = $this->getName();
        $presenter = substr($name, strrpos(':' . $name, ':'));
        $id = $this->getParameter(self::URL_PARAM);
        $layout = $this->layout ? $this->layout : 'layout';

        $basicDir = $this->context->parameters['organization']['templatePath'];
        $extendedDir = $this->context->parameters['organization']['eventsPath'];

        return array(
            "$extendedDir/$id/$presenter/@$layout.latte",
            "$extendedDir/$id/$presenter.@$layout.latte",
            "$extendedDir/$id/@$layout.latte",
            "$basicDir/$presenter/@$layout.latte",
            "$basicDir/$presenter.@$layout.latte",
            "$basicDir/@$layout.latte",
        );
    }

    /**
     * Formats view template file names.
     * @return array
     */
    public function formatTemplateFiles() {
        $name = $this->getName();
        $presenter = substr($name, strrpos(':' . $name, ':'));
        $id = $this->getParameter(self::URL_PARAM);

        $basicDir = $this->context->parameters['organization']['templatePath'];
        $extendedDir = $this->context->parameters['organization']['eventsPath'];
        return array(
            "$extendedDir/$id/$presenter/$this->view.latte",
            "$extendedDir/$id/$presenter.$this->view.latte",
            "$basicDir/$presenter/$this->view.latte",
            "$basicDir/$presenter.$this->view.latte",
        );
    }

    protected function startup() {
        parent::startup();
        $event = \dibi::select('*')
                        ->from('[:t:org_event]')
                        ->where('%and', array('url' => $this->getParameter(self::URL_PARAM)))
                        ->or('%and', array('url' => $this->getParameter(self::PREVIEW_PARAM)))
                        ->execute()->setRowClass('\Model\Org\Event')->fetch();

        if (!$event) {
            //throw new \Nette\Application\BadRequestException('Neexistující závod.', 404);
            $this->forward(':Public:Homepage:default', $this->getHttpRequest()->getQuery());
            //$this->forward(':Public:Homepage:default', $this->getParameter());
        }
        if (!$this->getUser()->isAllowed($event, 'view')) {
            throw new \Nette\Application\BadRequestException('Nedostatečné oprávnění.', 403);
        }
        // race given by url or the first one
        $race = \dibi::select('*')
                        ->from('[:t:org_race]')
                        ->where('%and', array('url' => $this->getParameter('race'), 'event_id' => $event->id))
                        ->or('%and', array('order' => 1, 'event_id' => $event->id))
                        ->orderBy('[order] DESC')
                        ->execute()->setRowClass('\Model\Org\Race')->fetch();


        $this->event = $this->template->event = $event;
        $this->currentRace = $this->template->currentRace = $race;
        if ($this->hasExtendedLayout()) {
            $this->template->templatePath = $this->context->parameters['organization']['eventsPath'] . DIRECTORY_SEPARATOR . $event->url;
        } else {
            $this->template->templatePath = $this->context->parameters['organization']['templatePath'];
        }

        $this->template->eventPath = $this->context->parameters['organization']['eventsPath'] . '/' . $event->url;
        $this->template->clubFull = $this->context->parameters['club']['fullName'];
        $this->template->clubShort = $this->context->parameters['club']['shortName'];
        $this->template->baseDomainUrl = 'http://www.' . $this->context->parameters['domain'];
    }

    protected function hasExtendedLayout() {
        $extendedDir = $this->context->parameters['organization']['eventsPath'];
        $id = $this->getParameter(self::URL_PARAM);
        return file_exists($extendedDir . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . '@layout.latte');
    }

}