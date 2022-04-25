<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Customfields\Renderer;


/**
 * Customer Account Type Grid Renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Message extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $globalConfig;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        array $data = []
    ) {
        $this->globalConfig = $globalConfig;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Render country grid column
     *
     * @param   \Epicor\Comm\Model\Location\Product $row
     * 
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $status = $row->getMessage();
        $request = (array) $this->globalConfig->get('mapping_xml_request/messages');
        //M1 > M2 Translation End
        $_upload = array();
        foreach ($request as $key => $request) {
            $_upload[$key] = $request;
        }
        return $_upload;
    }

}
