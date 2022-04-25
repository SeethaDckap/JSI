<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method int getColumnCount()
 * @method void setColumnCount(int $count)
 * @method bool getOnRight()
 * @method void setOnRight(bool $bool)
 * @method bool getOnLeft()
 * @method void setOnLeft(bool $bool)
 */
class Info extends \Magento\Framework\View\Element\Template
{

    /**
     *  @var \Magento\Framework\DataObject 
     */
    protected $_infoData = array();
    protected $_extraData = array();

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('epicor_comm/customer/account/info.phtml');
        $this->setColumnCount(3);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getInfoData()
    {
        return $this->_infoData;
    }

    public function getExtraData()
    {
        return $this->_extraData;
    }
}
