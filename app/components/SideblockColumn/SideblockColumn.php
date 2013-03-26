<?php

namespace OOB;

use Model\System\User;

class SideblockColumn extends \Nette\Application\UI\Control {

    protected function createTemplate($class = null) {
        $template = parent::createTemplate($class)->setFile(__DIR__ . "/column.latte");

        return $template;
    }

    public function render($position) {
        foreach ($this->getSideblocks($position) as $sideblock) {
            $template = new \Nette\Templating\Template();
            $template->registerFilter(new \Nette\Latte\Engine);
            $template->registerHelperLoader('\OOB\Helpers::loader');
            $template->control = $template->_control = $this->getPresenter(); // included template is like child of presenter
            $template->presenter = $template->_presenter = $this->getPresenter();

            $template->setSource($sideblock->content);
            $sideblock->content = $template->__toString();
        }
        $this->template->sideblocks = $this->getSideblocks($position);
        $this->template->render();
    }

    private $sideblocks = array();

    private function getSideblocks($position) {
        if (!$this->sideblocks) {
            $this->sideblocks = \dibi::select('*')
                    ->from('[:t:con_sideblock]')
                    ->orderBy('position, [order]')
                    ->fetchAssoc('position,order');
        }
        if (array_key_exists($position, $this->sideblocks)) {
            return $this->sideblocks[$position];
        } else {
            return array();
        }
    }

}