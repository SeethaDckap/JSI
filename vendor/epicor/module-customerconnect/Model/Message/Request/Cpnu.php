<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;

use Epicor\Comm\Model\Context;
use Epicor\Comm\Model\Message\Request;
use Epicor\Customerconnect\Model\ResourceModel\Erp\Customer\Skus\Collection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;

/**
 * Class Cpnu
 * @package Epicor\Customerconnect\Model\Message\Request
 */
class Cpnu extends Request
{
    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory; 
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Cpnu constructor.
     * @param Context $context
     * @param Collection $collection
     * @param ProductRepositoryInterface $productRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Collection $collection,
        ProductRepositoryInterface $productRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('CPNU');
        $this->setLicenseType('Customer');
        $this->setConfigBase('customerconnect_enabled_messages/CPNU_request/');
        $this->setResultsPath('products');
        $this->collection = $collection;
        $this->productRepository = $productRepository;
    }

    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function buildRequest()
    {
        $accountNumber = $this->getAccountNumber();
        $data = $this->getNewData();
        $cpnuAction = $this->getCpnuAction();

        if ($accountNumber) {

            $message = $this->getMessageTemplate();

            $message['messages']['request']['body']['accountNumber'] = $accountNumber;

            if ($cpnuAction == 'U' || $cpnuAction == 'R') {
                $coll = $this->collection->load()
                    ->addFieldToFilter('entity_id', array('in' => array_keys($data)))->getData();

                $pinfo = array();
                $oldCpn = array();
                foreach ($coll as $i) {
                    $pinfo[$i['entity_id']] = $i['product_id'];
                    $oldCpn[$i['entity_id']] = $i['sku'];
                }
            }

            $products = array();
            foreach ($data as $key => $items) {
                if ($cpnuAction == 'U' || $cpnuAction == 'R') {
                    $pcode = $pinfo[$key];
                    $oldPartNumber = $oldCpn[$key];
                } else {
                    $pcode = $items['pid'];
                    $oldPartNumber = '';
                }

                $customerPart = array(
                    '_attributes' => array('action' => $this->getCpnuAction()),
                    'oldProductCode' => $oldPartNumber,
                    'productCode' => $items['sku'],
                    'description' => $items['description']
                );

                $psku = $this->getProductSku($cpnuAction, $pcode, $items);

                $product = array(
                    'productCode' => $psku,
                    'customerPart' => $customerPart
                );

                array_push($products, $product);
            }

            $message['messages']['request']['body']['products']['product'] = $products;

            $this->setOutXml($message);

            return true;
        } else {
            return 'Missing account number';
        }
    }

    /**
     * @return bool
     */
    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            return true;
        }
        return false;
    }

    /**
     * @param $cpnuAction
     * @param $pcode
     * @param $items
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductSku($cpnuAction, $pcode, $items)
    {
        if ($cpnuAction == 'U' || $cpnuAction == 'R') {
            return $this->productRepository
                ->getById($pcode)
                ->getSku();
        } else {
            return $items['psku'];
        }
    }
}
