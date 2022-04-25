<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Block\Account;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Address\Config;
use Magento\Customer\Model\Session;


class Address
{

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var Config
     */
    protected $_addressConfig;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Address constructor.
     * @param AddressRepositoryInterface $addressRepository
     * @param Config $addressConfig
     * @param Mapper $addressMapper
     * @param Session $customerSession
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        Config $addressConfig,
        Mapper $addressMapper,
        Session $customerSession
    ){

        $this->addressRepository = $addressRepository;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Customer\Block\Account\Dashboard\Address $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetPrimaryShippingAddressHtml(
        \Magento\Customer\Block\Account\Dashboard\Address $subject,
        $result
    )
    {
        $_pAddsses = $this->customerSession->getCustomer()->getPrimaryShippingAddress();
        if ($_pAddsses && $_pAddsses->getId()) {
            $result = $this->_getAddressHtml($this->getAddressById($_pAddsses->getId()));
        }

        return $result;
    }

    /**
     * @param \Magento\Customer\Block\Account\Dashboard\Address $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetPrimaryBillingAddressHtml(
        \Magento\Customer\Block\Account\Dashboard\Address $subject,
        $result
    )
    {
        $_pAddsses = $this->customerSession->getCustomer()->getPrimaryBillingAddress();
        if ($_pAddsses && $_pAddsses->getId()) {
            $result = $this->_getAddressHtml($this->getAddressById($_pAddsses->getId()));
        }

        return $result;
    }

    /**
     * Get customer address by ID
     *
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAddressById($addressId)
    {
        try {
            return $this->addressRepository->getById($addressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param $address
     * @return string
     */
    protected function _getAddressHtml($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($this->addressMapper->toFlatArray($address));
    }

}
