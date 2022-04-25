<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Orders\Details\Parts;

use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;

/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    private $hidePrice;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    private $contract;

    public function __construct(
        HidePrice $hidePrice,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Lists\Helper\Frontend\Contract $contract,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commLocationsHelper = $commLocationsHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $context->getEventManager();
        $this->hidePrice = $hidePrice;
        $this->contract = $contract;

        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('customerconnect_order_parts');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('customerconnect');
        $this->setMessageType('cuod');
        $this->setIdColumn('product_code');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $order = $this->registry->registry('customer_connect_order_details');
        if ($order) {
            /* @var $order \Epicor\Common\Model\Xmlvarien */
            $lines = ($order->getLines()) ? $order->getLines()->getasarrayLine() : array();
            if (!empty($lines)) {
                foreach ($lines as $line) {
					
                    if ($line->getAttachments()) {
						$lineAttachments = $line->getAttachments();
						$attachmentsData = ($lineAttachments) ? $lineAttachments->getasarrayAttachment() : array();
                        $line->setAttachments($this->getAttachmentDetails($attachmentsData));
                    }

                    $line->setQty($line->getQuantity() ? $line->getQuantity()->getOrdered() : 0);
                    $line->setMiscLineTotal($line->getLineValue() + $line->getMiscellaneousChargesTotal());
                    $dealerVal = $line->getDealer() ? $line->getDealer()->getLineValueInc() : 0;
                    $line->setMiscLineTotalD($dealerVal + $line->getMiscellaneousChargesTotal());
                }
            }

            $this->setCustomData((array)$lines);
        }
    }

    protected function _getColumns()
    {
        $columns = array(
            'expand' => array(
                'header' => __(''),
                'align' => 'left',
                'index' => 'expand',
                'type' => 'text',
                'column_css_class' => "expand-row",
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer\Expand',
                'filter' => false
            ),
            'is_kit' => array(
                'header' => __('Kit'),
                'align' => 'left',
                'index' => 'is_kit',
                'type' => 'text',
                'filter' => false
            ),
            'product_code' => array(
                'header' => __('Part Number'),
                'align' => 'left',
                'index' => 'product_code',
                'filter' => false
            ),
            'description' => array(
                'header' => __('Description'),
                'align' => 'left',
                'index' => 'description',
                'type' => 'text',
                'filter' => false
            ),
            'additionaltext' => array(
                'header' => __('Additional Info'),
                'align' => 'left',
                'index' => 'additional_text',
                'type' => 'text',
                'filter' => false
            ),
            'contract_code' => array(
                'header' => __('Contract'),
                'align' => 'left',
                'index' => 'contract_code',
                'type' => 'text',
                'renderer' => 'Epicor\Customerconnect\Block\Listing\Renderer\ContractCode',
            ),
            'price' => array(
                'header' => __('Price'),
                'align' => 'left',
                'index' => 'price',
                'type' => 'number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer\Currency',
                'filter' => false
            ),
            'dealer_price' => array(
                'header' => __('Price'),
                'align' => 'left',
                'index' => 'dealer_price_inc',
                'type' => 'number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer\Currency',
                'filter' => false,
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display'
            ),
            'qty' => array(
                'header' => __('Qty'),
                'align' => 'center',
                'index' => 'qty',
                'type' => 'number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer\Qty',
                'filter' => false
            ),
            'unit_of_measure_description' => array(
                'header' => __('UOM'),
                'align' => 'left',
                'index' => 'unit_of_measure_description',
                'type' => 'text',
                'filter' => false
            ),

            'location' => array(
                'header' => __('Location'),
                'align' => 'left',
                'index' => 'location_code',
                'type' => 'text',
                'filter' => false,
                'renderer' => 'Epicor\Customerconnect\Block\Listing\Renderer\Location',
            ),
            'miscellaneous_charges_total' => array(
                'header' => __('Misc.'),
                'align' => 'left',
                'index' => 'miscellaneous_charges_total',
                'type' => 'number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer\Currency',
                'filter' => false
            ),
            'line_value' => array(
                'header' => __('Total Price'),
                'align' => 'left',
                'index' => 'line_value',
                'type' => 'number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer\Currency',
                'filter' => false
            ),
            'attachments' => array(
                'header' => __('Attachments'),
                'index' => 'attachments',
                'type' => 'text',
                'filter' => false,
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer\Attachments'
            ),
            'dealer_line_value' => array(
                'header' => __('Total Price'),
                'align' => 'left',
                'index' => 'dealer_line_value_inc',
                'type' => 'number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer\Currency',
                'filter' => false,
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display'
            ),
            'shipments' => array(
                'header' => __(''),
                'align' => 'left',
                'index' => 'shipments',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer\Shipping',
                'type' => 'text',
                'filter' => false,
                'keep_data_format' => 1,
                'column_css_class' => "expand-content",
                'header_css_class' => "expand-content"
            ),
        );
        //remove column if lists is disabled
        if (!$this->scopeConfig->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            || $this->contract->contractsDisabled()) {
            unset($columns['contract_code']);
        }
        if (!$this->scopeConfig->getValue('customerconnect_enabled_messages/crq_options/allow_misc_charges',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            unset($columns['miscellaneous_charges_total']);
        }

        $locHelper = $this->commLocationsHelper;
        $showLoc = ($locHelper->isLocationsEnabled()) ? $locHelper->showIn('cc_orders') : false;

        if (!$showLoc) {
            unset($columns['location']);
        }
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);
        $this->eventManager->dispatch('epicor_customerconnect_cuod_grid_columns_after', array(
                'block' => $this,
                'columns' => $columnObject
            )
        );
        if ($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1, 2, 3])) {
            $columnsToHide = [
                'line_value',
                'dealer_line_value',
                'dealer_price',
                'price',
                'miscellaneous_charges_total'
            ];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }

        return $columnObject->getData();
    }

    public function getRowUrl($row)
    {
        return null;
    }

    /**
     * @param $attachments
     * @return array
     */
    public function getAttachmentDetails($attachments)
    {
        $lineAttachment = [];
        foreach ($attachments as $k => $value) {
            $attachmentNumber = !empty($value['attachment_number']) ? $value['attachment_number'] : '';
            $attachmentDescription = (!empty($value['description'])) ?$value['description'] : '';
            $fileName = (!empty($value['filename'])) ? $value['filename'] : '';
            $erpFileId = (!empty($value['erp_file_id'])) ? $value['erp_file_id'] : '';

            $lineAttachment[$k]['attachment_number'] = $attachmentNumber;
            $lineAttachment[$k]['description'] = $attachmentDescription;

			$lineAttachment[$k]['erp_file_id']=  $erpFileId;
			$lineAttachment[$k]['filename'] =   $fileName;
        }
        return $lineAttachment;

    }
}