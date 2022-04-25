<?php

namespace Silk\CustomAccount\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Custom shipping model
 */
class Kuebix extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'kuebix';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    private $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $rateMethodFactory;

    private $resourceConnection;

    protected $registry;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->resourceConnection = $resourceConnection;
        $this->registry = $registry;
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/kuebix.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if($this->registry->registry('not_collect_kuebix')){
            $this->registry->unregister('not_collect_kuebix');
            return false;
        }

        
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();
        $upperchargeType = $this->getConfigData('uppercharge_type');
        $upperchargeValue = $this->getConfigData('uppercharge_value');
        $rates = $this->getShippingMethods($request);

        if(!empty($rates)){
            foreach ($rates as $carrierCode => $methods) {
                if(!empty($methods)){
                    foreach ($methods as $rate) {
                        if(isset($rate['serviceType']) && $rate['serviceType'] !== 'Guaranteed'){
                             /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                            $price = (float) $rate['totalPrice'];
                            if($upperchargeType){
                                if($upperchargeType == 'fixed'){
                                    $price = $price + (float) $upperchargeValue;
                                }

                                if($upperchargeType == 'percent'){
                                    $price = $price * (1 + ((float) $upperchargeValue) / 100);
                                }
                            }

                            $method = $this->rateMethodFactory->create();
                            $method->setCarrier($this->_code);
                            $method->setCarrierTitle($rate['carrierName']);
                            $method->setMethod($carrierCode . '_' . strtolower(str_replace(' ', '_', $rate['service'])));
                            $method->setMethodTitle($rate['service']);
                            $method->setPrice($price);
                            $result->append($method);

                            $logger->info('Return method: ' . $carrierCode . '_' . strtolower(str_replace(' ', '_', $rate['service'])));
                            $logger->info('Origin Price: ' . (float) $rate['totalPrice']);
                            $logger->info('Final Price: ' . $price);
                        }
                    }
                }
            }
        }

        return $result;
    }



    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    public function getShippingMethods($request){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/kuebix.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info('Trigger call api');

        // $shippingAddress = $this->checkoutSession->getQuote()->getShippingAddress();
        // $extensionAttributes = $shippingAddress->getExtensionAttributes();
        // $liftgate = $extensionAttributes->getLiftgate();
        // $delivery = $extensionAttributes->getDelivery();
        // $overlength = false;
        // $residential = $extensionAttributes->getResidential();
        $liftgate = 1;
        $delivery = 1;
        $residential = 1;
        $logger->info('liftgate: ' . $liftgate);
        $logger->info('delivery: ' . $delivery);
        $logger->info('residential: ' . $residential);

        $countryMapping = [
            "US" => "United States"
        ];

        $warehouseAddress = [
            "Fall River" => [
                "street" => "485 Commerce Drive",
                "city" => "Fall River",
                "state" => "MA",
                "postcode" => "02720",
                "key_id" => $this->getConfigData('ma_id')
            ],
            "Chicago" => [
                "street" => "380 Veterans Parkway Suite 100",
                "city" => "Bolingbrook",
                "state" => "IL",
                "postcode" => "60440",
                "key_id" => $this->getConfigData('cabinetry_id')
            ],
            "Atlanta" => [
                "street" => "4175 Boulder Ridge Drive",
                "city" => "Atlanta",
                "state" => "GA",
                "postcode" => "30336",
                "key_id" => $this->getConfigData('ga_id')
            ],
            "Denver" => [
                "street" => "4725 Leyden Street Unit C",
                "city" => "Denver",
                "state" => "CO",
                "postcode" => "80216",
                "key_id" => $this->getConfigData('co_id')
            ],
        ];

        try {
            $warehouseName = $this->getWarehouseByState($request->getDestRegionCode(), $request->getDestPostcode());
            $originAddress = isset($warehouseName) ? $warehouseAddress[$warehouseName] : $warehouseAddress['Fall River'];
            $requestData = [
                "origin" => [
                    "companyName" => "",
                    "country" => $countryMapping[$request->getDestCountryId()],
                    "stateProvince" => $request->getDestRegionCode(),
                    "city" => $request->getDestCity(),
                    "streetAddress" => $request->getDestStreet(),
                    "postalCode" => $request->getDestPostcode(),
                ],
                "destination" => [
                    "companyName" => "",
                    "country" => "United States",
                    "stateProvince" => $originAddress["state"],
                    "city" => $originAddress["city"],
                    "streetAddress" => $originAddress["street"],
                    "postalCode" => $originAddress["postcode"]
                ],
                "billTo" => [
                    "companyName" => "",
                    "country" => "United States",
                    "stateProvince" => $originAddress["state"],
                    "city" => $originAddress["city"],
                    "streetAddress" => $originAddress["street"],
                    "postalCode" => $originAddress["postcode"]
                ],
                "lineItems" => [],
                "handlingUnits" => [],
                "client" => [
                    "id" => $originAddress["key_id"]
                ],
                "weightUnit" => "LB",
                "lengthUnit" => "IN",
                "shipmentType" => "LTL",
                "totalSkids" => 1,
                "shipmentMode" => "Dry Van",
                "paymentType" => "Outbound Prepaid",
                "pickupReady" => "",
                "pickupClose" => ""
            ];

            $itemInformations = ['sku_set' => [], 'items' => []];
            $totalQty = 0;
            $totalWeight = 0;
            $freightClass = 0;
            if(!empty($request->getAllItems())){
                foreach ($request->getAllItems() as $item) {
                    $additionalOptions = $item->getBuyRequest()->getAdditionalOptions();
                    $data = [
                        "qty" => $item->getQty(),
                        "type" => ""
                    ];

                    if(!empty($additionalOptions)){
                        foreach ($additionalOptions as $additionalOption) {
                            if(isset($additionalOption['base_sku']) && $additionalOption['base_sku']){
                                $data["sku"] = $additionalOption['base_sku'];
                                $data["type"] = "Assemble";
                                break;
                            }
                        }
                    }
                    else{
                        $data["sku"] = $item->getProduct()->getSku();
                        $data["type"] = "Unassemble";
                    }

                    $itemInformations["sku_set"][] = $data["sku"];
                    $itemInformations['items'][] = $data;

                    $totalQty += $item->getQty();
                }
            }

            if(!empty($itemInformations)){
                $logger->info('item info: ' . json_encode($itemInformations));
                $itemInformation = $this->getItemInformation($itemInformations);
                $overlength = $itemInformation['hasOverLength'];
                $totalWeight = $itemInformation['totalWeight'];
                $freightClass = $itemInformation['freightClass'];
                $logger->info('has overlength: ' . $overlength);
            }

            $requestData['lineItems'][] = [
                "packageType" => "Case(s)",
                "inPackages" => $totalQty,
                "description" => "All Items",
                "weight" => $totalWeight,
                "freightClass" => $freightClass,
                "sku" => "All Items",
                "quantity" => $totalQty
            ];


            $requestData['totalSkids'] = 1;

            if($liftgate || $delivery || $overlength || $residential){
                $requestData["accessorials"] = [];
                if($liftgate){
                    $requestData["accessorials"][] = [
                        "code" => "Liftgate",
                        "accessorialType" => "Delivery"
                    ];
                }
                if($delivery){
                    $requestData["accessorials"][] = [
                        "code" => "Delivery Appointment",
                        "accessorialType" => "Delivery"
                    ];
                }
                if($overlength){
                    $requestData["accessorials"][] = [
                        "code" => "Overlength",
                        "accessorialType" => "Delivery"
                    ];
                }
                if($residential){
                    $requestData["accessorials"][] = [
                        "code" => "Residential",
                        "accessorialType" => "Delivery"
                    ];
                }
            }

            
            $logger->info('Request: ' . json_encode($requestData));

            $url = $this->getConfigData('url');
            $username = $this->getConfigData('username');
            $password = $this->getConfigData('password');
            $headers = [
                "Content-Type:application/json",
                "Authorization: Basic ". base64_encode("$username:$password")
            ];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $responseJson = curl_exec($curl);
            curl_close($curl);
            //$logger->info('Response: ' . $responseJson);

            $response = json_decode($responseJson, true);

            if(!empty($response) && isset($response['rateMap']) && !empty($response['rateMap'])){
                return $response['rateMap'];
            }
        } catch (\Exception $e) {
            $logger->info($e->getMessage());
        }

    }

    private function getItemInformation($itemInformations){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/kuebix.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $tableName = $this->resourceConnection->getTableName('decoder_base_sku');
        $connection = $this->resourceConnection->getConnection();
        $unassembleResult = [];
        $assembleResult = [];
        $totalWeight = 0;
        $hasOverlength = false;
        $totalCubes = 0;
        if(!empty($itemInformations['sku_set']) && !empty($itemInformations['items'])){
            $qry = $connection
                ->select()
                ->from($tableName)
                ->where('base_sku in (?)', $itemInformations['sku_set']);
            $results = $connection->fetchAll($qry);
            $formattedResult = [];

            if(!empty($results)){
                foreach ($results as $result) {
                    $formattedResult[$result['base_sku']] = $result;
                }
            }

            foreach ($itemInformations['items'] as $item) {
                if(isset($formattedResult[$item['sku']])){
                    $height = $item["type"] == "Assemble" ? $formattedResult[$item['sku']]['assembled_height'] : $formattedResult[$item['sku']]['unassembled_height'];
                    $width = $item["type"] == "Assemble" ? $formattedResult[$item['sku']]['assembled_width'] : $formattedResult[$item['sku']]['unassembled_width'];
                    $length = $item["type"] == "Assemble" ? $formattedResult[$item['sku']]['assembled_height'] : $formattedResult[$item['sku']]['unassembled_length'];
                    if($width > 84.7 || $length > 84.7 || $height > 84.7){
                        $hasOverlength = true;
                    }
                    $weight = $item["qty"] * $formattedResult[$item['sku']]['weight'];
                    $totalWeight += $weight;
                    $totalCubes += (float)($width * $length * $height) / 1728.00;
                }
                
            }
        }

        $logger->info('totalCubes: ' . $totalCubes);
        $logger->info('totalWeight: ' . $totalWeight);

        $freightClass = $this->getFreightClass($totalWeight, $totalCubes);

        $data = [
            "totalWeight" => $totalWeight,
            "freightClass" => $freightClass,
            "hasOverLength" => $hasOverlength
        ];
        $logger->info('processed item data: ' . json_encode($data));

        return $data;
    }

    private function getFreightClass($totalWeight, $totalCubes){
        $density = (float)$totalWeight / (float)$totalCubes;
        $tableName = $this->resourceConnection->getTableName('decoder_kuebix_freight');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, ['freight_class'])
            ->where('level > (?)', $density)
            ->order('level ASC');

        $result = $connection->fetchCol($qry);
        return $result ? $result[0] : 0;
    }

    public function getWarehouseByState($state, $zipcode){
        $tableName = $this->resourceConnection->getTableName('warehouse_mapping');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, ['warehouse'])
            ->where('state = (?)', $state)
            ->where(new \Zend_Db_Expr("zipcode = " . $zipcode . " or zipcode = ''"));

        $result = $connection->fetchCol($qry);

        return $result ? $result[0] : 'Fall River';
    }
}