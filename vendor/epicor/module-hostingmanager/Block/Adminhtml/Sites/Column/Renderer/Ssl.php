<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Sites\Column\Renderer;


/**
 * Renderer for Sites > Stores column, shows list of stores for the site
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Ssl extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\HostingManager\Model\Certificate
     */
    protected $hostingManagerCertificate;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\HostingManager\Model\Certificate $hostingManagerCertificate,
        array $data = []
    ) {
        $this->hostingManagerCertificate = $hostingManagerCertificate;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $cert_id = $row->getData($this->getColumn()->getCertId());
        if ($cert_id) {
            $cert = $this->hostingManagerCertificate->load($cert_id);
            /* @var $cert Epicor_HostingManager_Model_Certificate */
            if ($cert->isValidCertificate()) {
                $output = ' <img src="' . $this->getSkinUrl('images/success_msg_icon.gif') . '" alt="SSL Certificate Ready" /> ';
            } else {
                $output = ' <img src="' . $this->getSkinUrl('images/warning_msg_icon.gif') . '" alt="SSL Certificate Not Ready" /> ';
            }
            if ($this->getColumn()->getShowName())
                $output .= $cert->getName();
        } else {
            $output = ' <img src="' . $this->getSkinUrl('images/cancel_icon.gif') . '" alt="No SSL Certificate" /> ';
        }
        return $output;
    }

}
