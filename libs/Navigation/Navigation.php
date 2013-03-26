<?php

namespace Navigation;

/**
 * Description of Navigation
 *
 * @author Michal
 */
class Navigation extends \Nette\Application\UI\Control {
    const PINFO_TEXT = "text";
    const PINFO_TITLE = "title";
    const PINFO_THIS = "this";
    const PINFO_PARAMS = "params";

    /**
     * Translates action name into node.
     * @var array of BasicNode
     */
    public $nodes = array();

    /**
     * Root of the navigation
     * @var BasicNode
     */
    private $root;

    /**
     * Data for nodes when drawing path. Parameters for given node.
     * @var array of array of mixed
     */
    public $nodeData = array();

    // <editor-fold desc="Menu manipulation">

    /**
     *
     * @param BasicNode $node
     * @return BasicNode
     */
    public function addChild(BasicNode $node) {
        $this->root->addChild($node);

        if ($node->getAction() != "" && $node instanceof StaticNode && $node->getHierarchyDelimiter())
            $this->nodes[$node->getAction()] = $node;
        return $node;
    }

    public function getRoot() {
        return $this->root;
    }

    public function setupRoot($text, $action) {
        $this->root = new StaticNode($text, $action, true);
        $this->root->setNavigation($this);
        $this->root->setParent(null);
        $this->nodes[$action] = $this->root;
    }

    // </editor-fold>
    // <editor-fold desc="Rendering">
    public function renderTitle($delimiter = " | ") {
        //where are we?
        $action = $this->getPresenter()->getAction(true);
        $parameters = $this->getPresenter()->getParameter();
        echo $this->getTitle($action, $parameters, $delimiter);
    }

    public function renderPath($delimiter = " » ") {
        $template = $this->createTemplate()
                ->setFile(dirname(__FILE__) . "/path.phtml");

        //where are we?
        $action = $this->getPresenter()->getAction(true);

        //Init parameters for current action
        $this->nodeData[$action] = $this->getPresenter()->getParam();

        if (isset($this->nodeData[$action]["action"]))
            unset($this->nodeData[$action]["action"]);

        $path = array();

        if (!\array_key_exists($action, $this->nodes))
            throw new \Nette\InvalidStateException("Action '$action' not found in navigation.");

        $currentNode = $this->nodes[$action];

        //traverse to the root
        $leaf = true;
        while ($currentNode !== null) {
            $path = array_merge($path, $currentNode->getPathNodes($leaf));
            $leaf = false;
            $currentNode = $currentNode->getParent();
        }

        //TODO: provide reversed path?
        $template->path = \array_reverse($path);
        $template->delimiter = $delimiter;
        $template->render();
    }

    /**
     * Temporary parameters for actions when rendering menu
     * @var array of array of mixed
     */
    public $nodeDataMenu = array();

    public function renderMenu($maxDepth = 0) {
        if (!($this->root instanceof StaticNode)) {
            throw new \Nette\InvalidStateException("Root node must be StaticNode.");
        }


        $cache = \Nette\Environment::getCache("Navigation");

        $key = md5($this->root->getAction() . count($this->nodes));
        if (isset($cache[$key])) {
            $menuNode = $cache[$key];
        } else {
            $this->nodeDataMenu = array();
            $menuNode = $this->root->getMenuNode();

            $this->expandInto($this->root, $menuNode, 1, $maxDepth);

            $cache[$key] = $menuNode;
        }


        $template = $this->createTemplate()
                ->setFile(dirname(__FILE__) . "/menu.phtml");

        $template->root = $menuNode;
        $template->render();
    }

    public function renderUncles($level = 1) {
        $template = $this->createTemplate()
                ->setFile(dirname(__FILE__) . "/uncles.phtml");

        //where are we?
        $action = $this->getPresenter()->getAction(true);


        if (!\array_key_exists($action, $this->nodes))
            throw new \Nette\InvalidStateException("Action '$action' not found in navigation.");

        $currentNode = $this->nodes[$action];

        //traverse to level-th 'father'
        $leaf = true;
        while ($level > 0 && $currentNode !== null) {
            $currentNode = $currentNode->getParent();
            if ($currentNode->getHierarchyDelimiter())
                $level--;
        }

        if ($currentNode === null)
            return;

        //TODO: Support for dynamic/cyclic nodes
        $children = array();

        foreach ($currentNode->getChildren() as $childNode) {
            if (!$childNode->getMenu())
                continue;
            if ($childNode instanceof CyclicNode || $childNode instanceof DynamicNode) {
                $children = array_merge($children, $childNode->expand());
            } else {
                $children[] = $childNode->getMenuNode();
            }
        }

        $template->uncles = $children;
        $template->render();
    }

    public function getTitle($action, $parameters, $delimiter ='|') {
        //Init parameters for current action
        $this->nodeData[$action] = $parameters;

        if (isset($this->nodeData[$action]["action"]))
            unset($this->nodeData[$action]["action"]);



        if (!\array_key_exists($action, $this->nodes))
            throw new \Nette\InvalidStateException("Action '$action' not found in navigation.");

        if ($this->nodes[$action] == $this->root)
            return;

        $result = '';
        $first = true;
        foreach ($this->nodes[$action]->getPathNodes(true) as $node) {
            if (!$first)
                $result .= $delimiter;
            $result .= $node->text;
            $first = false;
        }
        return $result;
    }

// </editor-fold>
    // <editor-fold desc="Tree conversion">

    /**
     * Fills (recursively) child elements of @menuNode with child elements of @node and expands all expandanle nodes.
     * @param BasicNode $node	Node whose children should be expanded into menu node.
     * @param MenuNode $menuNode Target node.
     * @param int $depth Current depth in recursion.
     * @param int $maxDepth Maximal depth of recursion, 0 means unlimited.
     * @return void
     */
    protected function expandInto(BasicNode $node, MenuNode $menuNode, $depth = 0, $maxDepth = 0) {
        if ($maxDepth > 0 && $depth >= $maxDepth)
            return;



        if ($node instanceof CyclicNode) {
            foreach ($node->expand() as $childMenuNode) { //expansion is determined by the given parameters
                $menuNode->addChild($childMenuNode);

                $backup = null;
                if (isset($this->nodeDataMenu[$node->getAction()])) {
                    $backup = $this->nodeDataMenu[$node->getAction()];
                }
                $this->nodeDataMenu[$node->getAction()] = $childMenuNode->getParameters();
                $this->expandInto($node, $childMenuNode, $depth + 1, $maxDepth);
                $this->nodeDataMenu[$node->getAction()] = $backup;
            }
        }

        foreach ($node->getChildren() as $childNode) {
            if (!$childNode->getMenu())
                continue;

            if ($childNode instanceof StaticNode) {
                $childMenuNode = $childNode->getMenuNode();
                $menuNode->addChild($childMenuNode);
                $this->expandInto($childNode, $childMenuNode, $depth + 1, $maxDepth);
            } elseif ($childNode instanceof DynamicNode) {
                foreach ($childNode->expand() as $childMenuNode) { //expansion is determined by the given parameters
                    $menuNode->addChild($childMenuNode);

                    $this->nodeDataMenu[$childNode->getAction()] = $childMenuNode->getParameters();
                    $this->expandInto($childNode, $childMenuNode, $depth + 1, $maxDepth);
                    unset($this->nodeDataMenu[$childNode->getAction()]);
                }
            } elseif ($childNode instanceof CyclicNode) {
                $this->expandInto($childNode, $menuNode, $depth + 1, $maxDepth);
            }
        }
    }

// </editor-fold>
}

