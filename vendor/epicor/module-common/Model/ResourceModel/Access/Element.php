<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\ResourceModel\Access;


/**
 * 
 * Access element resource model
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Element extends \Epicor\Database\Model\ResourceModel\Access\Element
{

    private $_excludedModules = array(
        'Mage_Install'
    );

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Element\CollectionFactory
     */
    protected $commonResourceAccessElementCollectionFactory;

    /**
     * @var \Epicor\Common\Model\Access\ElementFactory
     */
    protected $commonAccessElementFactory;

    /**
     * @var \Epicor\Comm\Model\GlobalConfig\Config
     */
    protected $globalConfig;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Epicor\Common\Model\ResourceModel\Access\Element\CollectionFactory $commonResourceAccessElementCollectionFactory,
        \Epicor\Common\Model\Access\ElementFactory $commonAccessElementFactory,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $connectionName = null
    ) {
        $this->commonResourceAccessElementCollectionFactory = $commonResourceAccessElementCollectionFactory;
        $this->commonAccessElementFactory = $commonAccessElementFactory;
        $this->moduleReader=$moduleReader;
        $this->globalConfig = $globalConfig;
        parent::__construct(
            $context,
            $connectionName
        );
    }



    public function regenerate($module = null)
    {
        set_time_limit(0);
        if (empty($module)) {
            //M1 > M2 Translation Begin (Rule P2-5.6)
            //$modules = array_keys((array) Mage::getConfig()->getNode('modules')->children());
            $modules = array_keys((array) $this->_scopeConfig->getValue('modules')->children());
            //M1 > M2 Translation End
            foreach ($modules as $module) {
                $this->addElement($module, '*', '*', '', 'Access');
                //M1 > M2 Translation Begin (Rule P2-5.7)
                //$controllerDir = Mage::getModuleDir('controllers', $module);
                $controllerDir = $this->moduleReader->getModuleDir('controllers', $module);

                //M1 > M2 Translation End

                $this->getControllers($controllerDir, $module);
            }

            $this->deleteOldElements();
        } else {
            //M1 > M2 Translation Begin (Rule P2-5.7)
            //$controllerDir = Mage::getModuleDir('controllers', $module);
            $controllerDir = $this->moduleReader->getModuleDir('controllers', $module);

            //M1 > M2 Translation End

            $this->addElement($module, '*', '*', '', 'Access');
            $this->getControllers($controllerDir, $module);
            //$this->deleteOldElements($module);
        }
    }

    private function getControllers($controllerDir, $module)
    {

        if (stripos($controllerDir, 'Adminhtml') !== false || in_array($module, $this->_excludedModules)) {
            return;
        }

        $controllers = glob($controllerDir . DIRECTORY_SEPARATOR . "*");

        foreach ($controllers as $controller) {
            if (is_dir($controller)) {
                if (stripos($controller, 'Adminhtml') === false && !in_array($module, $this->_excludedModules)) {
                    $this->getControllers($controller, $module);
                }
            } else {
                //M1 > M2 Translation Begin (Rule P2-5.7)
                //$search = array(Mage::getModuleDir('controllers', $module) . DS, '.php', 'Controller', DS);
                $search = array($this->moduleReader->getModuleDir('controllers', $module) . DIRECTORY_SEPARATOR, '.php', 'Controller', DIRECTORY_SEPARATOR);

                //M1 > M2 Translation End

                $replace = array('', '', '', '_');

                $controllerName = ucfirst(strtolower(str_replace($search, $replace, $controller)));

                $actions = $this->_getActions($controller);
                $this->addElement($module, $controllerName, '*', '', 'Access');
                if (!empty($actions)) {

                    foreach ($actions as $action) {
                        $this->addElement($module, $controllerName, $action, '', 'Access');
                        //M1 > M2 Translation Begin (Rule 4)
                        //$actionBlocks = Mage::getConfig()->getNode('global/access/' . $module . '/' . $controllerName . '/' . $action);
                        $actionBlocks = $this->globalConfig->get('access/' . $module . '/' . $controllerName . '/' . $action);
                        //M1 > M2 Translation End

                        if (!empty($actionBlocks)) {
                            foreach ($actionBlocks->children() as $actionBlock) {
                                /* @var $block Mage_Core_Model_Config_Element */
                                $blockName = $actionBlock->getName();
                                foreach ($actionBlock->children() as $type) {
                                    $this->addElement($module, $controllerName, $action, $blockName, $type->getName());
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function _getActions($controller, $actions = array())
    {

        if (file_exists($controller)) {
            $controllerFile = file_get_contents($controller);
            // get actions from controller file
            preg_match_all('/function ([A-Za-z].*)Action/', $controllerFile, $controllerActions);

            if (isset($controllerActions[1]) && !empty($controllerActions[1])) {
                $actions = array_unique(array_merge($actions, $controllerActions[1]));
            }

            // check if the parent needs to be checked too

            preg_match_all('/extends[\s].*?([^\s].*)[\s].*?\{/', $controllerFile, $extends);

            unset($controllerFile);

            if (isset($extends[1]) && !empty($extends[1])) {
                $parent = trim(array_pop($extends[1]));
                if ($parent != 'Mage_Core_Controller_Front_Action') {
                    $route = explode('_', $parent);

                    $final = array_pop($route);

                    $module = $route[0] . '_' . $route[1];

                    unset($route[0]);
                    unset($route[1]);

                    $parts = implode(DIRECTORY_SEPARATOR, $route);

                    if (strpos($final, 'Controller')) {
                        //M1 > M2 Translation Begin (Rule P2-5.7)
                        //$base = Mage::getModuleDir('controllers', $module) . DS . $parts;
                        $base = $this->moduleReader->getModuleDir('controllers', $module) . DIRECTORY_SEPARATOR . $parts;
                        //M1 > M2 Translation End

                    } else {
                        //M1 > M2 Translation Begin (Rule P2-5.7)
                        // $base = Mage::getModuleDir('base', $module) . DS . $parts;
                        $base = $this->moduleReader->getModuleDir('base', $module) . DIRECTORY_SEPARATOR . $parts;
                        //M1 > M2 Translation End

                    }

                    $parentFileName = $base . DIRECTORY_SEPARATOR . $final . '.php';
                    $parentActions = $this->_getActions($parentFileName, $actions);
                    $actions = array_unique(array_merge($actions, $parentActions));
                }
            }
        } else {
            echo '<strong>file does not exist: ', $controller, '</strong><br />';
        }

        return $actions;
    }

    public function loadByAll($module, $controller, $action, $block, $action_type)
    {

        $collection = $this->commonResourceAccessElementCollectionFactory->create()->addFieldToFilter('module', $module)
            ->addFieldToFilter('controller', $controller)
            ->addFieldToFilter('action', $action)
            ->addFieldToFilter('block', $block)
            ->addFieldToFilter('action_type', $action_type);
        /* @var $collection Epicor_Common_Model_Resource_Access_Element_Collection */
        return $collection->getFirstItem();
    }

    private function addElement($module, $controller, $action, $block, $action_type)
    {

        $model = $this->loadByAll($module, $controller, $action, $block, $action_type);

        if (!$model || !$model->getId() || $model->isObjectNew()) {
            $model = $this->commonAccessElementFactory->create();
            /* @var $model Epicor_Common_Model_Access_Element */
            $model->setModule($module);
            $model->setController($controller);
            $model->setAction($action);
            $model->setBlock($block);
            $model->setActionType($action_type);
            $model->save();
        }

        $this->_updatedIds[] = $model->getId();
    }

    private function deleteOldElements($module = '')
    {
        $collection = $this->commonResourceAccessElementCollectionFactory->create()->addFieldToFilter('id', array('nin' => $this->_updatedIds));
        /* @var $collection Epicor_Common_Model_Resource_Access_Element_Collection */

        if (!empty($module)) {
            $collection->addFieldToFilter('module', $module);
        }

        if ($collection->count() > 0) {
            foreach ($collection->getItems() as $item) {
                $item->delete();
            }
        }
    }

}
