<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns;


/**
 * Returns creation page, Return block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class ReturnBlock extends \Epicor\Comm\Block\Customer\Returns\AbstractBlock
{

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        $this->commReturnsHelper = $commReturnsHelper;
        parent::__construct($context, $commReturnsHelper, $registry, $data);
    }
    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Return'));
        $this->setTemplate('epicor_comm/customer/returns/return.phtml');
    }

    public function getGuestEmail()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */
        if ($this->registry->registry('guest_email')) {
            $email = $helper->encodeReturn($this->registry->registry('guest_email'));
        } else {
            $email = '';
        }

        return $email;
    }

    public function getGuestName()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */
        if ($this->registry->registry('guest_name')) {
            $name = $helper->encodeReturn($this->registry->registry('guest_name'));
        } else {
            $name = '';
        }

        return $name;
    }

    public function getFindByOptions()
    {
        $options = array();

        $return = $this->configHasValue('find_by', 'return_number');
        $case = $this->configHasValue('find_by', 'case_number');
        $ref = $this->configHasValue('find_by', 'customer_ref');

        if ($return) {
            $options[] = array(
                'value' => 'return',
                'label' => __('Return Number'),
            );
        }

        if ($case) {
            $options[] = array(
                'value' => 'case_no',
                'label' => __('Case Management Number'),
            );
        }

        if ($ref) {
            $options[] = array(
                'value' => 'customer_ref',
                'label' => __('Customer Reference'),
            );
        }

        return $options;
    }

}
