<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Returns\Details;


/**
 * Return Details page title
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Title extends \Epicor\Customerconnect\Block\Customer\Title
{

    const FRONTEND_RESOURCE_EDIT = 'Epicor_Customerconnect::customerconnect_account_returns_edit';

    protected $_rereturnType = 'Returns';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data)
    {
        $this->registry = $registry;
        parent::__construct($context, $commonAccessHelper, $commReturnsHelper, $customerconnectHelper, $data);
    }


    public function _construct()
    {
        parent::_construct();
        $this->_setTitle();
    }

    /**
     * Sets the page title
     */
    protected function _setTitle()
    {
        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
        if ($return) {
            $this->_title = __('Return : %1', $return->getCustomerReference() ?: $return->getErpReturnsNumber());
        }
    }

    /**
     * Returns whether an entity can be reordered or not
     * 
     * @return boolean
     */
    public function canReorder()
    {
        return false;
    }

    /**
     * Returns whether an entity can be returned or not
     * 
     * @return boolean
     */
    public function canReturn()
    {
        return false;
    }

    /**
     * Returns whether an entity can be edited or not
     * 
     * @return boolean
     */
    public function canEdit()
    {
        $actions = [];
        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
        if ($return && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT)) {
            $actions = $return->getActions();
        }
        return !empty($actions);
    }

    /**
     * Returns whether an entity can be deleted or not
     * 
     * @return boolean
     */
    public function canDelete()
    {
        // use actions here to determine return can be deleted
        return true;
    }

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
            $url = $this->getUrl('epicor_comm/returns/index', $params);
        } else {
            $params = array(
                'erpreturn' => $helper->encodeReturn($return->getErpReturnsNumber())
            );
            $url = $this->getUrl('epicor_comm/returns/index', $params);
        }

        return $url;
    }

    /**
     * Returns whether an entity can be deleted or not
     * 
     * @return boolean
     */
    public function getDeleteUrl()
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
            $url = $this->getUrl('epicor_comm/returns/delete', $params);
        } else {
            $params = array(
                'erpreturn' => $helper->encodeReturn($return->getErpReturnsNumber())
            );
            $url = $this->getUrl('epicor_comm/returns/delete', $params);
        }

        return $url;
    }

}
