<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Transactionlogs\DataProvider;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Epicor\Punchout\Model\ResourceModel\Transactionlogs\Collection;
use Epicor\Punchout\Model\ResourceModel\Transactionlogs\CollectionFactory;
use Epicor\Punchout\Helper\Data;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{

    /**
     * Connections collection.
     *
     * @var Collection
     */
    protected $collection;

    /**
     * Loaded data.
     *
     * @var array
     */
    private $loadedData;

    /**
     * Data persistor interface.
     *
     * @var DataPersistorInterface
     */
    private $dataPersistor;


    /**
     * Constructor function.
     *
     * @param string                $name             Name.
     * @param string                $primaryFieldName Primary field name.
     * @param string                $requestFieldName Request field name.
     * @param ConfigReaderInterface $configReader     Config reader.
     * @param Data                  $helper           Helper class.
     * @param array                 $meta             Meta data array.
     * @param array                 $data             Data array.
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blockCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta=[],
        array $data=[]
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection    = $blockCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;

    }//end __construct()


    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Magento\Cms\Model\Block $block */
        foreach ($items as $block) {
            $this->loadedData[$block->getId()] = $block->getData();
        }
        $data = $this->dataPersistor->get('transactionlogs');
        if (!empty($data)) {
            $block = $this->collection->getNewEmptyItem();
            $block->setData($data);
            $this->loadedData[$block->getId()] = $block->getData();
            $this->dataPersistor->clear('transactionlogs');
        }
        return $this->loadedData;
    }

}//end class
