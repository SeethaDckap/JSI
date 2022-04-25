<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Plugin;

/**
 * Data controller
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
abstract class Action
{

    const FRONTEND_RESOURCE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultPageFactory;

    public function __construct(
        \Epicor\AccessRight\Helper\Data $authorization,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {

        $this->_accessauthorization = $authorization->getAccessAuthorization();
        $this->resultPageFactory = $resultPageFactory;

    }

    public function isAllowed()
    {
        if (!$this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE)) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->getUpdate()->addHandle('frontend_denied_account');
            $resultPage->getLayout()->unsetElement('content');
            $resultPage->getLayout()->getBlock('page.main.title')->setTemplate('Epicor_AccessRight::access_denied.phtml');
            return $resultPage;

        }
        return false;
    }

}
