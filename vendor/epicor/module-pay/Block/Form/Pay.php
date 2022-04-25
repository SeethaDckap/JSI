<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Pay\Block\Form;


class Pay extends \Magento\Payment\Block\Form
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->scopeConfig= $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('pay/form/pay.phtml');
    }
    //M1 > M2 Translation Begin (Rule p2-5.11)
    public function getStoreConfigFlag($path)
    {
        return $this->scopeConfig->isSetFlag($path);
    }
    //M1 > M2 Translation End

}
