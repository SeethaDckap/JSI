<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns;


/**
 * Returns creation page, Abstract block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class AbstractBlock extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->commReturnsHelper = $commReturnsHelper;
        $this->registry = $registry;
        $this->layout = $context->getLayout();
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
    }

    public function getEncodedReturn()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */
        if ($this->registry->registry('return_id')) {
            $return = $helper->encodeReturn($this->registry->registry('return_id'));
        } else {
            $return = '';
        }

        return $return;
    }

    public function getEncodedLines()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */
        if ($this->registry->registry('return_lines')) {
            $lines = $helper->encodeReturn($this->registry->registry('return_lines'));
        } else {
            $lines = '';
        }

        return $lines;
    }

    public function getEncodedLineAttachments()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */
        if ($this->registry->registry('return_line_attachments')) {
            $data = $helper->encodeReturn($this->registry->registry('return_line_attachments'));
        } else {
            $data = '';
        }

        return $data;
    }

    public function getEncodedAttachments()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */
        if ($this->registry->registry('return_attachments')) {
            $data = $helper->encodeReturn($this->registry->registry('return_attachments'));
        } else {
            $data = '';
        }

        return $data;
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function getReturn()
    {
        return $this->registry->registry('return_model');
    }

    /**
     * 
     * @return array
     */
    public function getLines()
    {
        return $this->registry->registry('return_lines');
    }

    /**
     * 
     * @return array
     */
    public function getLineAttachments()
    {
        return $this->registry->registry('return_line_attachments');
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function getAttachments()
    {
        return $this->registry->registry('return_attachments');
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function returnActionAllowed($action)
    {
        $hasAction = false;
        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
        if ($return) {
            $hasAction = $return->isActionAllowed($action);
        }

        return $hasAction;
    }

    /**
     * Checks a return config flag
     * 
     * @return boolean
     */
    public function checkConfigFlag($path, $type = null)
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        return $helper->checkConfigFlag($path, $type);
    }

    /**
     * Checks a return config value is present
     * 
     * @return boolean
     */
    public function configHasValue($path, $value, $type = null)
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        return $helper->configHasValue($path, $value, $type);
    }

    /**
     * get return type flag
     * 
     * @return boolean
     */
    public function getReturnType()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        return $helper->getReturnUserType();
    }

    public function getReturnBarHtml()
    {
        return $this->layout->createBlock('\Epicor\Comm\Block\Customer\Returns\Returnbar')->toHtml();
    }

    public function returnExists()
    {

        if ($this->registry->registry('return_model')) {
            $return = $this->registry->registry('return_model');
            /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

            $exists = $return->getId() ? true : false;
        } else {
            $exists = false;
        }

        return $exists;
    }

}
