<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Contract\Select\Grid\Renderer;


/**
 * Column Renderer for Contract Select Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Select extends \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action
{

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
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        if ($this->getColumn()->getLinks() == true) {

            $contractHelper = $this->listsFrontendContractHelper;
            /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
            $selectedContract = $contractHelper->getSelectedContract();

            $html = '';
            $params = [
                            'contract' => $row->getData($this->getColumn()->getIndex())
                       ];
            if ($row->getId() == $selectedContract) {
                $html .= __('Currently Selected');
                foreach ($actions as $action) {
                    if (is_array($action)) {
                        if (($html != '') && ($action['caption'] != 'Select')) {
                            $url = $this->_urlBuilder->getRouteUrl($action['url']['base'], $params);
                            $html .= '<span class="action-divider">' . ($this->getColumn()->getDivider() ?: ' | ') . '</span>';
                            $html .= '<a rel=nofollow id="'.$action['id'].'" onclick="'.$action['onclick'].'" href="'.$url.'" rel=nofollow>'.$action['caption']->getText().'</a>';
                        }
                    }
                }
            } else {
                foreach ($actions as $action) {
                    if (is_array($action)) {
                        if ($html != '') {
                            $html .= '<span class="action-divider">' . ($this->getColumn()->getDivider() ?: ' | ') . '</span>';
                        }
                        $url = $this->_urlBuilder->getRouteUrl($action['url']['base'], $params);
                        
                        if ($action['caption'] == 'Select'){
                            $onclick = '';    
                            if(isset($action['confirm'])) {
                                $onclick = 'onclick= "return window.confirm(\''
                                                   . addslashes($this->escapeHtml($action['confirm']))
                                                   . '\')"';
                            }
                            $html .= '<a href="'.$url.'" '.$onclick.' >'.$action['caption']->getText().'</a>';        
                        } else {
                            $html .= '<a id="'.$action['id'].'" onclick="'.$action['onclick'].'" href="'.$url.'" rel=nofollow >'.$action['caption']->getText().'</a>';
                        }
                                
                    }
                }
            }
            return $html;
        } else {
            return parent::render($row);
        }
    }

}
