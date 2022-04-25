<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Epicor\OrderApproval\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Epicor\OrderApproval\Model\ResourceModel\Groups\CustomerFactory as CustomerResourceFactory;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Customer as ResourceCustomer;
use Epicor\OrderApproval\Model\Groups\CustomerFactory as CustomerFactory;
use Epicor\OrderApproval\Model\Groups\Customer;
use \Epicor\OrderApproval\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CustomerRepository
 *
 * @package Epicor\OrderApproval\Model
 */
class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var ResourceCustomer
     */
    protected $resource;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerResourceFactory
     */
    private $customerResourceFactory;

    /**
     * CustomerRepository constructor.
     *
     * @param ResourceCustomer       $resource
     * @param CustomerResourcFactory $customerResourceFactory
     * @param CustomerFactory        $customerFactory
     */
    public function __construct(
        ResourceCustomer $resource,
        CustomerResourceFactory $customerResourceFactory,
        CustomerFactory $customerFactory
    ) {
        $this->resource = $resource;
        $this->customerFactory = $customerFactory;
        $this->customerResourceFactory = $customerResourceFactory;

    }

    /**
     * @param CustomerInterface|Customer $customer
     *
     * @return CustomerInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save(CustomerInterface $customer)
    {
        try {
            $this->resource->save($customer);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the customer: %1', $exception->getMessage()),
                $exception
            );
        }

        return $customer;
    }

    /**
     * @param string $customerId
     *
     * @return Customer
     */
    public function getById($customerId)
    {
        /** @var Customer $customer */
        $customer = $this->customerFactory->create();
        $this->customerResourceFactory->create()->load($customer, $customerId);
        if ( ! $customer->getId()) {
            throw new NoSuchEntityException(
                __('The Customer with the "%1" ID doesn\'t exist.', $customerId)
            );
        }

        return $customer;
    }

    /**
     * @param CustomerInterface|Customer $customer
     *
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(CustomerInterface $customer)
    {
        try {
            $this->resource->delete($customer);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Group: %1', $exception->getMessage())
            );
        }

        return true;
    }
}
