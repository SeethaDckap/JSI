<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Built;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Built\Search
{

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
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    public $dealerHelper;

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
        $this->customerconnectCustomerDashboardOrdersRendererReorderFactory = $customerconnectCustomerDashboardOrdersRendererReorderFactory;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $dealerHelper,
            $data
        );

        $this->setFooterPagerVisibility(false);
        $this->setId('dealerconnect_bom_built');
        $this->setDefaultSort('product_code');
        $this->setDefaultDir('asc');
        $this->setMessageBase('dealerconnect');
        $this->setMessageType('debm');
        $this->setIdColumn('identification_number');
        $this->initColumns();
        $this->setExportTypeCsv(false);
        $this->setCacheDisabled(false); 
        $this->setExportTypeXml(false);
    } 
    
    public function getInfoRow()
    {
        $html = '<tr>';
        $uniqueId = $this->registry->registry('unique_id');
        $block = $this->getLayout()->createBlock('Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Generic\Listing\Info');
        $html .= $block->setData('cols_span', sizeof($this->getColumns()))->setData('unique_id', $uniqueId)->toHtml();
        $html .= '</tr>';
        return $html;
    }
    
    public function getRowUrl($row)
    {
        return null;
    }
    
    public function getDmauForm()
    {
        $html = '<tr>';
        $uniqueId = $this->registry->registry('unique_id');
        $block = $this->getLayout()->createBlock('Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Generic\Listing\Form');
        $html .= $block->setData('cols_span', sizeof($this->getColumns()))->setData('unique_id', $uniqueId)->setData('grid_id', $this->getId())->toHtml();
        $html .= '</tr>';
        return $html;
    }

}