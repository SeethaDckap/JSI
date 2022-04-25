<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Helper
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Helper;

use Epicor\Punchout\Model\ResourceModel\Connections\Collection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Math\Random;
use Epicor\Punchout\Model\ResourceModel\Connections\CollectionFactory;
use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;

/**
 * Dara helper.
 */
class Data extends AbstractHelper
{

    /**
     * Json Serializer
     *
     * @var Serializer
     */
    private $serializer;

    /**
     * Math random.
     *
     * @var Random
     */
    private $mathRandom;

    /**
     * Data persistor interface.
     *
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Connections collection.
     *
     * @var Collection
     */
    private $collection;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;


    /**
     * Construction function.
     *
     * @param Context                $context                Helper context.
     * @param Serializer             $serializer             Json serializer.
     * @param Random                 $mathRandom             Math random class.
     * @param DataPersistorInterface $dataPersistor          Data persistor interface.
     * @param CollectionFactory      $blockCollectionFactory Collection factory.
     * @param \Epicor\Comm\Helper\Data $commHelper Comm Helper.
     */
    public function __construct(
        Context $context,
        Serializer $serializer,
        Random $mathRandom,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $blockCollectionFactory,
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->serializer    = $serializer;
        $this->mathRandom    = $mathRandom;
        $this->dataPersistor = $dataPersistor;
        $this->collection    = $blockCollectionFactory->create();
        $this->commHelper = $commHelper;
        parent::__construct($context);

    }//end __construct()


    /**
     * Get Serializer.
     *
     * @return mixed
     */
    public function getSerializer()
    {
        return $this->serializer;

    }//end getSerializer()


    /**
     * Get Math random.
     *
     * @return mixed
     */
    public function getMathRandom()
    {
        return $this->mathRandom;

    }//end getMathRandom()


    /**
     * Get Data persistor.
     *
     * @return mixed
     */
    public function getDataPersistor()
    {
        return $this->dataPersistor;

    }//end getDataPersistor()


    /**
     * Get Block collection.
     *
     * @return mixed
     */
    public function getBlockCollection()
    {
        return $this->collection;

    }//


    /**
     * Get Comm Helper.
     *
     * @return mixed
     */
    public function getCommHelper()
    {
        return $this->commHelper;

    }

    /**
     * Get Punchout Url.
     *
     * @return mixed
     */
    public function getPunchoutUrl()
    {
        return $this->_getUrl('punchout/punchout/index');

    }

    /**
     * Retrieve customer logout url
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->_getUrl('punchout/punchout/logout');
    }


}//end class