<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Themes
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

declare(strict_types=1);

namespace Epicor\Punchout\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Epicor\Punchout\Model\Connections as Connection;
use Epicor\Punchout\Model\ResourceModel\Connections\CollectionFactory;
use Epicor\Punchout\Api\ConnectionsRepositoryInterface;
use Epicor\Punchout\Helper\Data;
use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;

/**
 * Greenblack menu tab controller
 */
abstract class Connections extends \Magento\Backend\App\Action
{

    /**
     * #@+
     * Lengths of secret key
     */
    const LENGTH_CONNECTION_SECRET = 32;

    /**
     * Result layout factory.
     *
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Result Factory
     *
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * Result Page Factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;


    /**
     * Connection collection
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Connection repository interface.
     *
     * @var ConnectionsRepositoryInterface
     */
    protected $connectionRepository;

    /**
     * Helper Data.
     *
     * @var Data
     */
    protected $helper;

    /**
     * Json serializer.
     *
     * @var Serializer
     */
    protected $serializer;

    /**
     * Math random.
     *
     * @var Random
     */
    protected $mathRandom;


    /**
     * Constructor function.
     *
     * @param Context                        $context              Context.
     * @param LayoutFactory                  $resultLayoutFactory  Result layout factory.
     * @param PageFactory                    $resultPageFactory    Page factory.
     * @param FIlter                         $filter               UI component filter.
     * @param CollectionFactory              $collectionFactory    Connection collection.
     * @param ConnectionsRepositoryInterface $connectionRepository Connection repository inetrface.
     * @param Data                           $helper               Helper class.
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ConnectionsRepositoryInterface $connectionRepository,
        Data $helper
    ) {
        parent::__construct($context);

        $this->resultLayoutFactory  = $resultLayoutFactory;
        $this->resultFactory        = $context->getResultFactory();
        $this->resultPageFactory    = $resultPageFactory;
        $this->filter               = $filter;
        $this->collectionFactory    = $collectionFactory;
        $this->connectionRepository = $connectionRepository;
        $this->serializer           = $helper->getSerializer();
        $this->mathRandom           = $helper->getMathRandom();

    }//end __construct()


    /**
     * Load Entity.
     *
     * @param string|null $id Connection ID.
     *
     * @return Connection
     */
    public function loadEntity($id=null)
    {
        return $this->connectionRepository->loadEntity($id);

    }//end loadEntity()


    /**
     * Save mapping data
     *
     * @param Connection $connection Connection model.
     * @param array      $data       Data array.
     *
     * @return Connection
     */
    public function processMappings($connection, $data)
    {
            $mappingData = isset($data['connection_mappings']) ? $data['connection_mappings'] : [];
            $serializedData = $this->serializer->serialize($mappingData);
            $connection->setMappings($serializedData);

            $methodMappings = isset($data['connection_shipping_mappings']) ? $data['connection_shipping_mappings'] : [];
            $serializedData = $this->serializer->serialize($methodMappings);
            $connection->setShippingMappings($serializedData);

        return $connection;

    }//end processMappings()


    /**
     * Generate secret key
     *
     * @param Connection $connection Connection model.
     *
     * @return Connection
     * @throws LocalizedException Exception.
     */
    public function generateSecretKey($connection)
    {
        $secretKey = $this->mathRandom->getRandomString(
            self::LENGTH_CONNECTION_SECRET,
            \Magento\Framework\Math\Random::CHARS_DIGITS.\Magento\Framework\Math\Random::CHARS_LOWERS
        );

        return $connection->setSharedSecret($secretKey);

    }//end generateSecretKey()


}//end class
