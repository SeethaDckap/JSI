<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager;


/**
 * Certificates admin controller
 *
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
abstract class Certificates extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    /**
     * @var \Epicor\HostingManager\Model\CertificateFactory
     */
    protected $hostingManagerCertificateFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\HostingManager\Model\CertificateFactory $hostingManagerCertificateFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->hostingManagerCertificateFactory = $hostingManagerCertificateFactory;
        $this->backendSession = $backendAuthSession;
        parent::__construct($context, $backendAuthSession);
    }


    /**
     *
     * @param int $id
     * @return \Epicor\HostingManager\Model\Certificate
     */
    protected function _loadCertificate($id)
    {
        $model = $this->hostingManagerCertificateFactory->create();
        /* @var $model \Epicor\HostingManager\Model\Certificate */

        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = $this->backendSession->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Certificate does not exist'));
                $this->_redirect('*/*/certificates');
            }
        }

        $this->_registry->register('current_certificate', $model);
        return $model;
    }

}
