<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Renderer;


/**
 * Renderer for Sites > Stores column, shows list of stores for the site
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Remotelinks extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $httpAuth = $row->getHttpAuthorization();
        if ($httpAuth) {
            $output = ' <img src="' . $this->getViewFileUrl('Epicor_Common::epicor/common/images/success_msg_icon.gif') . '" alt="HTTP NOT Authorised" /> ';
//            if ($cert->isValidCertificate()) {
//                $output = ' <img src="' . $this->getSkinUrl('images/success_msg_icon.gif') . '" alt="HTTP Authorised" /> ';
//            } else {
//                $output = ' <img src="' . $this->getSkinUrl('images/warning_msg_icon.gif') . '" alt="HTTP NOT Authorised" /> ';
//            }
        } else {
            $output = ' <img src="' . $this->getViewFileUrl('Epicor_Common::epicor/common/images/cancel_icon.gif') . '" alt="HTTP NOT Authorised" /> ';
        }
        return $output;
    }

}
