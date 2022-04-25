<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Esdm\Helper;

class ClientTokenData extends \Magento\Framework\App\Helper\AbstractHelper
{

    const TEST_MODE = 0;
    const LIVE_MODE = 1;
    const DEMO_MODE = 2;

	private $_encryptor;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;	

    protected $_storeManager;    

    protected $customerSession;

    protected $tokenCollectionFactory;   

    protected $assetRepo;
    
    protected $_date;
    
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Encryption\EncryptorInterface $encryptor,
		\Magento\Framework\View\Asset\Repository $assetRepo,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Epicor\Esdm\Model\ResourceModel\Token\CollectionFactory $tokenCollectionFactory,
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
	) {
		parent::__construct($context);
		$this->_encryptor = $encryptor;
		$this->_storeManager = $storeManager;    
		$this->customerSession = $customerSession;
		$this->assetRepo  = $assetRepo;
		$this->tokenCollectionFactory = $tokenCollectionFactory;
                $this->_date =  $date;
	}


    private function getLive()
    {
        return $this->scopeConfig->getValue('payment/esdm/live_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == self::LIVE_MODE;
    }
    
    private function getDemoMode()
    {
        return $this->scopeConfig->getValue('payment/esdm/live_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == self::DEMO_MODE;
    }	

    public function generateTokenRequestData()
    {

    $controllerUrl = $this->_storeManager->getStore()->getUrl('esdm/index/opcsavereview');
    $demoMode = false;
    if($this->getDemoMode()) {
            $demoMode = true;
    }

            $data = [
                    'url' => $controllerUrl,
                    'demoMode'=>$demoMode,
                    'storedCards' => $this->getCustomerSavedCards()

            ];
            return $data;
    }

    public function getConfigValue($key)
    {
       return $this->scopeConfig->getValue('payment/esdm/' . $key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    public function getCustomerSavedCards($customerId = null)
    {
    	if (!$customerId) {
    		$customerId = $this->customerSession->getCustomer()->getId();
	    	$collection = $this->tokenCollectionFactory->create();
	    	$collection->addFieldToFilter('customer_id', $customerId);
	    	$collection->addFieldToFilter('reuseable', 1)->load(); 
	    	$cardInfo = array();
	    	$cardDetails = array();
	    	foreach ($collection->getItems() as $card) {
                    if($this->isCardValid($card->getExpiryDate())){
	    		$cardInfo['value'] = $card->getId();
	    		$cardInfo['type']  = $card->getCardType();
	    		$cardInfo['lastFour']  = '**** **** **** '.$card->getLastFour() ." , Expiry Date :". date('m / Y', strtotime($card->getExpiryDate()));
	    		$cardInfo['cardImage']  = $this->getCardTypeImage($card->getCardType());
	    		$encodeJson = json_encode( array('cc_type'=>$card->getCardType(),'exp_month'=>date('m', strtotime($card->getExpiryDate())),'exp_year'=>date('Y', strtotime($card->getExpiryDate())),'mask'=>'************'.$card->getLastFour()));
	    		$cardInfo['optionValues']  =$encodeJson;
	    		$cardDetails[] = $cardInfo;
	    	}
                }

	    	return $cardDetails;
    	}	
    }	


    public function getCardTypeImage($card_type)
    {
       $createAsset = $this->assetRepo->createAsset('Epicor_Esdm::images/cardtypes/' . strtolower($card_type) . '.gif');
       return $createAsset->getUrl();
    }    


    public function getEsdmLogo()
    {
       $createAsset = $this->assetRepo->createAsset('Epicor_Esdm::images/esdm.jpg');
       return $createAsset->getUrl();
    }        
    /**
     * @method isCardExpired
     * get the card date is valid or expired
     * @return type bool
    */
    public function isCardValid($cardDate = null) {
        $valid = false;
        $todayDate = $this->_date->date()->format('Y-m-d H:i:s');
        if ($todayDate && $cardDate) {
            $todayDate = $todayDate;
            $cardDate = $cardDate;
            $thisMonth = date('m', strtotime($todayDate));
            $thisYear = date('Y', strtotime($todayDate));
            $cardMonth = date('m', strtotime($cardDate));
            $cardYear = date('Y', strtotime($cardDate));
            if ($cardYear >= $thisYear) {
                if ($cardYear == $thisYear) {
                    if ($cardMonth >= $thisMonth) {
                        $valid = true;
                    } else {
                        $valid = false;
                    }
                } elseif ($cardYear > $thisYear) {
                    $valid = true;
                }
            }
        }
        return $valid;
    }


}
