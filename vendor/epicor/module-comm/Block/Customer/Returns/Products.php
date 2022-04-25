<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns;


/**
 * Returns creation page, Products block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Products extends \Epicor\Comm\Block\Customer\Returns\AbstractBlock
{

    private $_findByOptions;
    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Epicor\Common\Model\Url
     */
    protected $url;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Common\Model\Url $url,
        array $data = [])
    {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->url = $url;
        parent::__construct(
            $context,
            $commReturnsHelper,
            $registry,
            $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Products'));
        $this->setTemplate('epicor_comm/customer/returns/products.phtml');
    }

    public function getLinesHtml()
    {
        return $this->layout->createBlock('\Epicor\Comm\Block\Customer\Returns\Lines')->toHtml();
    }

    public function getFindLinesByOptions()
    {
        if ($this->_findByOptions === null) {
            $this->_findByOptions = array();

            $allowed = array();

            if (!$this->checkConfigFlag('allow_mixed_return')) {
                foreach ($this->getReturnLines() as $line) {
                    /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */
                    $allowed[] = $line->getSourceType();
                }
            }

            if (empty($allowed)) {
                $allowed = array('order', 'invoice', 'shipment', 'serial');
            }

            $order = $this->configHasValue('find_lines_by', 'order_number');
            $invoice = $this->configHasValue('find_lines_by', 'invoice_number');
            $shipment = $this->configHasValue('find_lines_by', 'shipment_number');
            $serial = $this->configHasValue('find_lines_by', 'serial_number');

            if ($order && in_array('order', $allowed)) {
                $this->_findByOptions[] = array(
                    'value' => 'order',
                    'label' => __('Order Number'),
                );
            }

            $helper = $this->commMessagingHelper;
            /* @var $helper Epicor_Comm_Helper_Messaging */

            if ($invoice && $helper->isMessageEnabled('customerconnect', 'cuid') && in_array('invoice', $allowed)) {
                $this->_findByOptions[] = array(
                    'value' => 'invoice',
                    'label' => __('Invoice Number'),
                );
            }

            if ($shipment && $helper->isMessageEnabled('customerconnect', 'cuss') && $helper->isMessageEnabled('customerconnect', 'CUSD') && in_array('shipment', $allowed)) {
                $this->_findByOptions[] = array(
                    'value' => 'shipment',
                    'label' => __('Shipment Number'),
                );
            }

            if ($serial && $helper->isMessageEnabled('epicor_comm', 'csns') && in_array('serial', $allowed)) {
                $this->_findByOptions[] = array(
                    'value' => 'serial',
                    'label' => __('Serial Number'),
                );
            }
        }

        return $this->_findByOptions;
    }

    public function addMethodAllowed($type)
    {
        $allowed = true;

        if (!$this->checkConfigFlag('allow_mixed_return')) {
            $lines = $this->getReturnLines();

            foreach ($lines as $line) {
                /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */
                $source = $line->getSourceType();
                if ($type == 'addsku') {
                    if ($source != 'sku') {
                        $allowed = false;
                    }
                } else {
                    if ($source == 'sku') {
                        $allowed = false;
                    }
                }
            }
        }

        return $allowed;
    }

    public function getReturnLines()
    {
        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        $linesData = array();
        if ($return) {
            $linesData = $return->getLines() ?: array();
        }

        return $linesData;
    }


}
