<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Api
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Api;

use Epicor\Punchout\Api\Data\TransactionlogsInterface;
use Epicor\Punchout\Model\Connections;
use Epicor\Punchout\Model\Transactionlogs;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface TransactionlogsRepositoryInterface
 *
 * @package Epicor\Punchout\Api
 */
interface TransactionlogsRepositoryInterface
{


    /**
     * Save Transaction logs.
     *
     * @param TransactionlogsInterface $transactionlog Transaction Logs interface.
     *
     * @return TransactionlogsInterface
     * @throws LocalizedException Exception.
     */
    public function save(TransactionlogsInterface $transactionlog);


    /**
     * Retrieve Transaction Logs.
     *
     * @param integer $transactionlogId transactionlog ID.
     *
     * @return TransactionlogsInterface
     * @throws LocalizedException Exception.
     */
    public function getById($transactionlogId);


    /**
     * Delete Transaction log.
     *
     * @param TransactionlogsInterface $transactionlog Transaction Log interface.
     *
     * @return boolean true on success
     * @throws LocalizedException Exception.
     */
    public function delete(TransactionlogsInterface $transactionlog);


    /**
     * Delete transactionlog by ID.
     *
     * @param integer $transactionlogId Transaction Log ID.
     *
     * @return boolean true on success
     * @throws NoSuchEntityException Exception.
     * @throws LocalizedException Exception.
     */
    public function deleteById($transactionlogId);


    /**
     * Get List of Transaction logs.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria Search Criteria.
     * @return TransactionlogsInterface Transaction Log interface.
     * @throws \Magento\Framework\Exception\LocalizedException LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria);


    /**
     * Load Entity.
     *
     * @param string|null $id Connection ID.
     *
     * @return Transactionlog
     */
    public function loadEntity($id=null);


}
