<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Xmlvarien
 *
 * @author David.Wylie
 */
class Xmlvarien extends \Magento\Framework\DataObject
{

    private $_rawTags = array('legacy_header');

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $data
        );
    }


    /**
     * Return the data form the varien object for the given config path.
     * @param string $configEntry
     * @param varien_object
     * @return varien_object
     */
    public function getVarienData($configEntry)
    {
        $path = $this->scopeConfig->getValue($configEntry, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->getVarienDataFromPath($path);
    }

    public function hasVarienDataFromPath($path, $data = null)
    {
        return $this->_getVarienDataFromPath($path, $data, true);
    }

    public function getVarienDataFromPath($path, $data = null)
    {
        return $this->_getVarienDataFromPath($path, $data);
    }

    public function getVarienDataArrayFromPath($path, $data = null)
    {
        $result = $this->_getVarienDataFromPath($path, $data);
        if (is_array($result)) {
            $resultArr = $result;
        } else {
            if ((is_string($result) && $result == '') || $result == null) {
                $resultArr = array();
            } else {
                $resultArr = array($result);
            }
        }
        return $resultArr;
    }

    /**
     * Return the data form the varien object for the given config path.
     * @param string $path
     * @param varien_object
     * @return varien_object
     */
    private function _getVarienDataFromPath($path, $data = null, $has = false)
    {
        if ($data == null) {
            $data = &$this;
        }

        $result = null;
        $exists = null;
        if (!empty($path)) {
            $paths = explode("/", $path);
            $result = $data;
            foreach ($paths as $path) {
                $exists = ($result) ? $result->hasData($path) : false;
                $result = ($result) ? $result->getData($path) : false;

                $continue = ($has) ? $exists : $result;

                if (!$continue) {
                    break;
                }
            }
        }

        return ($has) ? $exists : $result;
    }

    public function getVarienDataFlag($configEntry)
    {
        $resultData = $this->getVarienData($configEntry);
        if ($resultData == 'Y' || $resultData == 1) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    public function getVarienDataFlagWithDefaultConfig($configEntry, $defaultConfig)
    {
        $resultData = $this->getVarienDataWithDefaultConfig($configEntry, $defaultConfig);
        if ($resultData == 'Y' || $resultData == 1) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    public function getVarienDataWithDefaultConfig($configEntry, $defaultConfig)
    {
        $resultData = $this->getVarienData($configEntry);
        if (!empty($resultData) || $resultData == '0') {
            $result = $resultData;
        } else {
            $result = $this->scopeConfig->getValue($defaultConfig, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return $result;
    }

    public function getVarienDataArray($configEntry)
    {
        $result = $this->getVarienData($configEntry);
        if (is_array($result)) {
            $resultArr = $result;
        } else {
            if ((is_string($result) && $result == '') || $result == null) {
                $resultArr = array();
            } else {
                $resultArr = array($result);
            }
        }
        return $resultArr;
    }

    /**
     * Set a field in the varent object based on the given config entry.
     * @param type $configEntry
     * @param type $erpData
     * @param type $newValue
     */
    public function setVarienData($configEntry, $newValue)
    {
        $path = $this->scopeConfig->getValue($configEntry, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->setVarienDataFromPath($path, $newValue);
    }

    public function setVarienDataFromPath($path, $newValue)
    {
        $pathParts = explode('/', $path);
        $section = &$this;
        $lastKey = array_pop($pathParts);
        foreach ($pathParts as $key) {
            if (!$section->getData($key)) {
                $section->setData($key, $this->dataObjectFactory->create());
            }
            $section = $section->getData($key);
        }
        $section->setData($lastKey, $newValue);
    }

    public function setData($key, $value = null)
    {
        if (!in_array($key, $this->_rawTags) && is_string($value))
            $value = trim($value);

        return parent::setData($key, $value);
    }

    public function __call($method, $args)
    {
        if (substr($method, 0, 10) == 'getasarray') {
            //Varien_Profiler::start('GETTER: '.get_class($this).'::'.$method);
            $key = $this->_underscore(substr($method, 10));
            $data = $this->getData($key, isset($args[0]) ? $args[0] : null);
            //Varien_Profiler::stop('GETTER: '.get_class($this).'::'.$method);
            if (!is_array($data))
                $data = array($data);
            return $data;
        } else
            return parent::__call($method, $args);
    }

    public function __toString()
    {
        return (string) ($this->getValue() ?: '');
    }

}
