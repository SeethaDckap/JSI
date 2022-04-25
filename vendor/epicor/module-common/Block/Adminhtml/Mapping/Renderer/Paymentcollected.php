<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Renderer;


/**
 * ERP Image type list renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Paymentcollected extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $paymentOptions = array(
            '' => '',
            'C' => 'C - Collected',
            'A' => 'A - Authorised',
            'D' => 'D - Authorised/Will capture on ship',
            'N' => 'N - Token only '
        );

        $paymentCollected = $row->getPaymentCollected();
        $html = $paymentOptions[$paymentCollected];
        return $html;
    }

}
