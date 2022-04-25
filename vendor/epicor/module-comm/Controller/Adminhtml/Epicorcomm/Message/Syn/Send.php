<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Syn;

class Send extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Syn
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    /**
    * @var \Epicor\Comm\Model\Message\Request\SynFactory
    */
    protected $commMessageRequestSynFactory;
     /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Message\Request\SynFactory $commMessageRequestSynFactory,
        \Epicor\Comm\Helper\Data $commHelper
    )
    {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commMessageRequestSynFactory = $commMessageRequestSynFactory;
        $this->commHelper = $commHelper;
        $this->messageManager = $context->getMessageManager();
        parent::__construct($context, $backendAuthSession, $commMessageLogFactory, $commMessagingHelper);
        
    }
         
    public function execute()
    {
        
        if ($data = $this->getRequest()->getPost()) {
            if (property_exists($data, 'advanced_messages')) {       // if advanced messages exist the advanced option has been selected - ignore simple
                $messages = $data['advanced_messages'];
            } else {
                $messages = explode(',', implode(',', $data['simple_messages'])); // implode multimessage elements to string then explode string to array of single messages
            }

            $messageWeighting = $this->commMessagingHelper->getMessageTypeWeighting();
            // order selected messages according to weighting
            $sortedMessageWeighting = array_intersect($messageWeighting, $messages);
            $syn = $this->commMessageRequestSynFactory->create();
            /* @var $syn Epicor_Comm_Model_Message_Request_Syn */

            $syn->addMessageType($sortedMessageWeighting);
            $syn->addLanguage($data['languages']);
            $syn->setTrigger('Admin');

            if (!empty($data['stores'])) {
                $websites = array();
                $stores = array();
                foreach ($data['stores'] as $storeId) {
                    if (strpos($storeId, 'website_') !== false) {
                        $websites[] = str_replace('website_', '', $storeId);
                    } else {
                        $stores[] = str_replace('store_', '', $storeId);
                    }
                }

                if (!empty($websites)) {
                    $syn->setWebsites($websites);
                }

                if (!empty($stores)) {
                    $syn->setStores($stores);
                }
            }

            if (isset($data['sync_type']) && $data['sync_type'] == 'partial') {
                $time = $data['time'][0] . ':' . $data['time'][1] . ':' . $data['time'][2];
                $stringToTime = strtotime($data['date'] . ' ' . $time); 
                $helper = $this->commHelper;
                $UTCDateTime = $helper->UTCwithOffset($stringToTime);  
                $syn->setFrom($UTCDateTime);
            }
            
            if ($syn->sendMessage()) {
                $this->messageManager->addSuccess(__('SYN request successfully sent.'));
                $this->_redirect('*/*/index');
            } else { 
                //M1 > M2 Translation Begin (Rule 55)
                $this->messageManager->addError(__('SYN request failed. Status description - %1', $syn->getStatusDescriptionText()));
                $this->_redirect('*/*/index');
            } 
        }
    }

}
