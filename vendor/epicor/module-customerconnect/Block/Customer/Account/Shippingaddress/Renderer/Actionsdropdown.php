<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account\Shippingaddress\Renderer;


use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\PathInfo;
use Magento\Framework\App\Request\PathInfoProcessorInterface;
use Magento\Framework\App\Route\ConfigInterface\Proxy as ConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
use Magento\Framework\Stdlib\StringUtils;

class Actionsdropdown extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Epicor\Customerconnect\Helper\Data $customerconnectHelper
     * @param array $data
     */

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    )
    {
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct(
            $context,
            $jsonEncoder,
            $data
        );
    }


    /**
     * Adding action to dropdown.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {

        $helper = $this->customerconnectHelper;
        $actions = $this->getColumn()->getActions();
        $source = $row->getSource();
        $out = '<select class="admin__control-select" onchange="varienGridAction.execute(this);">'
            . '<option value=""></option>';
        foreach ($actions as $action) {
            if (is_array($action)) {
                $out .= $this->_toOptionHtml($action, $row);
            }
        }
        $out .= '</select>';
        return $out;
    }

}

?>
