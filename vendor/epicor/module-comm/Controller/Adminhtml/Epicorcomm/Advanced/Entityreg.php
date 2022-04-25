<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Advanced;


/**
 * Epicor_Comm_Adminhtml_Message_SynController
 * 
 * Controller for Epicor > Messages > Send SYN
 * 
 * @author Gareth.James
 */
abstract class Entityreg extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    /**
     * @var \Epicor\Comm\Model\Entity\RegisterFactory
     */
    protected $commEntityRegisterFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
     /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Entity\RegisterFactory $commEntityRegisterFactory,
        \Epicor\Comm\Helper\Data $commHelper,   
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->commEntityRegisterFactory = $commEntityRegisterFactory;
        $this->commHelper = $commHelper;
        $this->messageManager = $context->getMessageManager();
        parent::__construct($context, $backendAuthSession);
    }
    protected function _initPage()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Comm::advanced');
        return $resultPage;
        /*
        $this->loadLayout()
            ->_setActiveMenu('epicor_common/advanced/entity_register')
            ->_addBreadcrumb(__('Entity Register'), __('Entity Register'));
        return $this;
        */
    }
    
    protected function delete($id, $mass = false)
    {
        /* @var $model Epicor_Comm_Model_Entity_Register */
        $model = $this->commEntityRegisterFactory->create();
        
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $model->setToBeDeleted(true);
                if ($model->save()) {
                    if (!$mass) {
                         $this->messageManager->addSuccess(__('Uploaded data entry marked for deletion'));
                    }
                } else {
                    $this->messageManager->addError(__('Could not delete Uploaded data entry ' . $id));
                }
            }
        }
    }

}
