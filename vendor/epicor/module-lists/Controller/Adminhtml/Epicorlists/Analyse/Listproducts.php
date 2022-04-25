<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Analyse;

class Listproducts extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Analyse
{
     /** @var \Magento\Framework\View\LayoutFactory */
    protected $layoutFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context, $backendAuthSession);
        $this->layoutFactory = $layoutFactory;
    }

    public function execute()
    {
        if ($this->getRequest()->get('grid')) {
            $this->getResponse()->setBody(
                $this->layoutFactory->create()->createBlock('Epicor\Lists\Block\Adminhtml\Listing\Analyse\Products\Grid')->toHtml()
            );
        } else {
            $data = $this->getRequest()->getPost('data');
            $data = json_decode(base64_decode($data), true);
            if ($data) {
                $this->backendAuthSession->setAnalyseProductsData($data);
                $this->getResponse()->setBody(
                    $this->layoutFactory->create()->createBlock('Epicor\Lists\Block\Adminhtml\Listing\Analyse\Products')->toHtml()
                );
            }
        }
    }

    }
