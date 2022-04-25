<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\DataProviders\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory;
use Epicor\Comm\Model\ResourceModel\Location\Product\Collection;
use Epicor\Comm\Model\Location\Product;

/**
 * Class ProductlocationsDataProvider
 *
 * @method Collection getCollection
 */
class ProductlocationsDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
       
        $collection = $this->getCollection();

        //$collection->getProductSelect();
        
        $collection->addFieldToFilter('main_table.product_id', $this->request->getParam('current_product_id', 0));
        $collection->joinLocationInfo();
        //$collection->joinExtraProductInfo($this->request->getParam('store_id', 0));
        $arrItems = [
            'totalRecords' => $collection->getSize(),
            'items' => [],
        ];
        foreach ($collection as $item) {
            $arrItems['items'][] = $item->toArray([]);
        }
//print_r($arrItems); exit;
        return $arrItems;
    }

    
}
