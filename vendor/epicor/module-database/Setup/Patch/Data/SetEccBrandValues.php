<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Setup\Patch\Data;

use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class SetEccBrandValues
 * @package Epicor\Database\Setup\Patch\Data
 */
class SetEccBrandValues implements DataPatchInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Action
     */
    private $action;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * SetEccBrandValues constructor.
     * @param CollectionFactory $collectionFactory
     * @param Action $action
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param BulkManagementInterface $bulkManagement
     * @param OperationInterfaceFactory $operationFactory
     * @param IdentityGeneratorInterface $identityService
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Action $action,
        AttributeRepositoryInterface $attributeRepository,
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        BulkManagementInterface $bulkManagement,
        OperationInterfaceFactory $operationFactory,
        IdentityGeneratorInterface $identityService,
        SerializerInterface $serializer
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->action = $action;
        $this->attributeRepository = $attributeRepository;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operationFactory;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
           AddEccBrandOptions::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        if (!$eavSetup->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'ecc_brand')) {
            return;
        }

        $info = $this->getUploadInfo();
        $data = array_count_values($info);

        $values = $this->getOptionsValue();

        $this->publish($data, $info, $values);
    }

    /**
     * @return array
     */
    private function getUploadInfo()
    {
        $data = $this->collectionFactory->create()
            ->addFieldToFilter('ecc_brand', array('notnull' => true))
            ->addFieldToFilter('ecc_brand', array('neq' => ''))
            ->getItems();

        $info = array();
        foreach ($data as $d) {
            $info[$d->getEntityId()] = $d->getEccBrand();
        }

        return $info;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getOptionsValue()
    {
        $options = $this->attributeRepository->get(ProductAttributeInterface::ENTITY_TYPE_CODE, 'ecc_brand_updated')->getOptions();

        $values = array();
        foreach ($options as $option) {
            $values[$option['label']] = $option['value'];
        }

        return $values;
    }

    /**
     * @param $data
     * @param $info
     * @param $values
     * @throws LocalizedException
     */
    private function publish($data, $info, $values)
    {
        $productIds = array();
        foreach ($data as $val => $v) {
            $ids = array_keys($info, $val);
            $productIds[$values[$val]] = $ids;
        }

        $productIdsChunks = array_chunk(array_keys($productIds), 100, true);
        $bulkUuid = $this->identityService->generateId();
        $bulkDescription = __('Update ECC brand for ' . count($info) . ' products');
        $operations = [];
        foreach ($productIdsChunks as $productIdsChunk) {
            if ($productIds) {
                $operations[] = $this->makeOperation(
                    'Update ecc_brand_updated product attributes',
                    'ecc_brand.update',
                    $productIds,
                    $productIdsChunk,
                    $bulkUuid
                );
            }
        }

        if (!empty($operations)) {
            $result = $this->bulkManagement->scheduleBulk(
                $bulkUuid,
                $operations,
                $bulkDescription,
                1
            );
            if (!$result) {
                throw new LocalizedException(
                    __('Something went wrong while processing the request.')
                );
            }
        }
    }

    /**
     * @param $meta
     * @param $queue
     * @param $productIds
     * @param $chunk
     * @param $bulkUuid
     * @return mixed
     */
    private function makeOperation(
        $meta,
        $queue,
        $productIds,
        $chunk,
        $bulkUuid
    ) {
        $dataToEncode = [
            'meta_information' => $meta,
            'product_ids' => $productIds,
            'chunk' => $chunk
        ];
        $data = [
            'data' => [
                'bulk_uuid' => $bulkUuid,
                'topic_name' => $queue,
                'serialized_data' => $this->serializer->serialize($dataToEncode),
                'status' => \Magento\Framework\Bulk\OperationInterface::STATUS_TYPE_OPEN,
            ]
        ];

        return $this->operationFactory->create($data);
    }
}
