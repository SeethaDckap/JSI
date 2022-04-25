<?php
/**
 * Copyright © 2019-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Console\Command;

use Epicor\Comm\Helper\CommandLogger;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * This CLI is to update category position data for JIRA ECC-8593.
 * Class UpdateCategoryPosition
 */
class UpdateCategoryPosition extends ConsoleCommand
{
    const ALL_STORE_VIEW_ID = 0;

    /**
     * ResourceConnection
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * Directory List
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * CategoryHelper
     *
     * @var \Magento\Catalog\Helper\Category
     */
    private $category;

    /**
     * CategoryCollectionFactory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * Logger File
     *
     * @var \Epicor\Themes\Helper\Logger
     */
    private $commandlogger;

    /**
     * Category Repos Interface
     *
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepsInterface;

    /**
     * ImportErpMappingAttributes constructor.
     *
     * @param ResourceConnection                                              $resource                    Resource Connection.
     * @param \Magento\Catalog\Model\CategoryFactory                          $categoryFactory             CategoryFactory.
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory   CategoryCollectionFactory.
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface                $categoryRepositoryInterface CategoryRepositoryInterface.
     * @param \Epicor\Comm\Helper\CommandLogger                               $commandLogger               Command Logger.
     * @param \Magento\Framework\App\Filesystem\DirectoryList                 $directoryList               Directory List.
     */
    public function __construct(
        ResourceConnection $resource,
        CategoryFactory $categoryFactory,
        CollectionFactory $categoryCollectionFactory,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        CommandLogger $commandLogger,
        DirectoryList $directoryList
    ) {
        parent::__construct();
        $this->resource                  = $resource;
        $this->category                  = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepsInterface     = $categoryRepositoryInterface;
        $this->commandlogger             = $commandLogger;
        $this->directoryList             = $directoryList;

    }//end __construct()


    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('ecc-fixes:update_category_position');
        $this->setDescription(
            'Update category position to incremental position values'
        );
        parent::configure();

    }//end configure()


    /**
     * Execute function
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input  Input interface.
     * @param \Symfony\Component\Console\Output\OutputInterface $output Output interface.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException Localized Exception.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandlogger->createCommandLogFile();
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addAttributeToSelect('*')->addFieldToFilter(
            'parent_id',
            [
                'nin' => [
                    '0',
                    '1',
                ]
            ]
        );
        $count = 0;
        foreach ($categoryCollection as $category) {
            $duplicatePositionFound = $this->checkPositionDuplicate($category);
            if (!empty($duplicatePositionFound)) {
                $count++;
                $this->updatePosition($duplicatePositionFound, $output);
            }
        }

        if ($count === 0) {
            $this->commandlogger->log('NO CHANGES MADE!!');
        }

        $path = $this->directoryList->getPath(directoryList::ROOT);
        $this->commandlogger->log('Script finished execution - '.$this->commandlogger->getTime());
        $output->writeln('Complete - Please see '.$path.$this->commandlogger->getLogFile());

    }//end execute()


    /**
     * Update position of category.
     *
     * @param array                                             $duplicatePositionFound Duplicate Position Found.
     * @param \Symfony\Component\Console\Output\OutputInterface $output                 Output interface.
     */
    private function updatePosition($duplicatePositionFound, $output)
    {
        foreach ($duplicatePositionFound as $v) {
            $categoryObject = $this->category->create()->load($v['entity_id']);
            try {
                $path          = explode('/', (string) $v['path']);
                $toUpdateChild = array_diff($path, [$v['entity_id']]);
                $childPath     = implode('/', $toUpdateChild);
                $maxPosition   = $this->getMaxPositionByLevel($childPath);
                $this->commandlogger->log(
                    'Category Id '.$v['entity_id'].', '.$this->getCategoryName($v['entity_id'], $output).' position has changed from '.$v['position']
                    .' to '.($maxPosition + 1).' because it had a duplicate position.'
                );
                $categoryObject->setPosition($maxPosition + 1);
                $categoryObject->save();
            } catch (\Exception $e) {
                $output->writeln('Exception occurred while updating category position. Please Contact Epicor Dev team for assistance.');
                $output->writeln($e->getMessage());
            }//end try
        }

    }//end updatePosition()


    /**
     * Get maximum position of child categories by specific tree path
     *
     * @param  string $path Category Path.
     * @return int.
     */
    protected function getMaxPositionByLevel($path)
    {
        $connection    = $this->resource->getConnection();
        $positionField = $connection->quoteIdentifier('position');
        $level         = count(explode('/', (string) $path));
        $bind          = [
            'c_level' => $level,
            'c_path'  => $path.'/%',
        ];
        $select        = $connection->select()->from(
            'catalog_category_entity',
            'MAX('.$positionField.')'
        )->where(
            $connection->quoteIdentifier('path').' LIKE :c_path'
        )->where(
            $connection->quoteIdentifier('level').' = :c_level'
        );
        $position      = $connection->fetchOne($select, $bind);
        if (!$position) {
            $position = 0;
        }

        return $position;

    }//end getMaxPositionByLevel()


    /**
     * Check Category position is already assigned for some other category in same level
     *
     * @param object $categoryData Category object data.
     *
     * @return array
     */
    private function checkPositionDuplicate($categoryData)
    {
        $path          = explode('/', (string)$categoryData->getPath());
        $toUpdateChild = array_diff($path, [ $categoryData->getEntityId()]);
        $childPath     = implode('/', $toUpdateChild);
        $positionusedByCategory = [];
        $connection             = $this->resource->getConnection();
        $bind                   = [
            'c_position' => $categoryData->getPosition(),
            'c_level'    => $categoryData->getLevel(),
            'c_path'     => $childPath.'/%',
        ];
        $select                 = $connection->select()->from(
            'catalog_category_entity',
            '*'
        )->where(
            $connection->quoteIdentifier('position').' = :c_position'
        )->where(
            $connection->quoteIdentifier('level').' = :c_level'
        )->where(
            $connection->quoteIdentifier('path').' LIKE  :c_path'
        );
        $categoryPositionPresent = $connection->fetchAll($select, $bind);
        if (!empty($categoryPositionPresent)) {
            unset($categoryPositionPresent[0]);
            $positionusedByCategory = $categoryPositionPresent;
        }

        return $positionusedByCategory;

    }//end checkPositionDuplicate()


    /**
     * Get category Name by Id.
     *
     * @param integer $id Category Id.
     * @param $output Output interface.
     *
     * @return null|string
     */
    private function getCategoryName($id, $output)
    {
        try {
            $categoryInstance = $this->categoryRepsInterface->get($id, self::ALL_STORE_VIEW_ID);
            return $categoryInstance->getName();
        } catch (\Exception $e) {
            $output->writeln('unknown category '.$e->getMessage());
        }

    }//end getCategoryName()


}
