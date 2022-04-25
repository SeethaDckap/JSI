<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Certificates;

class Save extends \Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Certificates
{

    /**
     * @var \Epicor\HostingManager\Helper\Data
     */
    protected $hostingManagerHelper;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\HostingManager\Helper\Data $hostingManagerHelper,
        \Epicor\HostingManager\Model\CertificateFactory $hostingManagerCertificateFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->hostingManagerHelper = $hostingManagerHelper;

        parent::__construct($context, $hostingManagerCertificateFactory, $backendAuthSession);
    }


    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $id = $this->getRequest()->getParam('id', null);

            $cert = $this->_loadCertificate($id);
            $this->backendSession->setFormData($data);
            $helper = $this->hostingManagerHelper;
            /* @var $helper \Epicor\HostingManager\Helper\Data */
            try {

                $cert->addData($data);
                $selfCertSuccess = $csrSuccess = true;

                if ($this->getRequest()->getParam('generate_csr', null)) {
                    $csrSuccess = $cert->generateCSR();
                }

                if ($this->getRequest()->getParam('create_ssc', null)) {
                    $selfCertSuccess = $cert->createSelfSignCertificate();
                }

                $cert->save();


                if ($selfCertSuccess === false) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error creating self signed certificate'));
                }
                if ($csrSuccess === false) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error generating certificate signing request'));
                }

                if (!$cert->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving certificate'));
                }
                $this->messageManager->addSuccessMessage(__('Certificate was successfully saved.'));
                $this->backendSession->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $cert->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($cert && $cert->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $cert->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        $this->messageManager->addErrorMessage(__('No data found to save'));
        $this->_redirect('*/*/');
    }

}
