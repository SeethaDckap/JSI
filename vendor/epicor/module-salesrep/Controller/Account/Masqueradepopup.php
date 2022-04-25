<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account;

class Masqueradepopup extends \Epicor\SalesRep\Controller\Account
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
    public function execute()
    {
        $result = $this->resultPageFactory->create();

        return $result;
    }

}
