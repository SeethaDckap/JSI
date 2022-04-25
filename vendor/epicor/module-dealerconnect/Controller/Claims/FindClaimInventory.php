<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

class FindClaimInventory extends \Epicor\Dealerconnect\Controller\Claims 
{

    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\Deis
     */
    protected $dcMessageRequestDeis;
    
    /*
     * @var   \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealer_helper;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Helper\Messaging $dealerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Dealerconnect\Model\Message\Request\Dcld $dealerconnectMessageRequestDcld,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Dealerconnect\Model\Message\Request\Deis $dcMessageRequestDeis,
        \Epicor\Dealerconnect\Model\Message\Request\Deid $dcMessageRequestDeid,
        \Epicor\Dealerconnect\Helper\Data $dealer_helper
    )
    {
        $this->dcMessageRequestDeis = $dcMessageRequestDeis;
        $this->dealer_helper = $dealer_helper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $registry,
            $dealerconnectHelper,
            $request,
            $dealerconnectMessageRequestDcld,
            $generic,
            $commonAccessHelper,
            $commMessagingHelper,
            $commConfiguratorHelper,
            $commProductHelper,
            $catalogProductFactory,
            $storeManager,
            $commMessageRequestCdmFactory,
            $scopeConfig,
            $commonXmlvarienFactory,
            $urlDecoder,
            $encryptor,
            $dcMessageRequestDeid
        );
    }

    /**
     *  action
     */
    public function execute()
    {  
        $error = false;
        $deis = $this->dcMessageRequestDeis;
        $messageTypeCheck = $deis->getHelper()->getMessageType('DEIS');
        
        if ($postdata = $this->getRequest()->getPostValue()) { 
            
            if($postdata['claim_number']!=null){ 
                if($deis->isActive() && $messageTypeCheck){ 
                    $response = $this->prepareMessage($deis ,$postdata);
                    if($response!=false){
                        $row_count = count($response);
                        if(is_array($response) && $row_count >0){
                            if($row_count== 1){
                                  $final_result =  $this->DeidPrepareSingleCall($response);
                                  if($final_result!=false){
                                      $this->getResponse()->setHeader('Content-type', 'application/json');
                                       $this->getResponse()->setBody(
                                               json_encode(array('type'=>'success' , 'model'=>$final_result))); 
                                  }  
                            }else{
                                $this->registry->register('claim_search_record',$response);
                                $result = $this->resultLayoutFactory->create();
                                $this->getResponse()->setBody(
                                    $result->getLayout()->createBlock('Epicor\Dealerconnect\Block\Claims\FindClaimInventoryList')->toHtml()
                                );
                            }
                        }else{
                            $error = __('No Record Found'); 
                        }
                    }else{
                        $error = __('No matching Record found for given data.'); 
                    }
                }else{
                    $error = __('DEIS message is not active'); 
                }
            }
        }
        
        if($error!=false){
              $this->getResponse()->setHeader('Content-type', 'application/json');
              $this->getResponse()->setBody(json_encode(array('type'=>'error' , 'message'=>$error)));
        }
        
    }
    /*
     * Prepare call
     */
    public function prepareMessage($deis ,$postdata){
        $results = array();
        $helper = $this->commMessagingHelper;
        $erpCustomer = $helper->getErpAccountInfo();
        $deis->setAccountNumber($erpCustomer->getErpCode());
        $deis->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
        
        $deis->addSearchOption($postdata['claimby'], 'LIKE', $postdata['claim_number']);
         if ($deis->isActive()) {
            if ($deis->sendMessage()) { 
                $results = $deis->getResults(); 
                return $results;
            } else {
                  return false;
            }
        } else {
            $results = array();
            return false;
        }
    }
    
    /*
     * 
     */
    public function DeidPrepareSingleCall($response){
       $deis_data = array(); 
       if($response!=null & is_array($response)){
           foreach($response as $key=>$row){
                $loc_address= $row->getLocationAddress();
               if($row->getLocationNumber()!=null && $loc_address->getAccountNumber()!=null){
                    $deis_data = array(
                        'location_num'=>$row->getLocationNumber(),
                        'account_num'=>$loc_address->getAccountNumber()
                    );
               }
           }
           if(count($deis_data)>0){
               $deid_result = $this->getDeidData($deis_data);
                if($deid_result!=false){
                      return $this->dealer_helper->DeidParseResponse($deid_result);
                }else{
                    return false;
                }
           }else{
               return false;
           }
       }else{
           return false;
       }
    }
    /*
     * Prepare message for getting DEID Record
     */
    public function getDeidData($data){
        $results = array();
        $helper = $this->commMessagingHelper;
       
        $deid = $this->dcMessageRequestDeid;
        $deid->setAccountNumber($data['account_num'])
             ->setLocationNumber($data['location_num'])
             ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
        
         if ($deid->isActive()) {
            if ($deid->sendMessage()) { 
                $results = $deid->getResults();
                return (!empty($results)) ? $results : false;
            } else {
                  return false;
            }
        } else {
            return false;
        }
    }
   
}
