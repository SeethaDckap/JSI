<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class NonErpProductsProxy extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // check if non erp products is on and proxy set
        $options = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($enabled && $options == 'proxy'){
           $message = $observer->getEvent()->getMessage();
           $this->registry->unregister('message_type');
           $this->registry->register('message_type', $message->getMessageType());
           $message_array = $message->getMessageArray();
           $proxy = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/proxy_sku', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
           $messageType = $message->getMessageType();
           $lines=[];
           if($messageType == 'CRQU'){               
                $lines = $message_array['messages']['request']['body']['quote']['lines']['line'];
           }else{   
               if(isset($message_array['messages']['request']['body']['lines']['line'])){
                $lines = $message_array['messages']['request']['body']['lines']['line'];
               }
           }
           $lines = is_array($lines) ? $lines : (array)$lines;
           $sendAdminEmail = false;
           $sendEmailMsgs = array('GQR'=>true, 'CRQU'=>true);
           $message_product_skus = array();
           $productQtyAndValue = array();
           $nonErpProduct = false;
           foreach($lines as $key=>$line){
               if($messageType == 'CRQU'){                   
                  $productSku = $line['productCode'][0];
                  if(is_array($productSku)){
                      $productSku = $productSku['value'];
                      $productQtyAndValue[$productSku] = array('qty'=>$line['quantity'], 'value'=>$line['lineValue']);
                  }else{
                      $productQtyAndValue[$productSku] = array('qty'=>$line['quantity'], 'value'=>$line['lineValue']);
                  }
               }else{  
                    $productSku = $line['productCode'];  
                    //needs to be saved as by time gor is sent, cart is empty
                    if($messageType == 'GOR'){                       
                        $productQtyAndValue[$productSku] = array('qty'=>$line['quantity'], 'value'=>$line['lineValue']);
                    }
               }
               $productId = $this->catalogProductFactory->create()->getIdBySku($productSku);        
               $product = $this->catalogProductFactory->create()->load($productId);    
               $productQtyAndValue[$productSku]['name'] = $product->getName();
               $productQtyAndValue[$productSku]['id'] = $product->getId();
               $message_product_skus[$productSku] = $productQtyAndValue[$productSku];  
               if(!$product->getEccStkType()){
                    //if no stk type, product is non erp
                    switch ($messageType) {
                        case 'GOR':
                            $cart = $this->checkoutCart->create()->getQuote();
                            $orderNumber = $message_array['messages']['request']['body']['order']['orderReference'];
                            $this->registry->unregister('GOR_order_number');
                            $this->registry->register('GOR_order_number', $orderNumber);
                            $this->registry->unregister('GOR_request_message');
                            $this->registry->register('GOR_request_message', $message);
                            $line['customer']['lineText'] = !empty($line['customer']['lineText']) ? $line['customer']['lineText']."\nProxy SKU supplied for product:".$productSku."\n" : "Proxy SKU supplied for product:".$productSku;
                            $line['productCode'] = $proxy;
                            $sendAdminEmail = true;
                            break;
                        case 'CRQU':
                            $line['productCode'] = $proxy; 
                            $line['description'] = !empty($line['description']) ? $line['description']."\nProxy SKU supplied for product: ".$productSku."\n" : "Proxy SKU supplied for product: ".$productSku;
                            break;
                        default:
                            //used for BSV and GQR
                            $line['productCode'] = $proxy; 
                            $line['lineStatus']['description'] = !empty($line['lineStatus']['description']) ? $line['lineStatus']['description']."\nProxy SKU supplied for product:".$productSku."\n": "Proxy SKU supplied for product: ".$productSku;         
                            break;
                    }
                   if($messageType == 'CRQU'){                       
                        unset($message_array['messages']['request']['body']['quote']['lines']['line'][$key]);
                        $message_array['messages']['request']['body']['quote']['lines']['line'][$key] = $line;
                   }else{                       
                        unset($message_array['messages']['request']['body']['lines']['line'][$key]);
                        $message_array['messages']['request']['body']['lines']['line'][$key] = $line;
                   }
                   $nonErpProduct = true;
               }
           }  
           $this->registry->unregister('message_product_skus');
           $this->registry->register('message_product_skus', $message_product_skus);
           if($nonErpProduct){               
                $message->setMessageArray($message_array);
           }
           //send email message
           if(isset($sendEmailMsgs[$messageType])){ 
               $this->commHelper->create()->retrieveNonErpProductsInCart($message, true);
           }    
        }
    }

}