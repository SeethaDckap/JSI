<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\SalesRep\Plugin;

/**
 * Description of RuleComponentImagePath
 *
 * @author ashwani.arya
 */
class RuleComponentImagePath {
    
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;
    
    protected $request;
    
     public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Request\Http $request     
    ) {
        $this->_assetRepo = $assetRepo;
        $this->request = $request;
    }
    
    /**
     * Change Rule component  image path if PriceRule condition field  access from frontend
     *
     * @return string
     */
    public function afterGetAddLinkHtml(\Magento\Rule\Model\Condition\AbstractCondition $subject, $result)
    {
        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        
        if ($moduleName=='salesrep' && $controller == 'account_manage'){
            $src = $this->_assetRepo->getUrl('Epicor_SalesRep::epicor/salesrep/images/rule_component_add.gif');
            $result = '<img src="' . $src . '" class="rule-param-add v-middle" alt="" title="' . __('Add') . '"/>';
        }
        
        return $result;
    }
    
    /**
     * Change Rule component  remove image path if PriceRule condition field  access from frontend
     *
     * @return array
     */
    /*
    public function afterGetRemoveLinkHtml(\Magento\Rule\Model\Condition\AbstractCondition $subject, $result)
    {
        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        
        if ($moduleName=='salesrep' && $controller == 'account_manage'){
            $src = $this->_assetRepo->getUrl('Epicor_SalesRep::epicor/salesrep/images/rule_component_remove.gif');
            $result = '<span class="rule-param"><a href="javascript:void(0)" class="rule-param-remove"><img src="' .
                $src .
                '" alt="" class="v-middle" /></a></span>';
         } 
        return $result;
    } */
}
