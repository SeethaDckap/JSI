<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Checkout;


class Session
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Session constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ){
        $this->registry = $registry;
    }

    /**
     *  to make sure Epicor\Comm\Helper\Data::customerSessionFactory() init soon after login
     * to fix not sned default account soon after login msq
     *
     * @param \Magento\Checkout\Model\Session $subject
     */

    public function beforeLoadCustomerQuote(\Magento\Checkout\Model\Session $subject) {

        if (!$this->registry->registry('bsv_sent')) {
            $this->registry->unregister('after_login_msq_init');
            $this->registry->register('after_login_msq_init', 1);
        }
    }

}