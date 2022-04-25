<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Epicor\OrderApproval\Api\ErpAccountRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account as ResourceErpAccount;
use \Epicor\OrderApproval\Api\Data\ErpAccountInterface;

/**
 * Class GroupRepository
 *
 * @package Epicor\OrderApproval\Model
 */
class ErpAccountRepository implements ErpAccountRepositoryInterface
{
    /**
     * @var ResourceErpAccount
     */
    private $resource;

    /**
     * ErpAccountRepository constructor.
     *
     * @param ResourceErpAccount $resource
     */
    public function __construct(
        ResourceErpAccount $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param ErpAccountInterface $erpAccount
     *
     * @return ErpAccountInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save(ErpAccountInterface $erpAccount)
    {
        try {
            $this->resource->save($erpAccount);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the erp account: %1', $exception->getMessage()),
                $exception
            );
        }

        return $erpAccount;
    }

    /**
     * @param ErpAccountInterface $erpAccount
     *
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(ErpAccountInterface $erpAccount)
    {
        try {
            $this->resource->delete($erpAccount);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Group: %1', $exception->getMessage())
            );
        }

        return true;
    }
}
