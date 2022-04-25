<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\QuickOrderPad\Controller\Form;

class Results extends \Epicor\QuickOrderPad\Controller\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $catalogSearchHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Quickorderpad
     */
    protected $listsQopHelper;

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogSearch\Helper\Data $catalogSearchHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Epicor\Lists\Helper\Frontend\Quickorderpad $listsQopHelper,
        \Magento\Search\Model\QueryFactory $queryFactory
    )
    {
        $this->registry = $registry;
        $this->catalogSearchHelper = $catalogSearchHelper;
        $this->storeManager = $storeManager;
        $this->commProductHelper = $commProductHelper;
        $this->listsQopHelper = $listsQopHelper;
        $this->queryFactory = $queryFactory;
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }


    /**
     * Results Page
     */
    public function execute()
    {

        foreach ($this->getRequest()->getParams() as $key => $value) {
            if (substr($key, 0, 4) == 'amp;')
                $this->getRequest()->setParam(substr($key, 4), $value);
        }


        $q = $this->getRequest()->getParam('q', '');
        $instock = $this->getRequest()->getParam('instock', '');

        $this->registry->register('search-query', $q);
//        Mage::register('search-sku', $sku);
        $this->registry->register('search-instock', $instock != '' ? true : false);

        if ($q != '') {

            /** @var \Magento\Search\Model\Query $query */
            $query = $this->queryFactory->get();

            $query->setStoreId($this->storeManager->getStore()->getId());
            if ($this->catalogSearchHelper->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            } else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity() + 1);
                } else {
                    $query->setPopularity(1);
                }

                $query->prepare();
            }

            $this->catalogSearchHelper->checkNotes();

            if (!$this->catalogSearchHelper->isMinQueryLength()) {
                $query->save();
            }

            $result = $this->resultPageFactory->create();
            return $result;
        } else {
            // remove product from configure products list
            $helper = $this->commProductHelper;

            $csv = $this->getRequest()->getParam('csv');
            if (($csv && $helper->sessionHasConfigureList()) || ($this->listsQopHelper->listsEnabled() && $this->listsQopHelper->getSessionList())) {
                $result = $this->resultPageFactory->create();
                return $result;
            } else {
                return $this->_redirect('quickorderpad/form');
            }
        }
    }

}
