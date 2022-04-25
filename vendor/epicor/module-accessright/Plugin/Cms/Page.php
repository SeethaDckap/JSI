<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Plugin\Cms;

class Page
{

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        \Epicor\AccessRight\Helper\Data $authorization,
        \Magento\Cms\Model\Page $page,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {

        $this->_accessauthorization = $authorization->getAccessAuthorization();
        $this->_page = $page;
        $this->_storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function aroundPrepareResultPage(
        \Magento\Cms\Helper\Page $subject, callable $proceed,
        \Magento\Framework\App\Action\Action $action,
        $pageId
    )
    {

        if ($pageId !== null && $pageId !== $this->_page->getId()) {
            $delimiterPosition = strrpos($pageId, '|');
            if ($delimiterPosition) {
                $pageId = substr($pageId, 0, $delimiterPosition);
            }
            $this->_page->setStoreId($this->_storeManager->getStore()->getId());
            if (!$this->_page->load($pageId)) {
                return  $proceed($action, $pageId);
            }
        }
        $id = $this->_page->getId();
        if ($id && !$this->_accessauthorization->isAllowed('Epicor_CMS::cms_'.$id)) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->unsetElement('content');
            $resultPage->getLayout()->unsetElement('columns');
            $resultPage->getLayout()->getUpdate()->addHandle('frontend_denied_account_default');
            $resultPage->getLayout()->getBlock('page.main.title')->setTemplate('Epicor_AccessRight::access_denied.phtml');
            $handle = $this->_page->getPageLayout();
            $resultPage->getConfig()->setPageLayout($handle);
            return $resultPage;

        }
        $result = $proceed($action, $pageId);
        return $result;
    }

}