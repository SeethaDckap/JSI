<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Search\Listing;


/**
 * Inventory list Grid config
 * 
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Dealerconnect\Block\Portal\Inventory\Search\Listing\Search
{
    const FRONTEND_RESOURCE_DETAIL = 'Dealer_Connect::dealer_inventory_details';
    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Customerconnect\Block\Customer\Dashboard\Orders\Renderer\ReorderFactory
     */
    protected $customerconnectCustomerDashboardOrdersRendererReorderFactory;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
    
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
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    )
    {
        $this->customerconnectCustomerDashboardOrdersRendererReorderFactory = $customerconnectCustomerDashboardOrdersRendererReorderFactory;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $data
        );

        $this->setFooterPagerVisibility(false);
        $this->setId('dealerconnect_inventory');
        $this->setDefaultSort('identification_number');
        $this->setDefaultDir('asc');
        $this->setMessageBase('dealerconnect');
        $this->setMessageType('deis');
        $this->setIdColumn('identification_number');
        $this->initColumns();
        $this->setExportTypeCsv(false);
        $this->setCacheDisabled(false); 
        $this->setExportTypeXml(false);
    }

    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            $erp_account_number = $helper->getErpAccountNumber();
            $order_requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $row->getLocationNumber()));
            $url = $this->getUrl('*/*/details', array('location' => $order_requested));
        }
        return $url;
    }    
    
    protected function initColumns()
    {
        parent::initColumns();
        
        $columns = $this->getCustomColumns();
        if(isset($columns['warranty_code']) && is_array($columns['warranty_code'])){
            $column =  $columns['warranty_code'];
            $column['renderer'] = 'Epicor\Dealerconnect\Block\Portal\Inventory\Search\Listing\Renderer\WarrentyCode';
            $columns['warranty_code']  =  $column;
            $this->setCustomColumns($columns);
        }
        
        if (isset($columns['listing']) && is_array($columns['listing'])) {
            $column =  $columns['listing'];
            $column['renderer'] = 'Epicor\Dealerconnect\Block\Portal\Inventory\Search\Listing\Renderer\Listing';
            $columns['listing']  =  $column;
            $this->setCustomColumns($columns);
        } 
    }
}