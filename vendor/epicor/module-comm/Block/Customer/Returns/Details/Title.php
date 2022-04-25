<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Details;


/**
 * Return Details page title
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Title extends \Epicor\Customerconnect\Block\Customer\Returns\Details\Title
{

    const FRONTEND_RESOURCE_EDIT = 'Epicor_Customer::my_account_returns_edit';

    /**
     * Returns whether an entity can be deleted or not
     *
     * @return boolean
     */
    public function getEditUrl()
    {
        // edit link may need to be different if it exists in our system or not
        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        if (!$return->isObjectNew()) {
            $params = array(
                'return' => $helper->encodeReturn($return->getId())
            );
            $url = $this->getUrl('epicor_comm/returns/customerindex', $params);
        } else {
            $params = array(
                'erpreturn' => $helper->encodeReturn($return->getErpReturnsNumber())
            );
            $url = $this->getUrl('epicor_comm/returns/customerindex', $params);
        }

        return $url;
    }
}
