<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Catalog\Search\Advance;

use \Magento\Framework\View\Result\PageFactory;
use \Epicor\AccessRight\Helper\Data as AccessRightHelper;
use \Magento\CatalogSearch\Controller\Advanced\Index as SearchAdvanceIndex;
/**
 * Advanced search controller.
 */
class Index
{

    /**
     * Index resultPageFactory.
     *
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * AccessAuthorization.
     *
     * @var AccessRightHelper
     */
    private $accessAuthorization;


    /**
     * Index constructor.
     *
     * @param PageFactory       $resultPageFactory PageFactory.
     * @param AccessRightHelper $authorization     AccessRightHelper.
     */
    public function __construct(
        PageFactory $resultPageFactory,
        AccessRightHelper $authorization
    ) {
        $this->resultPageFactory   = $resultPageFactory;
        $this->accessAuthorization = $authorization->getAccessAuthorization();

    }//end __construct()


    /**
     * Around Execute.
     *
     * @param SearchAdvanceIndex $subject AdvanceSearch.
     * @param \Closure           $proceed Closer.
     *
     * @return \Magento\Framework\View\Result\Page|mixed
     */
    public function aroundExecute(
        SearchAdvanceIndex $subject,
        \Closure $proceed
    ) {
        // Validate accessRights.
        if ($this->accessAuthorization->isAllowed('Epicor_Checkout::catalog_advance_search') === false) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->getUpdate()->addHandle('frontend_denied');
            $resultPage->getLayout()->unsetElement('content');
            $resultPage->getLayout()->getBlock('page.main.title')->setTemplate('Epicor_AccessRight::access_denied.phtml');

            return $resultPage;
        }

        return $proceed();

    }//end aroundExecute()


}
