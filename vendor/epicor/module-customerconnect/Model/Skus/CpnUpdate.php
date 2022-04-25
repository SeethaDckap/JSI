<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\Skus;

use Epicor\Customerconnect\Model\Erp\Customer\Skus;
use Epicor\Customerconnect\Model\ResourceModel\Erp\Customer\Skus as ResourceSkus;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class CpnUpdate
 * @package Epicor\Customerconnect\Model\Skus
 */
class CpnUpdate
{
    /**
     * @var Skus
     */
    private $skus;

    /**
     * @var ResourceSkus
     */
    private $resourceSkus;

    /**
     * @var ResourceSkus\CollectionFactory
     */
    private $collection;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * CpnUpdate constructor.
     * @param Skus $skus
     * @param ResourceSkus $resourceSkus
     * @param ResourceSkus\CollectionFactory $collection
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Skus $skus,
        ResourceSkus $resourceSkus,
        ResourceSkus\CollectionFactory $collection,
        ManagerInterface $messageManager
    ) {
        $this->skus = $skus;
        $this->resourceSkus = $resourceSkus;
        $this->collection = $collection;
        $this->messageManager = $messageManager;
    }

    /**
     * @param $info
     * @return bool
     * @throws \Exception
     */
    public function update($info)
    {
        $duplicates = 'CPN(s)';
        $nonDups = 'CPN(s)';
        $result = true;
        $success = false;

        foreach ($info as $key => $item) {
            $data = [
                'entity_id' => $key,
                'sku' => $item['sku'],
                'description' => $item['description']
            ];
            $accSkus = $this->skus->setData($data);
            try {
                $this->resourceSkus->save($accSkus);
                $nonDups = $nonDups . ' ' . $item['sku'] . ',';
                $success = true;
            } catch (AlreadyExistsException $alreadyExistsException) {
                $duplicates = $duplicates . ' ' . $item['sku'] . ',';
                unset($info[$key]);
                $result = false;
            }
        }

        if (!$result) {
            $duplicates = rtrim($duplicates, ',');
            $duplicates = 'Combination(s) of Product SKU with ' . $duplicates . ' already exist(s) in ECC. So, can\'t be updated. Kindly check for duplicates.';
            $this->messageManager->addErrorMessage($duplicates);
        }

        if ($success) {
            $nonDups = rtrim($nonDups, ',');
            $nonDups = $nonDups . ' has been updated in ECC.';
            $this->messageManager->addSuccessMessage($nonDups);
        }

        return $info;
    }

    /**
     * @param $addData
     * @return bool
     * @throws \Exception
     */
    public function add($addData)
    {
        $duplicates = 'Combination(s)';
        $nonDups = 'Combination(s)';
        $result = true;
        $success = false;

        foreach ($addData as $key => $item) {
            $data = [
                'product_id' => $item['pid'],
                'customer_group_id' => $item['customer_group_id'],
                'sku' => $item['sku'],
                'description' => $item['description']
            ];
            $itemSkus = $this->skus->setData($data);
            try {
                $this->resourceSkus->save($itemSkus);
                $nonDups = $nonDups . ' ' . $item['psku'] . ' & ' . $item['sku'] . ',';
                $success = true;
            } catch (AlreadyExistsException $alreadyExistsException) {
                $duplicates = $duplicates . ' ' . $item['psku'] . ' & ' . $item['sku'] . ',';
                $result = false;
                unset($addData[$key]);
            }
        }

        if (!$result) {
            $duplicates = rtrim($duplicates, ',');
            $duplicates = $duplicates . ' already exist(s) in ECC.';
            $this->messageManager->addErrorMessage($duplicates);
        }

        if ($success) {
            $nonDups = rtrim($nonDups, ',');
            $nonDups = $nonDups . ' has been saved in ECC.';
            $this->messageManager->addSuccessMessage($nonDups);
        }

        return $addData;
    }

    /**
     * @param $data
     * @throws \Exception
     */
    public function delete($data)
    {
        $ids = array_keys($data);
        $coll = $this->collection->create()->addFieldToFilter('entity_id', array('in' => $ids));
        foreach ($coll as $item) {
            $this->resourceSkus->delete($item);
        }
    }
}
