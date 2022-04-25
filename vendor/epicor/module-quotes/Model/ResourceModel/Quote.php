<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\ResourceModel;

use Epicor\Database\Model\ResourceModel\Quote as EpicorDbQuote;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Psr\Log\LoggerInterface;

class Quote extends EpicorDbQuote
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Quote constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $connectionName
        );
        $this->logger = $logger;
    }

    /**
     * Fetch web reference id
     *
     * @param int $entityId
     * @return string|void
     */
    public function getWebReferenceId($entityId)
    {
        try {
            $select = $this->getConnection()->select()->from(
                $this->getMainTable(),
                ['reference']
            )->where(
                'entity_id = ?',
                $entityId
            );

            return $this->getConnection()->fetchOne($select);
        } catch (LocalizedException $localizedException) {
            $this->logger->error($localizedException->getMessage());
            return;
        }
    }
}
