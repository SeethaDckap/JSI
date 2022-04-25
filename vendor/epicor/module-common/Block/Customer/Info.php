<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Customer;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method int getColumnCount()
 * @method void setColumnCount(int $count)
 * @method bool getOnRight()
 * @method void setOnRight(bool $bool)
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_infoData = array();
    protected $_extraData = array();

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $helper;

    protected $_dateFormat = \IntlDateFormatter::MEDIUM;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $helper,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
    }


    /**
     * @return \Magento\Framework\DataObject
     */
    public function getInfoData()
    {
        return $this->_infoData;
    }

    //M1 > M2 Translation End


    public function getExtraData()
    {
        return $this->_extraData;
    }

    public function check_your_datetime($myDateString)
    {
        $valid = false;
        if (substr_count($myDateString, '-') > 1) {
            $valid = true;
        }
        return (bool)strtotime($myDateString) && $valid;
    }

    public function decamelize($string)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }

    public function renderDate($date)
    {
        $data = __('N/A');
        if (!empty($date)) {
            try {
                $data = $this->helper->getLocalDate($date, $this->_dateFormat);
            } catch (\Exception $ex) {
                $data = $date;
            }
        }

        return $data;
    }

    protected function isChildConfigValuesSet($index): bool
    {
        return strpos($index, '>') !== false;
    }


    /**
     * @param $index
     * @param $dataObject \Epicor\Common\Model\Xmlvarien
     * @return bool|\Magento\Framework\Phrase|string
     */
    protected function getChildConfigValues($index, $dataObject)
    {
        if (!$dataLevels = $this->getDataLevels($index)) {
            return false;
        }
        $levels = $this->setDataLevelsArray($dataLevels);

        $value = $this->getSubNodeValue($dataObject, $levels);

        if ($this->check_your_datetime($value)) {
            $value = $this->renderDate($value);
        }

        return $value;
    }

    protected function setDataLevelsArray($dataLevels): array
    {
        $levelsArray = [];
        if (is_array($dataLevels)) {
            foreach ($dataLevels as $level) {
                $levelsArray[] = $this->cleanDataString($level);
            }
        }

        return $levelsArray;
    }

    protected function getDataLevels(string $stringLevels)
    {
        return explode(">", $stringLevels);
    }

    protected function cleanDataString($dataString)
    {
        return trim($this->decamelize($dataString));
    }

    public function getResultValue($dataValue)
    {
        return is_array($dataValue) && isset($dataValue[0]) ? $dataValue[0] : $dataValue;
    }

    protected function getSubNodeValue($dataObject, $nodeLevels)
    {
        if (count($nodeLevels) < 2 || !$dataObject instanceof \Epicor\Common\Model\Xmlvarien) {
            return '';
        }
        if ($this->isNodeLevelSet(2, $nodeLevels)) {
            if ($nodeData = $dataObject->getData($nodeLevels[0])) {
                return $nodeData[$nodeLevels[1]];
            }
        }
        if ($this->isNodeLevelSet(3, $nodeLevels)) {
            if ($nodeData = $dataObject->getData($nodeLevels[0])[$nodeLevels[1]]) {
                return $nodeData[$nodeLevels[2]];
            }
        }
        if ($this->isNodeLevelSet(4, $nodeLevels)) {
            if ($nodeData = $dataObject->getData($nodeLevels[0])[$nodeLevels[1]][$nodeLevels[2]]) {
                return $nodeData[$nodeLevels[3]];
            }
        }
    }

    protected function isNodeLevelSet($levelCount, $nodeLevels)
    {
        $isSet = false;
        if (!is_array($nodeLevels)) {
            return $isSet;
        }

        if (count($nodeLevels) === $levelCount) {
            $isSet = true;
            for ($i = 0; $i < $levelCount; $i++) {
                if (!isset($nodeLevels[$i])) {
                    $isSet = false;
                }
            }
        }

        return $isSet;
    }


    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
