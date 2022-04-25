<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Silk\CustomAccount\Observer\Bsv;

use Epicor\Comm\Model\RepriceFlag as RepriceFlag;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\App\ObjectManager;

class UpdateBsvRequestAssemble implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/assemble.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        /* @var $bsv \Epicor\Comm\Model\Message\Request\Bsv */
        $bsv = $observer->getEvent()->getMessage();
        $data = $bsv->getMessageArray();

        try {
            if (isset($data['messages']['request']['body']['lines']['line']) && is_array($data['messages']['request']['body']['lines']['line'])) {
                $bGoodstotal = 0;
                $bGoodstotalInc = 0;
                $totalsRecalculation = false;
                foreach ($data['messages']['request']['body']['lines']['line'] as &$line) {
                    $itemId = $line['_attributes']['itemId'];
                    $item = $bsv->getLineItem($itemId);
                    if ($item) {
                        $sku = $item->getSku();
                        if(strpos($sku, '-', strpos($sku, '-') + 1)){
                            $subSku = substr($sku, strpos($sku, '-', strpos($sku, '-') + 1) + 1);
                            $baseSku = substr($sku, 0, strpos($sku, '-', strpos($sku, '-') + 1));
                            if($subSku){
                                $components = explode('-', $subSku);
                                if(!empty($components)){
                                    $line['assembly'] = ['components' => ['component' => []]];
                                    $line['assembly']['components']['component'][] = [
                                        'productCode' => $baseSku,
                                        'quantity' => 1,
                                        'unitOfMeasureCode' => 'EA'
                                    ];
                                    foreach ($components as $component) {
                                        $componentData = [
                                            'productCode' => $component,
                                            'quantity' => 1,
                                            'unitOfMeasureCode' => 'EA'
                                        ];
                                        $line['assembly']['components']['component'][] = $componentData;
                                    }


                                }
                            }
                        }
                    }
                }
            }
            $bsv->setMessageArray($data);
        } catch (\Exception $e) {
            $logger->info('Error: ' . $e->getMessage());
        }

        
    }
}
