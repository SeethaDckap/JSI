<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Lists;

class Autosuggest extends \Epicor\Customerconnect\Controller\Generic
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * Grid Collection
     *
     * @var \Epicor\Lists\Model\ResourceModel\SidebarLists
     */
    private $collection;


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context,
     * @param \Magento\Customer\Model\Session $customerSession,
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver,
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory,
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory,
     * @param \Epicor\Lists\Model\ResourceModel\SidebarLists $collection
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Epicor\Lists\Model\ResourceModel\SidebarLists $collection
    )
    {
        $this->layoutFactory = $layoutFactory;
        $this->collection = $collection;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * List ajax reload of grid tab
     *
     * @return void
     */
    public function execute()
    {
        $block = $this->getLayoutFactory()->create()->createBlock('Magento\Framework\View\Element\Template');
        $block->setTemplate('Epicor_Lists::customer/autosuggestsidebar.phtml');
        $block->setCollection($this->getItems());
        $output = $block->toHtml();

        $this->getResponse()->appendBody($output);

    }

    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory()
    {
        return $this->layoutFactory;
    }

    /**
     * Get list of Lists
     *
     * @return array
     */
    protected function getItems()
    {
        $title = $this->getRequest()->getParam('list_q', false);
        $listsCollections = $this->collection->getCollection();
        $listsCollections->addFieldToFilter('title', array('like' => '%'.$title.'%'));
        $listsCollections->getSelect()->limit(\Epicor\Lists\Model\ResourceModel\SidebarLists::SIDEBAR_LISTS_LIMIT);
        return $listsCollections;
    }
}
