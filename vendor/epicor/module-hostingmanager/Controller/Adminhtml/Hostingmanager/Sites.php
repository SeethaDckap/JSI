<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager;


/**
 * Sites admin controller
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
abstract class Sites extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    /**
     * @var \Epicor\HostingManager\Model\SiteFactory
     */
    protected $hostingManagerSiteFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\HostingManager\Model\SiteFactory $hostingManagerSiteFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->registry = $context->getRegistry();
        $this->_resultPageFactory = $context->getResultPageFactory();
        $this->hostingManagerSiteFactory = $hostingManagerSiteFactory;
        $this->backendSession = $backendAuthSession;
        parent::__construct($context, $backendAuthSession);
    }


    protected function _loadSite($id)
    {

        $model = $this->hostingManagerSiteFactory->create();
        /* @var $model \Epicor\HostingManager\Model\Site */

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('Site does not exist'));
                $this->_redirect('*/*/sites');
            }
        }

        if (!$this->registry->registry('current_site')) {
            $this->registry->register('current_site', $model);
        }

        return $model;
    }

}
