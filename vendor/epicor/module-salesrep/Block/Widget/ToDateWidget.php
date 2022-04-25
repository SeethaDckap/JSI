<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\SalesRep\Block\Widget;

/**
 * Class Dob
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class ToDateWidget extends \Magento\Customer\Block\Widget\Dob {

    /**
     * @return void
     */
    public function _construct() {
        parent::_construct();
        $this->setTemplate('Epicor_SalesRep::epicor/salesrep/widget/dob.phtml');
    }
    
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return false;
       // $attributeMetadata = $this->_getAttribute('dob');
       // return $attributeMetadata ? (bool)$attributeMetadata->isRequired() : false;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->setTime($date ? strtotime($date) : false);
        $this->setValue($this->applyOutputFilter($date));
        return $this;
    }

    /**
     * @return string|bool
     */
    public function getDay()
    {
        return $this->getTime() ? date('d', $this->getTime()) : '';
    }

    /**
     * @return string|bool
     */
    public function getMonth()
    {
        return $this->getTime() ? date('m', $this->getTime()) : '';
    }

    /**
     * @return string|bool
     */
    public function getYear()
    {
        return $this->getTime() ? date('Y', $this->getTime()) : '';
    }
    
    /**
     * Return label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
         return __('To Date');
    }
    /**
     * Create correct date field
     *
     * @return string
     */
    public function getFieldHtml()
    {
        $this->dateElement->setData([
            'extra_params' => $this->isRequired() ? 'data-validate="{required:true}"' : '',
            'name' => $this->getHtmlId(),
            'id' => $this->getHtmlId(),
            'class' => $this->getHtmlClass(),
            'value' => $this->getValue(),
            'date_format' => $this->getDateFormat(),
            'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
           // 'years_range' => '-120y:c+nn',
            //'max_date' => '-1d',
            'change_month' => 'true',
            'change_year' => 'true',
            'show_on' => 'both'
        ]);
        return $this->dateElement->getHtml();
    }

    /**
     * Return id
     *
     * @return string
     */
    public function getHtmlId()
    {
        return 'to_date';
    }

    /**
     * Returns format which will be applied for DOB in javascript
     *
     * @return string
     */
    public function getDateFormat()
    {
       return 'yyyy-MM-dd'; //$this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
    }

             
}
