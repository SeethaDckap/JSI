<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Cart;

use Epicor\Comm\Controller\Cart\Add as CartAdd;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Add
 * @package Epicor\Comm\Plugin\Cart
 */
class Add
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var CustomerCart
     */
    private $cart;

    /**
     * Add constructor.
     * @param ManagerInterface $messageManager
     * @param CustomerCart $cart
     */
    public function __construct(
        ManagerInterface $messageManager,
        CustomerCart $cart
    ) {
        $this->messageManager = $messageManager;
        $this->cart = $cart;
    }

    /**
     * Clearing Success message for add to cart on error
     * @param CartAdd $subject
     * @param ResponseInterface|ResultInterface $result
     * @return mixed
     */
    public function afterExecute(
        CartAdd $subject,
        $result
    ) {
        $childIds = $subject->getRequest()->getParam('super_group');
        $productId = [];
        if (!empty($childIds)) {
            $childIds = array_keys(array_filter(
                $childIds,
                function ($arrayValue) {
                    return ($arrayValue != 0);
                }
            ));
        }
        if (!empty($childIds)) {
            $productId = $childIds;
        } else if ($configOption = $subject->getRequest()->getParam('selected_configurable_option')) {
            $productId[] = (int)$configOption;
        } else {
            $productId[] = (int)$subject->getRequest()->getParam('product');
        }

        foreach ($productId as $id) {
            if ($this->cart->getQuote()->getHasError()) {
                $productIds = [];
                $messages = $this->messageManager->getMessages()->getErrors();
                foreach ($messages as $message) {
                    $data = $message->getData();
                    if (empty($data) === false && isset($data['product_id'])) {
                        $productIds[] = $data['product_id'];
                    }
                }

                if (in_array($id, $productIds) && (empty($childIds) || ($childIds == $productIds))) {
                    $this->messageManager->getMessages()
                        ->deleteMessageByIdentifier('addCartSuccessMessage');
                    $this->messageManager->getMessages()
                        ->deleteMessageByIdentifier('default_message_identifier');
                }
                if (empty($data)) {
                    $this->messageManager->getMessages()->deleteMessageByIdentifier('addCartSuccessMessage');
                }
            }
        }
        return $result;
    }
}