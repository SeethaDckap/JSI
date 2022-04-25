<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogController
 *
 * @author David.Wylie
 */
abstract class Log extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    protected $_returns = array('CREU', 'CRRC');
    protected $_aclId = 'Epicor_Comm::message_log';

    /**
     * @var \Epicor\Comm\Model\Message\LogFactory
     */
    protected $commMessageLogFactory;


    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;


    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commMessageLogFactory=$commMessageLogFactory;
        parent::__construct($context, $backendAuthSession);
    }

    protected function _initPage()
    {
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::message');

        $resultPage->getConfig()->getTitle()->prepend(__('Messaging Log'));

        return $resultPage;
    }

    protected function getBackRoute()
    {
        $source = $this->getRequest()->getParam('source');

        switch ($source) {
            case 'customer':
                $route = 'adminhtml/epicorcomm_customer_erpaccount/edit';
                break;
            case 'product' :
                $route = 'adminhtml/catalog_product/edit';
                break;
            case 'order' :
                $route = 'adminhtml/sales_order/view';
                break;
            case 'return' :
                $route = 'adminhtml/epicorcomm_returns/view';
                break;
            case 'notification' :
                $route = 'adminhtml/notification';
                break;
            case 'list' :
                $route = 'epicor_lists/epicorlists_lists/edit';
                break;
            default:
                $route = '*/*';
        }

        return $route;
    }

    protected function getSourceParams()
    {
        $sourceIdParam = $this->getRequest()->getParam('sourceid', null);
        if (!empty($sourceIdParam)) {
            $source = $this->getRequest()->getParam('source');
            if ($source == 'order') {
                $sourceId = array('order_id' => $sourceIdParam);
            } else {
                $sourceId = array('id' => $sourceIdParam);
            }
        } else {
            $sourceId = null;
        }

        return $sourceId;
    }


    protected function reprocess($id)
    {
        $model = $this->commMessageLogFactory->create();
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $rawType = $model->getMessageType();
                $type = strtolower($rawType);
                $note = "$rawType for '" . $model->getMessageSubject() . "'";
                if ($model->getMessageParent() != 'Upload') {
                    $this->messageManager->addWarningMessage(__("$note is not an Upload Message so cannot be Reprocessed"));
                } else {
                    $model->setMessageStatus(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_REPROCESSED);
                    $model->save();

                    $response = $helper->processSingleMessage($model->getXmlIn());
                    if ($response->getIsSuccessful()) {
                        $this->messageManager->addSuccessMessage(__("Re-processed $note successfully see log entry for details"));
                    } else {
                        $this->messageManager->addErrorMessage(__("Re-processing $note failed see log entry for details"));
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('Log entry missing'));
            }
        }
    }
    protected function delete($id, $mass = false)
    {
        $model = $this->commMessageLogFactory->create();
        /* @var $helper Epicor_Comm_Helper_Data */
        if ($id) {
            $model->load($id);
            if ($model->getId()) {

                if ($model->delete()) {
                    if (!$mass) {
                        $this->messageManager->addSuccessMessage(__('Message log entry deleted'));
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('Could not delete Message Log ' . $id));
                }
            }
        }
    }

}
