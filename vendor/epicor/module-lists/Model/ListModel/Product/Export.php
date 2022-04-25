<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model\ListModel\Product;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory as ListProductCollectionFactory;
use Magento\Framework\Filesystem\File\WriteInterface;
use Epicor\Lists\Helper\Messaging\Customer;

/**
 * Class Export
 */
class Export extends \Epicor\Lists\Model\Export
{

    /**
     * Additional path to folder
     *
     * @var string
     */
    private $csvFileName = 'ProductList';

    /**
     * Header.
     *
     * @var array
     */
    private $header = [
        'SKU',
        'UOM',
        'Currency',
        'Price',
        'Break Qty',
        'Break Price',
        'Description',
    ];

    /**
     * Product Collection
     *
     * @var ProductCollectionFactory
     */
    private $catalogResourceModelProductCollectionFactory;

    /**
     * ListProductCollectionFactory
     *
     * @var ListProductCollectionFactory
     */
    private $listsResourceListModelProductCollectionFactory;


    /**
     * Lise Customer Helper.
     *
     * @var Customer
     */
    private $listsMessagingCustomerHelper;

    /**
     * Export constructor.
     *
     * @param Context                      $context                                        Context.
     * @param Registry                     $registry                                       Registry.
     * @param Filesystem                   $filesystem                                     Filesystem.
     * @param ProductCollectionFactory     $catalogResourceModelProductCollectionFactory   ProductCollectionFactory.
     * @param ListProductCollectionFactory $listsResourceListModelProductCollectionFactory ListProductCollectionFactory.
     * @param Customer                     $listsMessagingCustomerHelper                   ListsMessagingCustomerHelper.
     * @param AbstractResource|null        $resource                                       AbstractResource.
     * @param AbstractDb|null              $resourceCollection                             AbstractDb.
     * @param array                        $data                                           Data.
     *
     * @throws \Magento\Framework\Exception\FileSystemException FileSystemException.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Filesystem $filesystem,
        ProductCollectionFactory $catalogResourceModelProductCollectionFactory,
        ListProductCollectionFactory $listsResourceListModelProductCollectionFactory,
        Customer $listsMessagingCustomerHelper,
        AbstractResource $resource=null,
        AbstractDb $resourceCollection=null,
        array $data=[]
    ) {
        $this->catalogResourceModelProductCollectionFactory   = $catalogResourceModelProductCollectionFactory;
        $this->listsResourceListModelProductCollectionFactory = $listsResourceListModelProductCollectionFactory;
        $this->listsMessagingCustomerHelper                   = $listsMessagingCustomerHelper;
        parent::__construct(
            $context,
            $registry,
            $filesystem,
            $resource,
            $resourceCollection
        );

    }//end __construct()


    /**
     * Set Export.
     *
     * @param string $listId ListId.
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException FileSystemException.
     */
    public function setProductListExport(string $listId)
    {
        // CSV Header.
        $this->setHeader($this->header);

        // Csv file name.
        $this->setFileName($this->csvFileName);

        // Initialize  CSV.
        $stream = $this->initCsvFile();
        /* @var $stream WriteInterface */

        // Process Data.
        $this->processCsvData($listId, $stream);

        // Close CSV.
        $this->closeCsv($stream);

        return [
            'type'  => 'filename',
            'value' => $this->filePath,
            'rm'    => true,  // Can delete temp file after use.
        ];

    }//end setProductListExport()


    /**
     * ProcessCsvData.
     *
     * @param string         $listId ListId.
     * @param WriteInterface $stream WriteInterface.
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException FileSystemException.
     */
    private function processCsvData(string $listId, WriteInterface $stream)
    {
        $products = $this->getProduct($listId);
        foreach ($products as $product) {
            $itemData        = [
                'SKU'         => '',
                'UOM'         => '',
                'Currency'    => '',
                'Price'       => '',
                'Break_Qty'   => '',
                'Break_Price' => '',
                'Description' => '',
            ];
            $itemData['SKU'] = $this->removeSeparator($product['sku']);
            $itemData['UOM'] = ($product['type_id'] !== 'grouped') ? $product['ecc_uom'] : '';

            $this->processPrice($product['id'], $itemData, $stream);
        }

    }//end processCsvData()


    /**
     * Csv download file name.
     *
     * @return string
     */
    public function getCsvFileName()
    {
        return $this->csvFileName;

    }//end getCsvFileName()


    /**
     * Add price data to csv.
     *
     * @param string         $productId ProductId.
     * @param array          $itemData  ItemData.
     * @param WriteInterface $stream    WriteInterface.
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException FileSystemException.
     */
    private function processPrice(string $productId, array $itemData, WriteInterface $stream)
    {
        $prices = $this->getPrice($productId);
        if (count($prices) > 0) {
            foreach ($prices as $price) {
                $priceData             = $itemData;
                $priceData['Currency'] = ($price['currency']) ?: '';
                $priceData['Price']    = ($price['price']) ?: '';
                $breaks                = unserialize($price['price_breaks']);
                if ($breaks) {
                    foreach ($breaks as $break) {
                        $data                = $priceData;
                        $data['Break_Qty']   = ($break['qty']) ?: '';
                        $data['Break_Price'] = ($break['price']) ?: '';
                        $data['Description'] = (trim($break['description'])) ?: '';
                        $this->writeCsv($stream, $data);
                    }
                } else {
                    $this->writeCsv($stream, $priceData);
                }
            }
        } else {
            $this->writeCsv($stream, $itemData);
        }//end if

    }//end processPrice()


    /**
     * Get PL Product Data.
     *
     * @param string $listId ListId.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getProduct(string $listId)
    {
        $collection = $this->catalogResourceModelProductCollectionFactory->create();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */

        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('ecc_uom');
        $collection->addAttributeToSelect('ecc_configurator');
        $collection->addAttributeToSelect('list_position');
        $collection->getSelect()->join(
            ['lp' => $collection->getTable('ecc_list_product')],
            'e.sku = lp.sku AND lp.list_id = "'.$listId.'"',
            [
                'id',
                'list_id',
                'qty',
                'location_code',
                'list_position'
            ]
        );

        return $collection;

    }//end getProduct()


    /**
     * GetPrice.
     *
     * @param string $productId ProductId.
     *
     * @return \Epicor\Lists\Model\ResourceModel\ListModel\Product\Collection
     */
    private function getPrice(string $productId)
    {
        $listProduct = $this->listsResourceListModelProductCollectionFactory->create();
        /* @var $listProduct \Epicor\Lists\Model\ResourceModel\ListModel\Product\Collection Collection. */

        $listProduct->getSelect()->joinLeft(
            ['price' => $listProduct->getTable('ecc_list_product_price')],
            'main_table.id = price.list_product_id'
        );
        $listProduct->addFieldToFilter('price.list_product_id', $productId);
        return $listProduct;

    }//end getPrice()

    /**
     * RemoveSeparator.
     *
     * @param string $fullSku Sku.
     *
     * @return string
     */
    private function removeSeparator($fullSku)
    {
        $delimiter = $this->listsMessagingCustomerHelper->getUOMSeparator();
        $sku = explode($delimiter, $fullSku);

        return isset($sku[0]) ? $sku[0] : $fullSku;

    }//end removeSeparator()


}
