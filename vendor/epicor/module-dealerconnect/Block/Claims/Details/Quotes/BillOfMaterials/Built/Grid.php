<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes\BillOfMaterials\Built;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Built\Grid
{
    /**
     * Columns not needed for Claim Quote Bill of Materials grid
     * @var array
     */
    public $_ignoreColumns = [
        'reorder',
        'action'
    ];
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Dealerconnect\Model\Message\Request\Inventory\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Customerconnect\Block\Customer\Dashboard\Orders\Renderer\ReorderFactory $customerconnectCustomerDashboardOrdersRendererReorderFactory,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $commonAccessHelper,
            $customerconnectHelper,
            $listsFrontendContractHelper,
            $customerconnectCustomerDashboardOrdersRendererReorderFactory,
            $urlEncoder,
            $registry,
            $encryptor,
            $dealerHelper,
            $data
        );
        $this->setUseAjax(true);
        $this->setParentContainerId('claim_bom');
        $this->setTemplate('Epicor_Dealerconnect::claims/details/quotes/billofmaterials/grid/extended.phtml');
    }
    
    protected function initColumns()
    {
        parent::initColumns();
        $customColumns = $this->getCustomColumns();
        if (is_array($customColumns)) {
            foreach ($customColumns as $columnId => $column) {
                if (in_array($column['index'], $this->_ignoreColumns)) {
                    unset($customColumns[$columnId]);
                }
            }
        }
        $addToQuoteColumn = [
            'header'        => __('Add to Quote'),
            'type'          => 'text',
            'index'         => 'add_to_quote',
            'filter_by'     => 'linq',
            'condition'     => 'EQ',
            'sort_type'     => 'text',
            'showfilter'    => 0,
            'visible'       => 1,
            'renderer'      => '\Epicor\Dealerconnect\Block\Claims\Details\Quotes\BillOfMaterials\Renderer\AddToQuote'
        ];
        $customColumns['add_to_quote'] = $addToQuoteColumn;
        $this->setCustomColumns($customColumns);
    }
    
    public function getDmauForm()
    {
        return;
    }
    
    public function isbomReplaceAllowed()
    {
        return false;
    }
}