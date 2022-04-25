<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\DataProviders\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory;
use \Epicor\Comm\Model\ResourceModel\Message\Log\Collection;
use Epicor\Comm\Model\Message\Log;

/**
 * Class ProductmessagelogDataProvider
 *
 * @method Collection getCollection
 */
class ProductmessagelogDataProvider extends AbstractDataProvider
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
        
        $collection->addFieldToFilter('message_parent', \Epicor\Comm\Model\Message::MESSAGE_TYPE_UPLOAD);
        $collection->addFieldToFilter('message_category', \Epicor\Comm\Model\Message::MESSAGE_CATEGORY_PRODUCT);
        
        $collection->addFieldToFilter('message_subject', $this->request->getParam('sku', 0));
        $arrItems = [
            'totalRecords' => $collection->getSize(),
            'items' => [],
        ];
        foreach ($collection as $item) {
            $arrItems['items'][] = $item->toArray([]);
        }
        return $arrItems;
    }

    
}
