<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block\Pickupsearch\Select\Grid\Renderer;


/**
 * Column Renderer for Branchpickup Select Grid
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Select extends \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;
    
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;    

    public function __construct(
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->branchPickupHelper = $branchPickupHelper;
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
        $this->directoryCountryFactory = $directoryCountryFactory;
        $this->url = $url;
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        if ($this->getColumn()->getLinks() == true) {
            $branchHelper = $this->branchPickupHelper;
            /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
            $getSelectedBranch = $branchHelper->getSelectedBranch();

            $html = '';
            
            $html .='<div id="branch-pickup-search-grid"></div>';
            
            $html .= '<div id="branchpickup-search-iframe-popup-modal_'.$row->getData('id').'"><div id="show-branch-search-cartpopup_'.$row->getData('id').'"></div></div>';
            
            $address1 = $row->getData('address1');
            $address2 = $row->getData('address2');
            $address3 = $row->getData('address3');
            $address = array($address1,$address2,$address3);
            $emptystreet = false;
            if(!$address1 && !$address2 && !$address3) {
                $emptystreet= true;
            }            
            $error = 0;
            if($emptystreet || !$row->getCity() || !$row->getPostcode() || !$row->getCountry() || !$row->getTelephoneNumber()) {
               $error = 1; 
            }     
            
            if(!empty($row->getCountry())) {
                $stateArray = $this->directoryCountryFactory->create()->setId($row->getCountry())->getLoadedRegionCollection()->toOptionArray(); 
                if((!empty($stateArray)) && (!($row->getCountyCode()))) {
                   $error = 1; 
                }
            }            

            if ($row->getCode() == $getSelectedBranch) {
                $html .= __('Currently Selected');
                foreach ($actions as $action) {
                    if (is_array($action)) {
                        if (($html != '') && ($action['caption'] != 'Select')) {
                            $locationCode = $row->getData($this->getColumn()->getIndex());
                            $locationId = $row->getData('id');
                            $html .= '<span class="action-divider">' . ($this->getColumn()->getDivider() ?: ' | ') . '</span>';
                            $html .= '<a data-customid="'.$locationId.'"  data-custom="'.$locationCode.'" class="selectBranchPopuplink" id="selectBranchPopuplink" href="#">'.$action['caption']->getText().'</a>';
                            if($error) {
                               $html .= '<input type="hidden" value="'.$locationCode.'" id="errorBranchlink_'.$locationId.'" class="errorBranchlink" >'; 
                            }
                        }
                    }
                }
            } else {
                foreach ($actions as $action) {
                    if (is_array($action)) {
                        if ($html != '') {
                            //$html .= '<span class="action-divider">' . ($this->getColumn()->getDivider() ?: ' | ') . '</span>';
                        }
                        $locationCode = $row->getData($this->getColumn()->getIndex());
                        $locationId = $row->getData('id');
                        $showpopuperror="1";
                        if($error) {
                           $showpopuperror = "2";
                        }                         
                        $javascript = "populateBranchAddressSelect('".$locationId."','".$locationCode."','".$showpopuperror."')";
                        $html .= '<a data-custom="'.$locationCode.'" onclick="'.$javascript.'" data-customid="'.$locationId.'" id="selectBranchPopuplink" class="selectBranchPopuplink" href="#">'.$action['caption']->getText().'</a>';
                    }
                }
            }
            return $html;
        } else {
            return parent::render($row);
        }
    }

}