<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

class FindClaimDetail extends \Epicor\Dealerconnect\Controller\Claims 
{

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
        \Epicor\Dealerconnect\Model\Message\Request\Deid $dcMessageRequestDeid,
        \Epicor\Dealerconnect\Helper\Data $dealer_helper
    )
    {
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
        $result = array();
        $data= $this->getRequest()->getParams(); 
        $deid = $this->dcMessageRequestDeid;
        $messageTypeCheck = $deid->getHelper()->getMessageType('DEID');
        
        if(is_array($data) && count($data)>0){
            if($data['location_num']!=null && $data['account_num']!=null){
                
                if($deid->isActive() && $messageTypeCheck){ 
                    $response = $this->prepareMessage($data);
                    if($response!=false){
                       // $this->registry->register('claim_search_detail',$response);
                        $parse_data = $this->dealer_helper->DeidParseResponse($response);
                        
                       $result = array('type'=>'success', 'model'=>$parse_data);
                    }else{
                        $error = 'Claim Detail not found'; 
                    }
                }else{
                    $error = 'DEID not Active'; 
                }
            }
        }else{
            $error = 'Invalid Record';
        }
        
        if($error!=false){
             $result = array('type'=>'error', 'message'=>$error);
        }
        
       $this->getResponse()->setHeader('Content-type', 'application/json');
       $this->getResponse()->setBody(json_encode($result));
    
    }
    /*
     * Prepare message for getting DEID Record
     */
    public function prepareMessage($data){
        $results = array();
        $helper = $this->commMessagingHelper;
       
        $deid = $this->dcMessageRequestDeid;
        $deid->setAccountNumber($data['account_num'])
             ->setLocationNumber($data['location_num'])
             ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
        
         if ($deid->isActive()) {
            if ($deid->sendMessage()) { 
                $results = $deid->getResults();  
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
     * DEID parse reponse from XML data
     */
    
//    public function DeidParseResponse($response){
//        
//       $deid_data = array(); 
//       if($response!=null){
//           
//            if($response->getLocationNumber()!=null){
//                 $deid_data = array(
//                     'locationNumber'=>$response->getLocationNumber(),
//                     'identificationNumber'=>($response->getIdentificationNumber()) ?$response->getIdentificationNumber() : "",
//                     'serialNumber'=>($response->getSerialNumber()) ? $response->getSerialNumber() : null,
//                     'productCode'=>($response->getProductCode()) ? $response->getProductCode() :null,
//                     'description'=>($response->getDescription()) ? $response->getDescription() : null,
//                     'orderNumber'=>($response->getOrderNumber()) ? $response->getOrderNumber() : null
//                 );
//                 
//                 $obj_ownder_add = $response->getOwnerAddress();
//                 
//                 if(!empty($obj_ownder_add) && $obj_ownder_add!=null){
//                        $owner_address_data= array(
//                            'accountNumber' =>$obj_ownder_add->getAccountNumber(),
//                            'addressCode' =>$obj_ownder_add->getAddressCode(),
//                            'name' =>$obj_ownder_add->getName(),
//                            'contactName' =>$obj_ownder_add->getContactName(),
//                            'address1' =>$obj_ownder_add->getAddress1(),
//                            'address2' =>$obj_ownder_add->getAddress2(),
//                            'address3' =>$obj_ownder_add->getAddress3(),
//                            'city' =>$obj_ownder_add->getCity(),
//                            'county' =>$obj_ownder_add->getCounty(),
//                            'country' =>$obj_ownder_add->getCountry(),
//                            'postcode' =>$obj_ownder_add->getPostcode(),
//                            'telephoneNumber' =>$obj_ownder_add->getTelephoneNumber(),
//                            'faxNumber' =>$obj_ownder_add->getFaxNumber(),
//                            'emailAddress' =>$obj_ownder_add->getEmailAddress()
//
//                        );
//                      $deid_data['ownerAddress']= $owner_address_data;
//                 }
//                 
//                  $soldToAddress = $response->getSoldToAddress();
//                   
//                  if(!empty($soldToAddress) && $soldToAddress!=null){
//	$sold_to_address_data= array(
//                            'accountNumber' =>$soldToAddress->getAccountNumber(),
//                            'addressCode' =>$soldToAddress->getAddressCode(),
//                            'name' =>$soldToAddress->getName(),
//                            'contactName' =>$soldToAddress->getContactName(),
//                            'address1' =>$soldToAddress->getAddress1(),
//                            'address2' =>$soldToAddress->getAddress2(),
//                            'address3' =>$soldToAddress->getAddress3(),
//                            'city' =>$soldToAddress->getCity(),
//                            'county' =>$soldToAddress->getCounty(),
//                            'country' =>$soldToAddress->getCountry(),
//                            'postcode' =>$soldToAddress->getPostcode(),
//                            'telephoneNumber' =>$soldToAddress->getTelephoneNumber(),
//                            'faxNumber' =>$soldToAddress->getFaxNumber(),
//                            'emailAddress' =>$soldToAddress->getEmailAddress()
//
//	);
//	  $deid_data['soldToAddress']= $sold_to_address_data;
//                }
//                  return $deid_data;
//            }else{
//                 return false;
//            }
//       }else{
//           return false;
//       }
//    } 
}
