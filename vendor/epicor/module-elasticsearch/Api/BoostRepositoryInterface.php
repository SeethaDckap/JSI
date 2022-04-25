<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Api;

/**
 * Interface BoostRepositoryInterface
 *
 * @package Epicor\Elasticsearch\Api
 * @api
 */
interface BoostRepositoryInterface
{
    /**
     * Retrieve boost by its ID
     *
     * @param int $boostId Id of the boost.
     *
     * @return \Epicor\Elasticsearch\Api\Data\BoostInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($boostId);

    /**
     * Retrieve list of boost
     *
     * @return \Epicor\Elasticsearch\Api\Data\BoostInterface
     */
    public function getList();

    /**
     * Save boost record
     *
     * @param \Epicor\Elasticsearch\Api\Data\BoostInterface $boost
     *
     * @return \Epicor\Elasticsearch\Api\Data\BoostInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(\Epicor\Elasticsearch\Api\Data\BoostInterface $boost);

    /**
     * Delete boost record
     *
     * @param \Epicor\Elasticsearch\Api\Data\BoostInterface $boost
     *
     * @return \Epicor\Elasticsearch\Api\Data\BoostInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Epicor\Elasticsearch\Api\Data\BoostInterface $boost);

    /**
     * Remove boost by given ID
     *
     * @param int $boostId Id of the boost.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function deleteById($boostId);
}

