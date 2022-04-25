<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Renderer;


/**
 * Invoice Reorder link grid renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Remotelinks extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Epicor\Comm\Helper\Remotelinks
     */
    protected $commRemotelinksHelper;

    public function __construct(
        \Epicor\Comm\Helper\Remotelinks $commRemotelinksHelper
    ) {
        $this->commRemotelinksHelper = $commRemotelinksHelper;
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->commRemotelinksHelper;
        /* @var $helper Epicor_Comm_Helper_Remotelinks */

        $remotelink_code = $this->getColumn()->getRemotelinkCode();
        $title = $this->getColumn()->getRemotelinkLabel();
        $url = $helper->fieldSubstitution($row, $remotelink_code);
        $html = '<a title="' . $title . '" href="' . $url . '">' . $title . '</a>';

        return $html;
    }

}
