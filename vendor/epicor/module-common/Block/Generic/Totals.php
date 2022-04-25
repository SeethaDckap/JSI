<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Generic;


/**
 * Generic totals rows display class
 * 
 * Used with generic grids to add totals rows
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Totals extends \Magento\Framework\View\Element\Template
{

    private $_rows = array();

    private $_sub = array();

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = []
    ) {
        $this->commonHelper = $commonHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Common::epicor_common/totals.phtml');
        $this->setColumns(1);
    }

    /**
     * Adds a row to the totals display
     * 
     * @param string $label
     * @param string $value
     * @param string $labelClass
     * @param string $valueClass
     */
    protected function addRow($label, $value, $labelClass = '', $valueClass = '', $rawValue = 0, $origValue = 0, $expand = false, $expandDef = false)
    {
        $this->_rows[] = array(
            'label' => $label,
            'value' => $value,
            'label_class' => $labelClass,
            'value_class' => $valueClass,
            'raw-value' => number_format($rawValue, 2),
            'orig-value' => $origValue,
            'expand' => $expand,
            'expandDef' => $expandDef
        );
    }

    /**
     * Adds a row to the totals display
     *
     * @param string $label
     * @param string $value
     * @param string $labelClass
     * @param string $valueClass
     */
    protected function addSubRow($labelClass, $valueArr)
    {
        $this->_sub[$labelClass] = array(
            'value' => $valueArr
        );

    }

    /**
     * gets the row array
     * 
     * @return array
     */
    public function getRows()
    {
        return $this->_rows;
    }

    /**
     * gets the row array
     *
     * @return array
     */
    public function getSubRows()
    {
        return $this->_sub;
    }

    /**
     * Gets the helper
     * 
     * @return \Epicor\Common\Helper\Data
     */
    public function getHelper($type = null)
    {
        //M1 > M2 Translation Begin (Rule p2-7)
        //return Mage::helper('epicor_common');
        return $this->commonHelper;
        //M1 > M2 Translation End
    }
}
