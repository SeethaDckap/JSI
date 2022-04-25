<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Model\ResourceModel\Faqs;


/**
 * Faqs collection 
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 * 
 * @method   __construct()
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Faq\Collection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }


    protected function _construct()
    {
        $this->_init('Epicor\Faqs\Model\Faqs', 'Epicor\Faqs\Model\ResourceModel\Faqs');
    }

}
