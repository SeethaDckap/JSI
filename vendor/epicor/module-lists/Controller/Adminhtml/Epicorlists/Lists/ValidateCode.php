<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class ValidateCode extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    public function __construct(
        \Epicor\Lists\Helper\Data $listsHelper
    ) {
        $this->listsHelper = $listsHelper;
    }
    public function execute()
    {
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */
        $this->getResponse()->setBody(json_encode($helper->validateNewListCode($this->getRequest())));
    }

}
