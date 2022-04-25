<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Helper;


/**
 * Branch Helper
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Epicor\AccessRight\Model\Authorization $authorization
    ) {
        $this->_accessauthorization = $authorization;
        parent::__construct(
            $context
        );
    }

    /**
     * @return \Magento\Framework\AuthorizationInterface
     */
    public function getAccessAuthorization()
    {
        return $this->_accessauthorization;
    }

    /**
     * @return bool
     */
    public function isAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }

    public function coreActionList()
    {
        $actions = [
            'sales_order_view' => 'Epicor_Customer::my_account_orders_details',
            'sales_order_history' => 'Epicor_Customer::my_account_orders_read',
            'customer_address_index' => 'Epicor_Customer::my_account_address_book_read',
            'customer_address_form' => 'Epicor_Customer::my_account_address_book_edit',
            'customer_account_edit' => 'Epicor_Customer::my_account_information',
            'downloadable_customer_products' => 'Epicor_Customer::my_account_downloadable_products',
            'wishlist_index_index' => 'Epicor_Customer::my_account_wishlist',
            'newsletter_manage_index' => 'Epicor_Customer::my_account_dashboard_newsletters_edit',
            'review_customer_index' => 'Epicor_Customer::my_account_products_review_read',
            'review_customer_view' => 'Epicor_Customer::my_account_products_review_details',
            'paypal_billing_agreement_index' => 'Epicor_Customer::my_account_billing_agreements_read',
            'vault_cards_listaction' => 'Epicor_Customer::my_account_stored_payment_methods_edit'
        ];
        return $actions;
    }
}
