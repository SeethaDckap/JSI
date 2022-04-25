<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method void setOnRight(bool $bool)
 * @method bool getOnRight()
 */
class Editableaddress extends \Epicor\Customerconnect\Block\Customer\Address
{

    public function _construct()
    {
        parent::_construct();
        $this->_addressData = array();
        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/address/edit.phtml');
        $this->setOnRight(false);
        $this->setHideWrapper(true);
    }

    public function isEditable()
    {
        return true;
    }

}
