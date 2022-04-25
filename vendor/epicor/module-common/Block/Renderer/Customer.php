<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Renderer;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Type
 *
 * @author Paul.Ketelle
 */
class Customer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    const DISPLAY_NAME_ONLY = 1;
    const DISPLAY_EMAIL_ONLY = 2;
    const DISPLAY_CUSTOMER_CODE_ONLY = 3;
    const DISPLAY_NAME_AND_EMAIL = 4;
    const DISPLAY_NAME_AND_CUSTOMER_CODE = 5;
    const DISPLAY_EMAIL_AND_CUSTOMER_CODE = 6;
    const DISPLAY_ALL = 7;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        array $data = []
    ) {
        $this->customerCustomerFactory = $customerCustomerFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $customer_id = $row->getData($this->getColumn()->getIndex());
        $display_info_flag = $this->getColumn()->getDisplayInfo();

        $customer = $this->customerCustomerFactory->create()->load($customer_id);
        /* @var $customer Mage_Customer_Model_Customer */

        $customer_info = '';
        if (in_array($display_info_flag, array(
                self::DISPLAY_NAME_ONLY,
                self::DISPLAY_NAME_AND_EMAIL,
                self::DISPLAY_NAME_AND_CUSTOMER_CODE,
                self::DISPLAY_ALL
                )
            )
        ) {
            $customer_info .= $customer->getName();
        }
        if (in_array($display_info_flag, array(
                self::DISPLAY_EMAIL_ONLY,
                self::DISPLAY_NAME_AND_EMAIL,
                self::DISPLAY_EMAIL_AND_CUSTOMER_CODE,
                self::DISPLAY_ALL
                )
            )
        ) {
            if ($customer_info != '')
                $customer_info .= ' (' . $customer->getEmail() . ')';
            else
                $customer_info .= $customer->getEmail();
        }

        if (in_array($display_info_flag, array(
                self::DISPLAY_CUSTOMER_CODE_ONLY,
                self::DISPLAY_NAME_AND_CUSTOMER_CODE,
                self::DISPLAY_EMAIL_AND_CUSTOMER_CODE,
                self::DISPLAY_ALL
                )
            )
        ) {
            $customer_code = $this->helper('epicor_comm')->getErpAccountNumber($customer->getEccErpaccountId());
            /* @var $customer_group Mage_Customer_Model_Group */
            if ($customer_info != '')
                $customer_info .= ' [' . $customer_code . ']';
            else
                $customer_info .= $customer_code;
        }


        return $customer_info;
    }

}
