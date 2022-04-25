<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Cart\Contract\Select\Renderer;

/**
 * Price Renderer for Line Contract Select Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
    \Magento\Backend\Block\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Epicor\Comm\Helper\Messaging $commMessagingHelper, array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
                $context, $data
        );
    }

    /**
     * Render column
     *
     * @param   \Epicor\Lists\Model\ListModel $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row) {
        $store = $this->storeManager->getStore();
        /* @var $store Epicor_Comm_Model_Store */
        $html = $this->commMessagingHelper->formatPrice($row->getPrice());

        if ($row->getPriceBreaks()) {
            $html .= '<br / ><ul class="tier-prices product-pricing">';

            foreach ($row->getPriceBreaks() as $x => $break) {
                if(is_array($break)){
                    $discountVal = round(100 - (($break['price'] / $row->getPrice()) * 100));
                    $discount = '<span class="percent tier-' . $x . '">' . $discountVal . '%</span>';
                    //M1 > M2 Translation Begin (Rule 55)
                    //$saving = '<strong class="benefit">' . $this->__('save %s', $discount) . '</strong>';
                    $saving = '<strong class="benefit">' . __('save %1', $discount) . '</strong>';
                    //M1 > M2 Translation End
                    $priceDisplay = '<span class="price">' . $this->commMessagingHelper->formatPrice($break['price'], false) . '</span>';
                    $html .= '<li class="tier-price tier-' . $x . '">';
                    //M1 > M2 Translation Begin (Rule 55)
                    //$html .= $this->__('Buy %s for %s each and %s', $break->getQuantity(), $priceDisplay, $saving);
                    $html .= __('Buy %1 for %2 each and %3', $break['quantity'], $priceDisplay, $saving);
                    //M1 > M2 Translation End
                    $html .= '</li>';
                }else{
                    $discountVal = round(100 - (($break->getPrice() / $row->getPrice()) * 100));
                    $discount = '<span class="percent tier-' . $x . '">' . $discountVal . '%</span>';
                    //M1 > M2 Translation Begin (Rule 55)
                    //$saving = '<strong class="benefit">' . $this->__('save %s', $discount) . '</strong>';
                    $saving = '<strong class="benefit">' . __('save %1', $discount) . '</strong>';
                    //M1 > M2 Translation End
                    $priceDisplay = '<span class="price">' . $this->commMessagingHelper->formatPrice($break->getPrice(), false) . '</span>';
                    $html .= '<li class="tier-price tier-' . $x . '">';
                    //M1 > M2 Translation Begin (Rule 55)
                    //$html .= $this->__('Buy %s for %s each and %s', $break->getQuantity(), $priceDisplay, $saving);
                    $html .= __('Buy %1 for %2 each and %3', $break->getQuantity(), $priceDisplay, $saving);
                    //M1 > M2 Translation End
                    $html .= '</li>';

                }
            }

            $html .= '</ul>';
        }
        return $html;
    }

}
