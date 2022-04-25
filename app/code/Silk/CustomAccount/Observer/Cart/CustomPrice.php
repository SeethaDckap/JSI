<?php
namespace Silk\CustomAccount\Observer\Cart;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Epicor\Comm\Helper\Data as EpicorHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomPrice implements ObserverInterface
{
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        EpicorHelper $epicorHelper,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->epicorHelper = $epicorHelper;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->log('set custom price observer');
        try {
            $useCustomApi = $this->scopeConfig->getValue('customapi/switch/enable', 
    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $item = $observer->getEvent()->getData('quote_item');

            $additionalOptions = $item->getBuyRequest()->getAdditionalOptions();
            $isAssemble = $additionalOptions && !empty($additionalOptions);
            if($isAssemble){
                $this->log('Product Option SKU: ' . json_encode($additionalOptions));
            }
            else{
                $this->log('Product Option Empty');
            }

            if($useCustomApi == 1){
                $item = $observer->getEvent()->getData('quote_item');
                $this->log('SKU: ' . $item->getSku());
                $this->log('Qty: ' . $item->getQty());
                
                $customPrice = $this->getProductApiPrice($item, $isAssemble);
                $this->log('item custom price: ' . $customPrice);

                if ($customPrice){
                    $item->setCustomPrice($customPrice);
                    $item->setOriginalCustomPrice($customPrice);
                    $item->getProduct()->setIsSuperMode(true);
                }
            }
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }

        return $this;
    }

    private function getProductApiPrice($item, $isAssemble) {
        $customPrice = null;
        $sku = $item->getSku();
        $qty = $item->getQty() ? $item->getQty() : 1;
        $locationId = $item->getBuyRequest()->getLocationId();
        $templateId = $item->getBuyRequest()->getTemplateId();
        $this->log('locationId: ' . $locationId);
        $this->log('templateId: ' . $templateId);

        $info = [
            'location_id' => $locationId,
            'template_id' => $templateId,
            'is_assemble' => $isAssemble
        ];

        if($locationId){
            $customPrice = $this->callPriceApi($sku, $qty, $info);
        }

        return $customPrice;
    }

    private function callPriceApi($sku, $qty, $info){
        $customPrice = null;
        $customerId = $this->customerSession->getCustomerId();
        $erpAccount = $this->epicorHelper->getErpAccountInfo();
        $erpCustomerId = null;
        $company = null;
        if($erpAccount){
            $erpCustomerId = $erpAccount->getAccountNumber();
            $companyId = $erpAccount->getCompany();
            $headers = [
                "Content-Type:application/json",
                "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1OTU5MjMzMjEsInN1YiI6Imd1bmFzZWVsYW5lQGRja2FwLmNvbSIsIm5hbWUiOiJndW5hc2VlbGFuZUBkY2thcC5jb21fXzIwMjAtMDctMjggMDg6MDI6MDEuOTczNzEzX18iLCJpc191bmxpbWl0ZWQiOnRydWV9.-mOr8gabqCmSQyRndBflbs-opLSGdu535_0lgtlMIA4",

            ];
            if($info['is_assemble']){
                $url = "https://lightning.cloras.com/v1/dynamic/listener/6220c0b82943b4007cebacfd";
                $data = [
                    "assembly_item_id" => $sku,
                    "customer_id" => $erpCustomerId,
                    "location_id" => $info['location_id'],
                    "template_id" => $info['template_id']
                ];

                $this->log('Assemble Request: ' . json_encode($data));

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                $responseJson = curl_exec($curl);
                $info = curl_getinfo($curl);
                if(isset($info['total_time'])){
                    $this->log('Took ' . $info['total_time'] . ' seconds');
                }
                curl_close($curl);

                $this->log('Assemble Response: ' . $responseJson);

                $response = json_decode($responseJson, true);

                if(!empty($response) && !empty($response['data']) && isset($response['data']['template_details']) && isset($response['data']['template_details']['price'])){
                    $customPrice = $response['data']['template_details']['price'];
                }
            }
            else{
                $url = "https://lightning.cloras.com/v1/dynamic/listener/620eb1392943b4000ed00e3c";
                $data = [
                    "company_id" => $companyId,
                    "customer_id" => $erpCustomerId,
                    "location_id" => $info['location_id'],
                    "products" => [
                        [
                            "item_id" => $sku,
                            "uom" => "EA",
                            "qty" => $qty
                        ]
                    ]
                ];

                $this->log('Regular Request: ' . json_encode($data));

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                $responseJson = curl_exec($curl);
                $info = curl_getinfo($curl);
                if(isset($info['total_time'])){
                    $this->log('Took ' . $info['total_time'] . ' seconds');
                }
                curl_close($curl);

                $this->log('Regular Response: ' . $responseJson);

                $response = json_decode($responseJson, true);

                if(!empty($response) && !empty($response['data']) && isset($response['data'][0]) && isset($response['data'][0]['unit_price'])){
                    $customPrice = $response['data'][0]['unit_price'];
                }
            }
            

            
        }
        else{
            throw NoSuchEntityException(__('Customer does not belong to any ERP account'));
        }

        return $customPrice;
    }

    private function log($message){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/observer.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
    }
}
