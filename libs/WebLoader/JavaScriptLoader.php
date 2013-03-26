<?php

namespace WebLoader;

use Nette\Utils\Html;
use Nette\ComponentModel\IContainer;

/**
 * JavaScript loader
 *
 * @author Jan Marek
 * @license MIT
 */
class JavaScriptLoader extends WebLoader {

    /**
     * Construct
     * @param IComponentContainer parent
     * @param string name
     */
    public function __construct(IContainer $parent = null, $name = null) {
        parent::__construct($parent, $name);
        $this->setGeneratedFileNamePrefix("jsloader-");
        $this->setGeneratedFileNameSuffix(".js");
    }

    /**
     * Get script element
     * @param string $source
     * @return Html
     */
    public function getElement($source) {
        return Html::el("script")->type("text/javascript")->src($source);
    }

    private $scriptCodeWithKey = array();
    private $scriptCode = null;

    public function addScriptCode($code, $key = null) {
        if ($key === null) {
            $this->scriptCode .= $code;
        } else if (!array_key_exists($key, $this->scriptCodeWithKey)) {
            $this->scriptCodeWithKey[$key] = $code;
        }
    }
    
    public function renderScriptCode() {
        $el = Html::el("script")->type("text/javascript");
        $el->setHtml(join("\n", $this->scriptCodeWithKey) . $this->scriptCode);
        echo $el;
    }

}