<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Index extends \Epicor\SalesRep\Controller\Account\Manage
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(\Epicor\SalesRep\Controller\Context $context)
    {
        $this->resultPageFactory = $context->getResultPageFactory();
        parent::__construct($context);
    }


}
