<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines;

use Magento\Framework\DataObjectFactory as DataObject;
use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;

/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{
    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_invoices_misc';
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
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    private $hidePrice;

    private $dataObjectFactory;

    protected $customerconnectHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    private $contract;

    public function __construct(
        HidePrice $hidePrice,
        DataObject $dataObjectFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Lists\Helper\Frontend\Contract $contract,
        array $data = []
    ) {
        $this->hidePrice = $hidePrice;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commLocationsHelper = $commLocationsHelper;
        $this->eventManager = $eventManager;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        $this->contract = $contract;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('customerconnect_invoices_lines');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('customerconnect');
        $this->setMessageType('cuid');
        $this->setIdColumn('invoice_number');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $invoice = $this->registry->registry('customer_connect_invoices_details');
        if ($invoice) {
            $lines = ($invoice->getLines()) ? $invoice->getLines()->getasarrayLine() : array();

            if (!empty($lines)) {
                foreach ($lines as $line) {
                    if ($line->getAttachments()) {
						$lineAttachments = $line->getAttachments();
						$attachmentsData = ($lineAttachments) ? $lineAttachments->getasarrayAttachment() : array();
                        $line->setAttachments($this->getAttachmentDetails($attachmentsData));
                    }

                    if ($line->getQuantities()) {
                        $delivered = $line->getQuantities()->getDelivered();
                        $toFollow = $line->getQuantities()->getToFollow();
                        $line->setQuantities($line->getQuantities()->getOrdered());
                        $line->setDelivered($delivered);
                        $line->setToFollow($toFollow);
                    } else {
                        $line->setQuantities(0);
                        $line->setDelivered(0);
                        $line->setToFollow(0);
                    }
                    $line->setMiscLineTotal($line->getLineValue() + $line->getMiscellaneousChargesTotal());
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
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer\Expand',
                'filter' => false
            ),
            'quantities' => array(
                'header' => __('Quantities'),
                'align' => 'left',
                'index' => 'quantities',
                'type' => 'number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer\Quantities'
            ),
            'delivered' => array(
                'header' => __('Delivered'),
                'align' => 'left',
                'index' => 'delivered',
                'type' => 'number',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display'
            ),
            'to_follow' => array(
                'header' => __('To Follow'),
                'align' => 'left',
                'index' => 'to_follow',
                'type' => 'number',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display'
            ),
            'location' => array(
                'header' => __('Location'),
                'align' => 'left',
                'index' => 'location_code',
                'type' => 'text',
                'filter' => false,
                'renderer' => 'Epicor\Customerconnect\Block\Listing\Renderer\Location',
            ),
            'unit_of_measure_description' => array(
                'header' => __('UOM'),
                'align' => 'left',
                'index' => 'unit_of_measure_description',
                'type' => 'text'
            ),
            'product_code' => array(
                'header' => __('Part Number'),
                'align' => 'left',
                'index' => 'product_code'
            ),
            'description' => array(
                'header' => __('Description'),
                'align' => 'left',
                'index' => 'description',
                'type' => 'text'
            ),
            'shipping_date' => array(
                'header' => __('Shipping Date'),
                'align' => 'left',
                'column_css_class' => 'shipping_date',
                'index' => 'shipping_date',
                'type' => 'date'
            ),
            'packing_slip' => array(
                'header' => __('Packing Slip'),
                'align' => 'left',
                'index' => 'packing_slip',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer\Packingslip',
                'type' => 'text'
            ),
            'contract_code' => array(
                'header' => __('Contract'),
                'align' => 'left',
                'index' => 'contract_code',
                'type' => 'text',
                'renderer' => 'Epicor\Customerconnect\Block\Listing\Renderer\ContractCode',
            ),
            'price' => array(
                'header' => __('Unit Price'),
                'align' => 'left',
                'column_css_class' => 'a-right',
                'index' => 'price',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer\Currency',
                'type' => 'number'
            ),
            'miscellaneous_charges_total' => array(
                'header' => __('Misc.'),
                'align' => 'left',
                'index' => 'miscellaneous_charges_total',
                'type' => 'number',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer\Currency',
                'filter' => false
            ),
            'line_value' => array(
                'header' => __('Line Value'),
                'align' => 'left',
                'column_css_class' => 'a-right',
                'index' => 'line_value',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer\Currency',
                'type' => 'number'
            ),
            'attachments' => array(
                'header' => __('Attachments'),
                'index' => 'attachments',
                'type' => 'text',
                'filter' => false,
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer\Attachments'
            ),
            'misc' => array(
                'header' => __(''),
                'align' => 'left',
                'index' => 'miscellaneous_charges',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer\MiscCharges',
                'type' => 'text',
                'filter' => false,
                'keep_data_format' => 1,
                'column_css_class' => "expand-content",
                'header_css_class' => "expand-content"
            )

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
        /* @var $locHelper Epicor_Comm_Helper_Locations */
        $showLoc = ($locHelper->isLocationsEnabled()) ? $locHelper->showIn('cc_invoices') : false;

        if (!$showLoc) {
            unset($columns['location']);
        }
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);
        $this->eventManager->dispatch('epicor_customerconnect_cuod_grid_columns_after', array(
                'block' => $this,
                'columns' => $columnObject,
                'type' => 'invoice'
            )
        );
        if ($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1, 2, 3])) {
            $columnsToHide = ['line_value', 'price', 'miscellaneous_charges_total'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }
        return $columnObject->getData();
    }

    public function getRowUrl($row)
    {
        return null;
    }

    public function setCollection($collection)
    {
        if ($this->canShowMisc()) {
            $invoiceFilter = [
                'miscCharge' => 'Y'
            ];
        } else {
            $invoiceFilter = [];
        }
        $collection->setRowFilters($invoiceFilter);
        $this->_collection = $collection;
    }

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ);
        return $showMiscCharges && $isMiscAllowed;
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