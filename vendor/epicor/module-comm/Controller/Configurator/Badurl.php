<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Configurator;

class Badurl extends \Epicor\Comm\Controller\Configurator {

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $_resultLayoutFactory;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {

        $this->_resultLayoutFactory = $resultLayoutFactory;
        parent::__construct(
                $context
        );
    }

    public function execute() {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $this->_view->getLayout();

        /** @var \Foo\Bar\Block\Popin\Content $block */
        $block = $layout->createBlock(\Magento\Framework\View\Element\Template::class);
        $block->setTemplate('Epicor_Comm::epicor_comm/catalog/product/ewabadurl.phtml');

        $this->getResponse()->setBody($block->toHtml());
    }

}
