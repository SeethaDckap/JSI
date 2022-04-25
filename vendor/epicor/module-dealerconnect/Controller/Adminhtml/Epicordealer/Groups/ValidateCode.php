<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class ValidateCode extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;

    public function __construct(
        \Epicor\Dealerconnect\Helper\Data $dealerHelper
    ) {
        $this->dealerHelper = $dealerHelper;
    }
    public function execute()
    {
        $helper = $this->dealerHelper;
        /* @var $helper Epicor_Dealerconnect_Helper_Data */
        $this->getResponse()->setBody(json_encode($helper->validateNewGroupCode($this->getRequest())));
    }

}
