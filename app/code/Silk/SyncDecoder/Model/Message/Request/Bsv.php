<?php

namespace Silk\SyncDecoder\Model\Message\Request;

use Epicor\Comm\Helper\BsvAndGor;
use \Epicor\Comm\Model\RepriceFlag as RepriceFlag;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

class Bsv extends \Epicor\Comm\Model\Message\Request\Bsv
{

    const MESSAGE_TYPE = 'BSV';

    private $bsvAndGorHelper;

    private $resourceConnection;

    private $sku;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Epicor\Comm\Helper\Messaging\Customer $commMessagingCustomerHelper,
        \Magento\Tax\Model\ClassModelFactory $taxClassModelFactory,
        \Epicor\Common\Helper\Cart $commonCartHelper,
        \Magento\Quote\Model\QuoteFactory $quoteQuoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Epicor\Comm\Helper\BsvAndGor $bsvAndGor = null,
        ResourceConnection $resourceConnection = null
    )
    {
        parent::__construct(
            $context, 
            $commonHelper,
            $arpaymentsHelper,
            $customerFactory,
            $commMessagingCustomerHelper,
            $taxClassModelFactory,
            $commonCartHelper,
            $quoteQuoteFactory,
            $quoteRepository,
            $checkoutSession,
            $resource,
            $resourceCollection,
            $data,
            $bsvAndGor,
            $resourceConnection
        );
        $this->bsvAndGorHelper = $bsvAndGor;
        $this->resourceConnection = $resourceConnection ?: ObjectManager::getInstance()->get(ResourceConnection::class);
    }

    public function setSku($sku){
        $this->sku = $sku;
        return $this;
    }

    public function getSku(){
        return $this->sku;
    }

    public function buildObjectRequest()
    {
        parent::buildObjectRequest();
        $data = $this->getMessageArray();
        $sku = $this->getSku();

        $line = [[
            '_attributes' => array(
                'number' => 1,
                'itemId' => 0,
                'preventRepricing' => 'N'
            ),
            'productCode' => $sku,
            'unitOfMeasureCode' => 'EA',
            'contractCode' => '',
            'locationCode' => '',
            'decimalPlaces' => '',
            'quantity' => 1,
            'price' => 0,
            'priceInc' => 0,
            'lineValue' => 0,
            'lineValueInc' => 0,
            'lineDiscount' => 0,
            'taxCode' => 'ITEM',
            'dateRequired' => $data['messages']['request']['header']['datestamp'],
            'eccGqrLineNumber' => '',
            'attributes' => [],
        ]];
        // if(strpos($sku, '-', strpos($sku, '-') + 1)){
        //     $subSku = substr($sku, strpos($sku, '-', strpos($sku, '-') + 1) + 1);
        //     $baseSku = substr($sku, 0, strpos($sku, '-', strpos($sku, '-') + 1));
        //     if($subSku){
        //         $components = explode('-', $subSku);
        //         if(!empty($components)){
        //             $line['assembly'] = ['components' => ['component' => []]];
        //             $line['assembly']['components']['component'][] = [
        //                 'productCode' => $baseSku,
        //                 'quantity' => 1,
        //                 'unitOfMeasureCode' => 'EA'
        //             ];
        //             foreach ($components as $component) {
        //                 $componentData = [
        //                     'productCode' => $component,
        //                     'quantity' => 1,
        //                     'unitOfMeasureCode' => 'EA'
        //                 ];
        //                 $line['assembly']['components']['component'][] = $componentData;
        //             }
        //         }
        //     }
        // }

        $data['messages']['request']['body']['lines']['line'] = $line;

        $this->setOutXml($data);

        return true;
    }

    public function processResponseArray()
    {
        $response = $this->getResponse();



        $linesGroup = $response['lines'];
        $price = 0;
        if ($linesGroup && isset($linesGroup['line'])) {
            $lines = $linesGroup['line'];

            if (!is_array($lines)) {
                $lines = array($lines);
            }
            
            if(isset($lines['price'])){
                $price = $lines['price'];
            }

        }
        
        return $price;
    }


    public function processResponse()
    {
        $response = $this->getResponse();
        $linesGroup = $response->getLines();
        $price = 0;

        if ($linesGroup) {
            $lines = $linesGroup->getLine();


            if (!is_array($lines)) {
                $lines = array($lines);
            }

            $line = $lines[0];
            $price = $line->getData('price');
        }
        return $price;
    }

    private function getCustomerDefaultShippingAddress()
    {
        return $this->bsvAndGorHelper
            ->getCustomerDefaultShippingAddress($this->customerSession, $this->getQuoteAddresses());
    }

    private function getQuoteAddresses()
    {
        if ($this->_quote instanceof \Epicor\Comm\Model\Quote) {
            return $this->_quote->getAddresses();
        }
    }

    private function getCustomerShippingAddress()
    {
        return $this->getQuoteShippingAddress() ?: $this->getCustomerDefaultShippingAddress();
    }

    private function getQuoteShippingAddress()
    {
        return $this->_quote->getIsMultiShipping() ? $this->getShippingAddress() : $this->_quote->getShippingAddress();
    }

    private function buildProductAttributes($product)
    {
        $attributes = array();
        if ($product->getMsqAttributes()) {
            foreach ($product->getMsqAttributes() as $key => $value) {
                $attributes['attribute'][] = array(
                    'description' => $key,
                    'value' => $value
                );
            }
        }
        return $attributes;
    }

    /**
     * @param $quote
     */
    private function cleanEmptyQuote($quote)
    {
        $data = [
            'ecc_bsv_goods_total' => null,
            'ecc_bsv_goods_total_inc' => null,
            'ecc_bsv_carriage_amount' => null,
            'ecc_bsv_carriage_amount_inc' => null,
            'ecc_bsv_discount_amount' => null,
            'ecc_bsv_grand_total' => null,
            'ecc_bsv_grand_total_inc' => null,
            'base_subtotal' => 0,
            'subtotal' => 0,
            'base_subtotal_incl_tax' => 0,
            'base_subtotal_total_incl_tax' => 0,
            'subtotal_incl_tax' => 0,
            'base_shipping_amount' => 0,
            'shipping_amount' => 0,
            'base_shipping_incl_tax' => 0,
            'shipping_incl_tax' => 0,
            'base_grand_total' => 0,
            'grand_total' => 0
        ];

        $address = $quote->getShippingAddress();
        $address->setBaseTotalAmount('subtotal', 0);
        $address->setBaseTotalAmount('grand', 0);
        $address->setTotalAmount('subtotal', 0);
        $address->setTotalAmount('grand', 0);
        $address->addData($data);
        $quote->addData($data);
    }

}
