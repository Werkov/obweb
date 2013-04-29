<?php

namespace OOB;

use \Nette\Application\Responses\TextResponse;

class CategoryPriceEdit extends \Nette\Application\UI\Control {
    /**
     * Delimiter of CSV columns.
     */

    const DELIMITER = ';';

    /**
     * @var callback
     */
    private $dataSource;

    /**
     * @var callback
     */
    private $dataHandler;

    /**
     * @var string
     */
    private $filename;

    public function getDataSource() {
        return $this->dataSource;
    }

    public function setDataSource($dataSource) {
        $this->dataSource = $dataSource;
    }

    public function getDataHandler() {
        return $this->dataHandler;
    }

    public function setDataHandler($dataHandler) {
        $this->dataHandler = $dataHandler;
    }

    public function getFilename() {
        return $this->filename;
    }

    public function setFilename($filename) {
        $this->filename = $filename;
    }

    protected function createComponentFrmPricesImport($name) {
        $form = new \Nette\Application\UI\Form($this, $name);

        $form->addUpload('inputFile', 'CSV soubor s cenami')
                ->setOption('description', 'UTF-8, vzor řádku "H21;140".')
                ->addRule(\Nette\Forms\Form::FILLED);


        $form->addSubmit('Import', 'Importovat');

        $that = $this; // phew, PHP...
        $form->onSuccess[] = function($form) use($that) {
                    $values = $form->getValues();
                    $file = $values['inputFile'];
                    $data = $that->parseCSV($file->getTemporaryFile());
                    call_user_func($that->dataHandler, $data);
                };

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        return $form;
    }

    protected function createTemplate($class = null) {
        $template = parent::createTemplate($class)->setFile(__DIR__ . "/template.latte");
        $template->registerHelperLoader('\OOB\Helpers::loader');
        return $template;
    }

    public function render() {
        $this->template->render();
    }

    public function handleExport() {
        $content = $this->createCSV();
        
        $httpResponse = $this->presenter->context->httpResponse;        
        $httpResponse->setContentType('text/plain');
        $httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . $this->getFilename() . '"');
        $httpResponse->setHeader('Content-Length', strlen($content));
        
        $this->presenter->sendResponse(new TextResponse($content));
    }

    /**
     * @internal (for use in closure only)
     */
    public function parseCSV($filename) {
        $result = array();
        foreach (file($filename, FILE_IGNORE_NEW_LINES) as $line) {
            $parts = explode(self::DELIMITER, $line);
            $result[$parts[0]] = $parts[1];
        }
        return $result;
    }
    
    private function createCSV() {
        $data = call_user_func($this->dataSource);
        $content = array();
        foreach($data as $category => $price) {
            $content[] = $category . self::DELIMITER . $price;
        }
        return implode("\r\n", $content);
    }

}