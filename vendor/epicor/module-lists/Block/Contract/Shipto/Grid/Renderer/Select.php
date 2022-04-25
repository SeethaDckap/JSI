<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Contract\Shipto\Grid\Renderer;

/**
 * Column Renderer for Shipto Select Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Select extends \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action {

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        array $data = []
    ) {
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
         
        parent::__construct(
            $context,
            $jsonEncoder,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row) {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        if ($this->getColumn()->getLinks() == true) {

            $contractHelper = $this->listsFrontendContractHelper;
            /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
            $shipto = $contractHelper->getSelectedContractShipto();
            if ($row->getEccErpAddressCode() === $shipto) {
                $html = __('Currently Selected');
            } else {
                foreach ($actions as $action) {
                    if (is_array($action)) {
                        $params = [
                            'shipto' => $row->getData($this->getColumn()->getIndex())
                        ];
                        
                        $url = $this->_urlBuilder->getRouteUrl($action['url']['base'], $params);
                        
                        $html = '<a href="' . $url . '">' . $action['caption']->getText() . '</a>';
                    }
                }
            }

            return $html;
        } else {
            return parent::render($row);
        }
    }

}
