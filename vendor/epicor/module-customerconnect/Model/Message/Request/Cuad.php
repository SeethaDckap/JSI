<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CUAD - Customer returns details enquiry  
 * 
 * Websales requesting search for orders for account
 * 
 * XML Data Support - Request
 * /brand/company                                           - supported
 * /brand/branch                                            - supported
 * /brand/warehouse                                         - supported
 * /brand/group                                             - supported 
 * /accountNumber                                           - supported
 * /orderNumber                                             - supported
 * /languageCode                                            - supported


 * 
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Cuad extends \Epicor\Customerconnect\Model\Message\Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('CUAD');
        $this->setConfigBase('customerconnect_enabled_messages/CUAD_request/');
        $this->setResultsPath('customer');
    }

    public function buildRequest()
    {
        $data = $this->getMessageTemplate();
        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
        $data['messages']['request']['body']['languageCode'] = $this->getLanguageCode();

        $defaultPeriodFrom = $this->getConfig('period_balance_from');


        //M1 > M2 Translation Begin (Rule 32)
        //$data['messages']['request']['body']['periodBalanceFrom'] = $this->getHelper()->getLocalDate(strtotime(date('Y-m-01', strtotime('-' . $defaultPeriodFrom . ' month'))), self::DATE_FORMAT);
        $data['messages']['request']['body']['periodBalancesFrom'] = $this->getHelper()->getLocalDate(strtotime(date('Y-m-01', strtotime('-' . $defaultPeriodFrom . ' month'))), \IntlDateFormatter::LONG);
        //M1 > M2 Translation End
        $this->setOutXml($data);

        return true;
    }
     public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            $this->setResults($this->getResponse()->getVarienDataFromPath($this->getResultsPath()));
            $invoiceAddress = $this->getResponse()->getVarienDataFromPath('customer/invoice_address');
            if($invoiceAddress){
                 $this->registry->register('cuad_invoice_address_exists', true, true);
            }else{
                 $this->registry->register('cuad_invoice_address_exists', false, true);             
            }
            return true;
        } else {
            return false;
        }
    }    
     public function sendMessage(\Zend_Http_Client $connection = null)
    {
         //this calls the logcompleted method on conclusion of the cuad processResponse method
         $result = parent::sendMessage($connection);
         
         //only run this if cuad has otherwise completed successfully 
         if($this->getStatusCode() == '200'){             
            if (!$this->registry->registry('cuad_invoice_address_exists')) {

                //this removes the previously set 'Success'
                $this->unsetStatusDescription();

                $this->setStatusDescription($this->getErrorDescription(self::STATUS_INVOICE_ADDRESS_NOT_SUPPLIED_ERROR, 'CUAD'));

                $this->setStatusCode(self::STATUS_INVOICE_ADDRESS_NOT_SUPPLIED_ERROR);
                $this->setStatus(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_ERROR);
            } else {
                $this->setStatus(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_SUCCESS);
            }
            
            $this->logCompleted(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_SUCCESS);
         }
         return $result;
         
     }

}
