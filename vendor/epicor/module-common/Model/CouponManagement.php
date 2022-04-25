<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;


use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;

/**
 * XML validator
 *
 * Validates an XML object based on rules supplied
 *
 * @author Gareth.James
 */
class CouponManagement extends \Magento\Quote\Model\CouponManagement
{

    protected $quoteRepository;

    protected $registry;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Registry $registry
    )
    {
        parent::__construct($quoteRepository);
        $this->quoteRepository = $quoteRepository;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $couponCode)
    {
        $couponCode = trim($couponCode);
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        if (!$quote->getStoreId()) {
            throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $this->registry->unregister('ecc_appply_coupon');
            $this->registry->register('ecc_appply_coupon',true);
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__('The coupon code couldn\'t be applied: ' .$e->getMessage()), $e);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __("The coupon code couldn't be applied. Verify the coupon code and try again."),
                $e
            );
        }
        if ($quote->getCouponCode() != $couponCode) {
            $quote->setCouponCode('');
            $quote->getShippingAddress()->setFreeShipping(0);
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }
        return true;
    }

    public function remove($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setCouponCode('');
            $quote->setDiscountAmount(0);
            $quote->getShippingAddress()->setFreeShipping(0);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete coupon code'));
        }
        if ($quote->getCouponCode() != '') {
            throw new CouldNotDeleteException(__('Could not delete coupon code'));
        }
        return true;
    }

}