<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Cart\Totals;

use Epicor\BranchPickup\Model\Carrier\Epicorbranchpickup;
use Epicor\Comm\Helper\Cart\SendbsvFactory;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Model\TotalsInformationManagement;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;

class TotalsInformationManagementPlugin
{
     /**
     * @var SendbsvFactory
     */
    protected $sendBsvHelperFactory;

    /**
     * Cart total repository.
     *
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    public function __construct(
        SendbsvFactory $sendBsvHelperFactory,
        CartRepositoryInterface $cartRepository,
        CartTotalRepositoryInterface $cartTotalRepository
    ) {
        $this->sendBsvHelperFactory = $sendBsvHelperFactory;
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
    }

    /**
     * @param TotalsInformationManagement $subject
     * @param $cartId
     * @param TotalsInformationInterface $addressInformation
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeCalculate(
        TotalsInformationManagement $subject,
        $cartId,
        TotalsInformationInterface $addressInformation
    )
    {
        $eccData = [];
        $quote = $this->cartRepository->get($cartId);
        $newcode= $addressInformation->getShippingCarrierCode() . '_' . $addressInformation->getShippingMethodCode();
        if($addressInformation->getShippingCarrierCode() && $quote->getShippingAddress()->getShippingMethod() !== $newcode){
            $eccData = [
                'ecc_bsv_goods_total' => null,
                'ecc_bsv_goods_total_inc' => null,
                'ecc_bsv_carriage_amount' => null,
                'ecc_bsv_carriage_amount_inc' => null,
                'ecc_bsv_discount_amount' => null,
                'ecc_bsv_grand_total' => null,
                'ecc_bsv_grand_total_inc' => null
            ];
        }
        if ($quote) {
            $quote->addData($eccData);
            if ($quote->getShippingAddress()) {
                $quote->getShippingAddress()->addData($eccData);
                if($quote->getShippingAddress()->getShippingMethod() === Epicorbranchpickup::ECC_BRANCHPICKUP_COMBINE){
                    $addressInformation->setShippingCarrierCode(Epicorbranchpickup::ECC_BRANCHPICKUP);
                    $addressInformation->setShippingMethodCode(Epicorbranchpickup::ECC_BRANCHPICKUP);
                }
            }
        }
    }

    /**
     * Send BSV after quote collect totals is run
     *
     * @param TotalsInformationManagement $subject
     * @param $return
     * @param $cartId
     * @param TotalsInformationInterface $addressInformation
     * @return \Magento\Quote\Api\Data\TotalsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterCalculate(
        TotalsInformationManagement $subject,
        $return,
        $cartId,
        TotalsInformationInterface $addressInformation
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->cartRepository->get($cartId);

        /* unwanted bsv at the time of totals-information */
        if ($quote->getShippingAddress()->getShippingMethod()) {
            /* @var $helper \Epicor\Comm\Helper\Cart\Sendbsv */
            $helper = $this->sendBsvHelperFactory->create();
            $helper->sendCartBsv($quote);
            $quote->save();
        }

        return $this->cartTotalRepository->get($cartId);
    }
}
