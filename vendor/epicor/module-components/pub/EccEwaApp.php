<?php
/**
 * ECC EWA Application
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Event;
use Magento\Framework\Filesystem;

class EccEwaApp
    extends \Magento\Framework\App\Http
    implements \Magento\Framework\AppInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productObj;
    
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObj;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;
    
    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $configuratorHelper;

    /**
     * @var \Epicor\Comm\Model\Message\Request\CimFactory
     */
    protected $cim;
    
    /**
     * @var \Epicor\Comm\Helper\MessagingFactory
     */
    protected $commMessagingHelper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Event\Manager $eventManager
     * @param AreaList $areaList
     * @param RequestHttp $request
     * @param ResponseHttp $response
     * @param ConfigLoaderInterface $configLoader
     * @param State $state
     * @param Filesystem $filesystem,
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Event\Manager $eventManager,
        \Magento\Framework\App\AreaList $areaList,
        RequestHttp $request,
        ResponseHttp $response,
        ConfigLoaderInterface $configLoader,
        \Magento\Framework\App\State $state,
        Filesystem $filesystem,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productObj,
        \Magento\Framework\DataObjectFactory $dataObj,
        \Magento\Framework\View\LayoutInterface $layout,
        \Epicor\Comm\Helper\ConfiguratorFactory $configuratorHelper,
        \Epicor\Comm\Model\Message\Request\CimFactory $cim,
        \Epicor\Comm\Helper\MessagingFactory $commMessagingHelper
    )
    {
        $state->setAreaCode('frontend');
        $this->storeManager = $storeManager;
        $this->productObj = $productObj;
        $this->dataObj = $dataObj;
        $this->layout = $layout;
        $this->configuratorHelper = $configuratorHelper;
        $this->cim = $cim;
        $this->commMessagingHelper = $commMessagingHelper;
        
        $objectManager->configure($configLoader->load('frontend'));
        
        parent::__construct(
            $objectManager,
            $eventManager,
            $areaList,
            $request,
            $response,
            $configLoader,
            $state,
            $filesystem,
            $registry
        );
    }

    public function launch()
    {
        $this->processEwa();
        return $this->_response;
    }

    public function processEwa()
    {
        $helper = $this->configuratorHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Configurator */

        $action = $this->_request->getParam('action');
        $currentStoreId = $this->_request->getParam('currentStoreId');
        $productId = $this->_request->getParam('productId');
        $return = $this->_request->getParam('return');
        $quoteId = $this->_request->getParam('quoteId');
        $address = $this->_request->getParam('address');

        $storeId = $this->storeManager->getStore($currentStoreId)->getStoreId();
        $resourceProduct = $this->productObj->create()->getResource();
        /* @var $resourceProduct \Epicor\Comm\Model\ResourceModel\Product */
        $skuData = $resourceProduct->getAttributeRawValue($productId, 'sku', $storeId);
        $uom = $resourceProduct->getAttributeRawValue($productId, 'ecc_uom', $storeId);

        $sku = isset($skuData['sku']) ? $skuData['sku'] : '';
        
        $cimData = [
            'product_sku' => $sku,
            'product_uom' => $uom,
            'quote_id' => !empty($quoteId) ? $quoteId : null,
            'line_number' => $this->_request->getParam('lineNumber'),
            'delivery_address' => $helper->getDeliveryAddressFromRFQ($address),
            'ewa_code' => $this->_request->getParam('ewaCode'),
            'group_sequence' => $this->_request->getParam('groupSequence'),
            'item_id' => $this->_request->getParam('itemId')
        ];

        $this->sendCim($cimData);
        $this->showBlock();
        // $location = $request->getParam('location');
        // $registry->register('location_code', $location);
    }
    
    public function showBlock() {
        $ewaBlock =  $this->layout->createBlock('Epicor\Comm\Block\Catalog\Product\Ewa');
        /* @var $ewaBlock Epicor_Comm_Block_Catalog_Product_Ewa */
        $this->_response->setBody($ewaBlock->toHtml());
    }

    public function sendCim($cimData)
    {
        $helper = $this->commMessagingHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Messaging */
        $response = $helper->sendErpMessage('epicor_comm', 'cim', $cimData);
        $cim = $response['message'];
        /* @var $cim \Epicor\Comm\Model\Message\Request\Cim */

        if ($cim->isSuccessfulStatusCode()) {
            $this->registry->register('EWAData', $cim->getResponse()->getConfigurator());
            $this->registry->register('EWASku', $cim->getProductSku());
            $this->registry->register('CIMData', $this->dataObj->create(['data' => $cimData]));
        }
    }

    public function catchException(
    \Magento\Framework\App\Bootstrap $bootstrap,
    \Exception $exception
    )
    {
        return false;
    }

}
