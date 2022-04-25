<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer;


/**
 * Currency display, converts a row value to currency display
 *
 * @author Sean Flynn
 */
class Quantities extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $list = array('Ordered' => 'quantities', 'Shipped' => 'delivered', 'To Follow' => 'to_follow');
        $quantitiesList = explode(',', $this->scopeConfig->getValue('customerconnect_enabled_messages/CUID_request/quantity_options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $html = '';
        foreach ($quantitiesList as $quantity) {
            if ($row[$quantity]) {
                $html .= array_search($quantity, $list) . ' : ' . floatval($row[$quantity]) . '<br/>';
            } else {
                $html .= array_search($quantity, $list) . ' : ' . '<br/>';
            }
        }

        return $html;
    }

}
