<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Controller\Adminhtml\Faqs;

class NewAction extends \Epicor\Faqs\Controller\Adminhtml\Faqs
{

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Create new Faqs item
     */
    public function execute()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    }
