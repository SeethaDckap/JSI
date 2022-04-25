<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Controller\Form;

class Configclear extends \Epicor\QuickOrderPad\Controller\Form
{

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Product $commProductHelper
    ) {
        $this->commProductHelper = $commProductHelper;
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }


/**
     * Default Page
     */
    public function execute()
    {
        $helper = $this->commProductHelper;
        /* @var $helper Epicor_Comm_Helper_Product */

        $helper->clearConfigureList();
        $this->_redirect('quickorderpad/form');
    }

    }
