<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Cart\Contract\Select;

/**
 * Contract select page grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid {

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging\Customer
     */
    protected $commMessagingCustomerHelper;

    /**
     * @var \Epicor\Lists\Block\Cart\Contract\Select\Renderer\PriceFactory
     */
    protected $listsCartContractSelectRendererPriceFactory;

    /**
     * @var \Epicor\Lists\Block\Cart\Contract\Select\Renderer\SelectFactory
     */
    protected $listsCartContractSelectRendererSelectFactory;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
            \Magento\Backend\Helper\Data $backendHelper,
            \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
            \Epicor\Common\Helper\Data $commonHelper,
            \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
            \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
            \Epicor\Comm\Helper\Messaging $commMessagingHelper,
            \Magento\Framework\Registry $registry, \Epicor\Comm\Helper\Messaging\Customer $commMessagingCustomerHelper,
            \Epicor\Lists\Block\Cart\Contract\Select\Renderer\PriceFactory $listsCartContractSelectRendererPriceFactory,
            \Epicor\Lists\Block\Cart\Contract\Select\Renderer\SelectFactory $listsCartContractSelectRendererSelectFactory,
             \Magento\Framework\DataObjectFactory $dataObjectFactory,
            array $data = []
    ) {
        $this->listsCartContractSelectRendererPriceFactory = $listsCartContractSelectRendererPriceFactory;
        $this->listsCartContractSelectRendererSelectFactory = $listsCartContractSelectRendererSelectFactory;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->registry = $registry;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data
        );

        $this->setId('selectlinecontractgrid');
        $this->setDefaultSort('title');
        $this->setDefaultDir('ASC');

        $this->setSaveParametersInSession(false);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('epicor_common');
        $this->setIdColumn('id');

        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
        $this->setCacheDisabled(true);
        $this->setShowAll(false);
        $this->setUseAjax(true);
        $this->setSkipGenerateContent(true);

        $this->setCustomData($this->getContractData());

        $this->_emptyText = __('No Contracts available');
    }

    /**
     * Sorts out contract data to be displayed
     *
     * @return array
     */
    protected function getContractData() {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        $messageHelper = $this->commMessagingHelper;
        /* @var $messageHelper Epicor_Comm_Helper_Messaging */
        $item = $this->registry->registry('ecc_line_contract_item');
        /* @var $item Epicor_Comm_Model_Quote_Item */
        $product = $item->getProduct();

        $contractData = $helper->getContractsForCartItem($item);
        $productContracts = array();
        $contracts = array();
        foreach ($contractData as $contract) {
            /* @var $contract Epicor_Lists_Model_ListModel */
            $productContracts[] = $contract->getErpCode();
            $contracts[$contract->getErpCode()] = $contract;
        }

        $product->setQty(1);
        $product->setMsqQty(1);
        $product->setEccContracts($productContracts);

        $products = array($product);

        $functions = array(
            'setTrigger' => array('custom_msq_send'),
            'addProducts' => array($products),
        );

        $messageHelper->sendErpMessage('epicor_comm', 'msq', array(), array(), $functions);

        $msqData = (array) $product->getEccMsqContractData();

        foreach ($msqData as $erpContract) {
            if ($erpContract) {
                if(is_array($erpContract)){
                    $contract = isset($contracts[$erpContract['contractCode']]) ? $contracts[$erpContract['contractCode']] : false;
                    if ($contract) {
                       (isset($erpContract['maximumContractQty'])) ? $contract->setQuantity($erpContract['maximumContractQty']) : false;
                       (isset($erpContract['customerPrice'])) ? $contract->setPrice($erpContract['customerPrice']) : false;
                        $breaks =  $this->_getGroupedDataArray('breaks', 'break', $erpContract);
                        $contract->setPriceBreaks($breaks);
                       
                        $contracts[$erpContract['contractCode']] = $contract;
                    }
                   
                }else{
                    $contract = isset($contracts[$erpContract->getContractCode()]) ? $contracts[$erpContract->getContractCode()] : false;
                    if ($contract) {
                        $contract->setQuantity($erpContract->getMaximumContractQty());
                        $contract->setPrice($erpContract->getCustomerPrice());
                        $breaks = $erpContract->getBreaks() ? $erpContract->getBreaks()->getasarrayBreak() : array();
                        $contract->setPriceBreaks($breaks);
                        $contracts[$erpContract->getContractCode()] = $contract;
                    }
                }
            }
        }

        return $contracts;
    }
    
    protected function _getGroupedDataArray($groupRef, $childRef, $erpData)
    {
        $groupRef =( isset($erpData[$groupRef]) ) ? $erpData[$groupRef]:false;
       // $childRef = 'getasarray' . ucfirst($this->getHelper()->convertStringToCamelCase($childRef));
        $group = $groupRef;
        //$result = $group && is_array($group) ? $group[$childRef] : array();
        if($group && is_array($group)){
            if(isset($group[$childRef])){
                return (isset($group[$childRef][0])) ? $group[$childRef]: [$group[$childRef]];
            }else{
                return [];
            }
            
        }else{
            return [];            
        }
    }

    protected function _toHtml() {
        $html = '';
        $item = $this->registry->registry('ecc_line_contract_item');
        $uomDelimiter = $this->commMessagingCustomerHelper->getUOMSeparator();
        $sku = implode(' ', explode($uomDelimiter, $item->getSku()));

        if ($this->getRequest()->getParam('noheader') == false) {
            $html .= '<h2 id="line-contract-select-header">' . __("Select Line Contract") . '</h2>';
            $html .= '<button id="line-select-close-popup" title="' . __('Close Popup') . '" type="button" class="scalable " onclick="lineContractSelect.closepopup()" style=""><span><span>' . __('Close Popup') . '</span></span></button>';
            $html .= '<span id="line-contract-select-width" style="">';
            $html .= '<span id="line-contract-select-product">' . __("Product: {$sku}") . '</span>';
            $html .= '</span>';
        }
        $html .= parent::_toHtml(true);

        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */
        if ($helper->requiredContractType() == 'O' && $this->getRequest()->getParam('noheader') == false) {
            /* @var $item Epicor_Comm_Model_Quote_Item */
            $url = $this->getUrl('epicor_lists/cart/applycontractselect', array('itemid' => $item->getId(), 'contract' => 0));
            $html .= '<button id="line-select-no-contract" title="' . __('No Contract') . '" type="button" class="scalable" onclick="javascript:window.location=\'' . $url . '\'"><span><span>' . __('Continue With No Contract') . '</span></span></button>';
        }

        return $html;
    }

    public function getRowUrl($row) {
        return false;
    }

    /**
     * Build columns for List Contract
     *
     *
     */
    protected function _getColumns() {
        $columns = array(
            'title' => array(
                'header' => __('Title'),
                'index' => 'title',
                'filter_index' => 'title',
                'sortable' => false,
                'type' => 'text'
            ),
            'erp_code' => array(
                'header' => __('Description'),
                'index' => 'description',
                'filter_index' => 'description',
                'sortable' => false,
                'type' => 'text'
            ),
            'price' => array(
                'header' => __('Price'),
                'index' => 'price',
                'filter_index' => 'price',
                'type' => 'number',
                'width' => 300,
                'sortable' => false,
                'renderer' => '\Epicor\Lists\Block\Cart\Contract\Select\Renderer\Price'
            ),
            'quantity' => array(
                'header' => __('Quantity'),
                'index' => 'quantity',
                'filter_index' => 'quantity',
                'type' => 'text',
                'align' => 'center',
                'width' => 150,
                'sortable' => false,
            ),
            'end_date' => array(
                'header' => __('End Date'),
                'index' => 'end_date',
                'filter_index' => 'end_date',
                'type' => 'datetime',
                'sortable' => false,
            ),
            'select' => array(
                'header' => __('Select'),
                'index' => 'id',
                'type' => 'text',
                'filter' => false,
                'sortable' => false,
                'width' => 150,
                'renderer' => '\Epicor\Lists\Block\Cart\Contract\Select\Renderer\Select'
            )
        );

        return $columns;
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl() {
        $item = $this->registry->registry('ecc_line_contract_item');
        /* @var $item Epicor_Comm_Model_Quote_Item */
        return $this->getUrl('*/*/contractselectgrid', array('itemid' => $item->getId(), 'noheader' => 1));
    }

}
