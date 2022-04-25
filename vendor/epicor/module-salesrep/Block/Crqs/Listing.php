<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Crqs;


/**
 * Customer RFQ list
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []    
    ) {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        
         parent::__construct(
            $context,
            $data
        );
        
    }
    
    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }
    
    protected function _setupGrid()
    {
        $this->_controller = 'crqs_listing';
        $this->_blockGroup = 'Epicor_SalesRep';
        $this->_headerText = __('RFQs');

        $helper = $this->commonAccessHelper;
        /* @var $helper Epicor_Common_Helper_Access */

        $msgHelper = $this->commMessagingHelper;
        /* @var $msgHelper Epicor_Comm_Helper_Messaging */
        $enabled = $msgHelper->isMessageEnabled('customerconnect', 'crqu');

        if ($enabled && $msgHelper->isMasquerading() && $helper->customerHasAccess('Epicor_Customerconnect', 'Rfqs', 'new', '', 'Access')) {
            $url = $this->getUrl('customerconnect/rfqs/new/');
            $this->addButton(
                'new', array(
                'label' => __('New Quote'),
                'onclick' => 'javascript:setLocation(\'' . $url . '\')',
                'class' => 'save',
                ), 10
            );
            
        }
    }

}
