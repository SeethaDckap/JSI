<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Esdm\Block;


class Savedcards extends \Epicor\AccessRight\Block\Template
{
     protected $_gridFactory; 

     protected $customerSession;

     private $tokenRequestData;


     public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Esdm\Model\TokenFactory $gridFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Esdm\Helper\ClientTokenData $clientTokenData,
        array $data = []
     ) {
        $this->_gridFactory = $gridFactory;
        $this->customerSession = $customerSession;
        $this->tokenRequestData = $clientTokenData;
        parent::__construct($context, $data);
        $this->pageConfig->getTitle()->set('Esdm - Saved Cards');
        $collectionData = $this->prepareCollection();
        $this->setCollection($collectionData);
    }

    /**
     * Build data for ESDM Collections
     */
    protected function prepareCollection()
    {
        $customer_id = $this->customerSession->getCustomer()->getId();
        //get collection of data 
        $collection = $this->_gridFactory->create()->getCollection();
        $collection->addFieldToFilter('customer_id', $customer_id);
        $collection->addFieldToFilter('reuseable', 1);
        return $collection;
    }    
  
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            // create pager block for collection 
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'epicor.esdm.record.pager'
            )->setCollection(
                $this->getCollection() // assign collection to pager
            );
            $this->setChild('pager', $pager);// set pager block in layout
        }
        return $this;
    }

    /**
     * @return string
     */
    // method for get pager html
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }   

    public function getCardImage($cardType) 
    {
        return $this->tokenRequestData->getCardTypeImage($cardType);
    }

    public function getDeleteUrl($card_id)
    {
        return $this->getUrl('esdm/savedcards/delete/', array('card_id' => $card_id));
    }

    public function isDeleteAllowed()
    {
        return $this->_isAccessAllowed("Epicor_Customer::my_account_esdm_delete");
    }

}
?>