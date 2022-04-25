<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Supplierconnect\Setup;


use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class UpgradeData implements UpgradeDataInterface
{
    protected $configWriter;

    protected $cacheTypeList;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        WriterInterface $configWriter
    )
    {
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;

        try{
            $state->setAreaCode('frontend');
        }catch (\Magento\Framework\Exception\LocalizedException $e)
        { /* DO NOTHING, THE AREA CODE IS ALREADY SET */
        }

    }


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.6.3.0', '<')) {
            $this->version_1_0_6_3_0($setup);
        }

        if (version_compare($context->getVersion(), '1.0.6.3.5', '<')) {
            $this->cacheTypeList->cleanType('config');
            $this->version_1_0_6_3_5($setup);
        }

        if (version_compare($context->getVersion(), '1.0.6.3.6', '<')) {
            $this->cacheTypeList->cleanType('config');
            $this->version_1_0_6_3_6($setup);
        }

        if (version_compare($context->getVersion(), '1.0.6.3.7', '<')) {
            $this->cacheTypeList->cleanType('config');
            $this->version_1_0_6_3_7($setup);
        }

        if (version_compare($context->getVersion(), '1.0.6.3.8', '<')) {
            $this->cacheTypeList->cleanType('config');
            $this->version_1_0_6_3_8($setup);
        }
    }

    /**
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function version_1_0_6_3_0($setup)
    {
        $this->updatePaymentGridConfig($setup);
    }

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private function updatePaymentGridConfig($setup)
    {
        $value = 'a:5:{s:18:"_1380797566395_395";a:9:{s:6:"header";s:12:"Payment Date";s:4:"type";s:4:"date";s:5:"index";s:12:"payment_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797567947_947";a:9:{s:6:"header";s:15:"Check Reference";s:4:"type";s:4:"text";s:5:"index";s:17:"payment_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797568508_508";a:9:{s:6:"header";s:12:"Check Amount";s:4:"type";s:6:"number";s:5:"index";s:14:"payment_amount";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Supplierconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380797569058_58";a:9:{s:6:"header";s:14:"Invoice Number";s:4:"type";s:4:"text";s:5:"index";s:14:"invoice_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:72:"Epicor_Supplierconnect_Block_Customer_Payments_List_Renderer_Linkinvoice";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380797569706_706";a:9:{s:6:"header";s:14:"Payment Amount";s:4:"type";s:5:"range";s:5:"index";s:22:"invoice_payment_amount";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Supplierconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';

        $writeConnection = $setup->getConnection('core_write');
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'supplierconnect_enabled_messages/SUPS_request/grid_config',
            'value' => $value,
        ];
        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'), $data, ['value']
        );
    }

    private function version_1_0_6_3_5($setup) {

        $values = [
            'epicor_comm_enabled_messages/CRRS_request/grid_config' ,
            'customerconnect_enabled_messages/CUOS_request/grid_config',
            'customerconnect_enabled_messages/CPHS_request/grid_config',
            'customerconnect_enabled_messages/CUIS_request/grid_config',
            'customerconnect_enabled_messages/CUSS_request/grid_config',
            'customerconnect_enabled_messages/CUPS_request/grid_config',
            'customerconnect_enabled_messages/CURS_request/grid_config',
            'customerconnect_enabled_messages/CUCS_request/grid_config',
            'customerconnect_enabled_messages/CRQS_request/grid_config',
            'customerconnect_enabled_messages/CCCS_request/grid_config',
            'customerconnect_enabled_messages/CAPS_request/grid_config',
            'supplierconnect_enabled_messages/SPLS_request/grid_config',
            'supplierconnect_enabled_messages/SPOS_request/grid_config',
            'supplierconnect_enabled_messages/SPOS_request/newpogrid_config',
            'supplierconnect_enabled_messages/SPCS_request/grid_config',
            'supplierconnect_enabled_messages/SURS_request/grid_config',
            'supplierconnect_enabled_messages/SUIS_request/grid_config',
            'supplierconnect_enabled_messages/SUPS_request/grid_config',
            'dealerconnect_enabled_messages/DCLS_request/grid_config',
        ];

        $writeConnection = $setup->getConnection('core_write');
        $readConnection = $setup->getConnection('core_read');
        $tableName =$setup->getTable('core_config_data');
        foreach ($values as $path => $value) {
            $var = $readConnection->query('SELECT config_id,value,path,scope_id,scope FROM '.$tableName.' WHERE path = "'.$value.'"');
            $erpInfo = $var->fetchAll();
            if(count($erpInfo) > 0) {
                foreach ($erpInfo as $valueinfos) {
                    if(!empty($valueinfos['value'])) {
                        $exist = true;
                        $array = unserialize($valueinfos['value']);
                        foreach($array as $key => &$val) {
                            if (!isset($array[$key]['visible'])) {
                                $exist = false;
                                $array[$key]['visible'] = '1';
                            }
                            if (!isset($array[$key]['showfilter'])) {
                                $exist = false;
                                $array[$key]['showfilter'] = '1';
                            }
                            if (!$exist) {
                                $serializeArray = serialize($array);
                                $this->configWriter->save($valueinfos['path'], $serializeArray,$valueinfos['scope'],$valueinfos['scope_id']);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function version_1_0_6_3_6($setup)
    {

        $value = 'a:8:{s:18:"_1380793814806_806";a:10:{s:6:"header";s:9:"PO Number";s:4:"type";s:5:"range";s:7:"options";s:0:"";s:5:"index";s:21:"purchase_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793893373_373";a:10:{s:6:"header";s:10:"Order Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793893954_954";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:48:"supplierconnect/config_source_orderstatusoptions";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793894801_801";a:10:{s:6:"header";s:12:"Ship-To Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"delivery_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793895433_433";a:10:{s:6:"header";s:15:"Ship-To Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"delivery_address_street";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793895954_954";a:10:{s:6:"header";s:12:"Ship-To City";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"delivery_address_city";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793896473_473";a:10:{s:6:"header";s:13:"Ship-To State";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"delivery_address_county";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380793897018_18";a:10:{s:6:"header";s:9:"Confirmed";s:4:"type";s:7:"options";s:7:"options";s:50:"supplierconnect/config_source_confirmstatusoptions";s:5:"index";s:15:"order_confirmed";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:68:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Confirmed";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';

        $writeConnection = $setup->getConnection('core_write');
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'supplierconnect_enabled_messages/SPOS_request/grid_config',
            'value' => $value,
        ];
        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'), $data, ['value']
        );


        $value2 = 'a:9:{s:18:"_1380794775648_648";a:9:{s:6:"header";s:7:"Confirm";s:4:"type";s:4:"text";s:5:"index";s:14:"new_po_confirm";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:68:"Epicor_Supplierconnect_Block_Customer_Orders_Create_Renderer_Confirm";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794798405_405";a:9:{s:6:"header";s:6:"Reject";s:4:"type";s:4:"text";s:5:"index";s:13:"new_po_reject";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:67:"Epicor_Supplierconnect_Block_Customer_Orders_Create_Renderer_Reject";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794798930_930";a:9:{s:6:"header";s:13:"Our PO Number";s:4:"type";s:4:"text";s:5:"index";s:21:"purchase_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794799626_626";a:9:{s:6:"header";s:10:"Order Date";s:4:"type";s:4:"date";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794800354_354";a:9:{s:6:"header";s:12:"Ship-To Name";s:4:"type";s:4:"text";s:5:"index";s:21:"delivery_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380794801098_98";a:9:{s:6:"header";s:15:"Ship-To Address";s:4:"type";s:4:"text";s:5:"index";s:23:"delivery_address_street";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794801650_650";a:9:{s:6:"header";s:12:"Ship-To City";s:4:"type";s:4:"text";s:5:"index";s:21:"delivery_address_city";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794802290_290";a:9:{s:6:"header";s:13:"Ship-To State";s:4:"type";s:4:"text";s:5:"index";s:23:"delivery_address_county";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_State";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794802866_866";a:9:{s:6:"header";s:6:"Status";s:4:"type";s:4:"text";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:70:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Orderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';


        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'supplierconnect_enabled_messages/SPOS_request/newpogrid_config',
            'value' => $value2,
        ];
        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'), $data, ['value']
        );

    }
    private function version_1_0_6_3_7($setup)
    {

        $value = 'a:8:{s:18:"_1380793814806_806";a:10:{s:6:"header";s:9:"PO Number";s:4:"type";s:5:"range";s:7:"options";s:0:"";s:5:"index";s:21:"purchase_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793893373_373";a:10:{s:6:"header";s:10:"Order Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793893954_954";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:48:"supplierconnect/config_source_orderstatusoptions";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:73:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Erporderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793894801_801";a:10:{s:6:"header";s:12:"Ship-To Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"delivery_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793895433_433";a:10:{s:6:"header";s:15:"Ship-To Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"delivery_address_street";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793895954_954";a:10:{s:6:"header";s:12:"Ship-To City";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"delivery_address_city";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380793896473_473";a:10:{s:6:"header";s:13:"Ship-To State";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"delivery_address_county";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_State";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380793897018_18";a:10:{s:6:"header";s:9:"Confirmed";s:4:"type";s:7:"options";s:7:"options";s:50:"supplierconnect/config_source_confirmstatusoptions";s:5:"index";s:15:"order_confirmed";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"text";s:8:"renderer";s:68:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Confirmed";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';

        $writeConnection = $setup->getConnection('core_write');
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'supplierconnect_enabled_messages/SPOS_request/grid_config',
            'value' => $value,
        ];
        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'), $data, ['value']
        );


        $value2 = 'a:9:{s:18:"_1380794775648_648";a:9:{s:6:"header";s:7:"Confirm";s:4:"type";s:4:"text";s:5:"index";s:14:"new_po_confirm";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:68:"Epicor_Supplierconnect_Block_Customer_Orders_Create_Renderer_Confirm";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794798405_405";a:9:{s:6:"header";s:6:"Reject";s:4:"type";s:4:"text";s:5:"index";s:13:"new_po_reject";s:9:"filter_by";s:4:"none";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:67:"Epicor_Supplierconnect_Block_Customer_Orders_Create_Renderer_Reject";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794798930_930";a:9:{s:6:"header";s:13:"Our PO Number";s:4:"type";s:4:"text";s:5:"index";s:21:"purchase_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:67:"Epicor_Supplierconnect_Block_Customer_Orders_Create_Renderer_Linkpo";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794799626_626";a:9:{s:6:"header";s:10:"Order Date";s:4:"type";s:4:"date";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794800354_354";a:9:{s:6:"header";s:12:"Ship-To Name";s:4:"type";s:4:"text";s:5:"index";s:21:"delivery_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1380794801098_98";a:9:{s:6:"header";s:15:"Ship-To Address";s:4:"type";s:4:"text";s:5:"index";s:23:"delivery_address_street";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794801650_650";a:9:{s:6:"header";s:12:"Ship-To City";s:4:"type";s:4:"text";s:5:"index";s:21:"delivery_address_city";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794802290_290";a:9:{s:6:"header";s:13:"Ship-To State";s:4:"type";s:4:"text";s:5:"index";s:23:"delivery_address_county";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:64:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_State";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380794802866_866";a:9:{s:6:"header";s:6:"Status";s:4:"type";s:4:"text";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:70:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Orderstatus";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';


        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'supplierconnect_enabled_messages/SPOS_request/newpogrid_config',
            'value' => $value2,
        ];
        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'), $data, ['value']
        );

    }
    private function version_1_0_6_3_8($setup)
    {

        $value = 'a:8:{s:18:"_1381758946179_179";a:10:{s:6:"header";s:9:"PO Number";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"purchase_order_number";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758948320_320";a:10:{s:6:"header";s:10:"Order Date";s:4:"type";s:4:"date";s:7:"options";s:0:"";s:5:"index";s:10:"order_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758948821_821";a:10:{s:6:"header";s:6:"Status";s:4:"type";s:7:"options";s:7:"options";s:48:"supplierconnect/config_source_orderstatusoptions";s:5:"index";s:12:"order_status";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758949389_389";a:10:{s:6:"header";s:12:"Ship-To Name";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"delivery_address_name";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758949981_981";a:10:{s:6:"header";s:15:"Ship-To Address";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"delivery_address_street";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758950582_582";a:10:{s:6:"header";s:12:"Ship-To City";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:21:"delivery_address_city";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1381758952319_319";a:10:{s:6:"header";s:13:"Ship-To State";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:23:"delivery_address_county";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:17:"_1381758953013_13";a:10:{s:6:"header";s:9:"Confirmed";s:4:"type";s:4:"text";s:7:"options";s:0:"";s:5:"index";s:15:"order_confirmed";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:68:"Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Confirmed";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';

        $writeConnection = $setup->getConnection('core_write');
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'supplierconnect_enabled_messages/SPCS_request/grid_config',
            'value' => $value,
        ];
        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'), $data, ['value']
        );


        $value2 = 'a:8:{s:18:"_1380792275502_502";a:9:{s:6:"header";s:11:"Part Number";s:4:"type";s:4:"text";s:5:"index";s:12:"product_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792285534_534";a:9:{s:6:"header";s:15:"Cross Reference";s:4:"type";s:4:"text";s:5:"index";s:15:"cross_reference";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792286124_124";a:9:{s:6:"header";s:20:"Cross Reference Type";s:4:"type";s:4:"text";s:5:"index";s:20:"cross_reference_type";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792286613_613";a:9:{s:6:"header";s:14:"Operation Code";s:4:"type";s:4:"text";s:5:"index";s:16:"operational_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792287157_157";a:9:{s:6:"header";s:14:"Effective Date";s:4:"type";s:4:"date";s:5:"index";s:14:"effective_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792287716_716";a:9:{s:6:"header";s:15:"Expiration Date";s:4:"type";s:4:"date";s:5:"index";s:15:"expiration_date";s:9:"filter_by";s:3:"erp";s:9:"condition";s:7:"LTE/GTE";s:9:"sort_type";s:4:"date";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792288220_220";a:9:{s:6:"header";s:15:"Base Unit Price";s:4:"type";s:4:"text";s:5:"index";s:5:"price";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:6:"number";s:8:"renderer";s:51:"Epicor_Supplierconnect_Block_List_Renderer_Currency";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}s:18:"_1380792288837_837";a:9:{s:6:"header";s:3:"UOM";s:4:"type";s:4:"text";s:5:"index";s:20:"unit_of_measure_code";s:9:"filter_by";s:3:"erp";s:9:"condition";s:2:"EQ";s:9:"sort_type";s:4:"text";s:8:"renderer";s:0:"";s:7:"visible";s:1:"1";s:10:"showfilter";s:1:"1";}}';


        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'supplierconnect_enabled_messages/SPLS_request/grid_config',
            'value' => $value2,
        ];
        $writeConnection->insertOnDuplicate(
            $setup->getTable('core_config_data'), $data, ['value']
        );

    }
}