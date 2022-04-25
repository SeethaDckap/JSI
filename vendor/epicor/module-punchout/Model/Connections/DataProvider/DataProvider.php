<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Connections\DataProvider;

use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Epicor\Punchout\Model\ResourceModel\Connections\Collection;
use Epicor\Punchout\Model\ResourceModel\Connections\CollectionFactory;
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
     * Config reader
     *
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * Json Serializer
     *
     * @var Serializer
     */
    private $serializer;


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
        ConfigReaderInterface $configReader,
        Data $helper,
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
        $this->collection    = $helper->getBlockCollection();
        $this->configReader  = $configReader;
        $this->dataPersistor = $helper->getDataPersistor();
        $this->serializer    = $helper->getSerializer();

    }//end __construct()


    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        if (! count($items)) {
            $newItem = $this->collection->getNewEmptyItem();
            $this->initDefaultMappings($newItem->getId());
        }

        foreach ($items as $connection) {
            $connection->setScope();
            $this->loadedData[$connection->getId()] = $connection->getData();
            $this->addMappings($connection);
        }

        $data = $this->dataPersistor->get('connection');
        if (! empty($data)) {
            $connection = $this->collection->getNewEmptyItem();
            $connection->setData($data);
            $this->loadedData[$connection->getId()] = $connection->getData();
            $this->dataPersistor->clear('connection');
        }

        return $this->loadedData;

    }//end getData()


    /**
     * Pre fill default mapping data.
     *
     * @param integer $id Connection ID.
     *
     * @return void
     */
    public function initDefaultMappings($id)
    {
        $mappingData = array_values((array) $this->configReader->getData('connection_mappings'));
        $this->loadedData[$id]['connection_mappings'] = $mappingData;

    }//end initDefaultMappings()


    /**
     * Mapping data provider.
     *
     * @param mixed $connection Connection model.
     *
     * @return void
     */
    public function addMappings($connection)
    {
        $mappings       = $this->serializer->unserialize($connection->getMappings());
        $methodMappings = $this->serializer->unserialize($connection->getShippingMappings());

        $this->loadedData[$connection->getId()]['connection_mappings']          = $mappings;
        $this->loadedData[$connection->getId()]['connection_shipping_mappings'] = $methodMappings;

    }//end initDefaultMappings()


}//end class
