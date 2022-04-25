<?php
namespace Epicor\Customerconnect\Plugin\Arpayments;

class DisableArpaymentsHeaderFooter{

        /**
         * @var \Magento\Framework\Registry
         */
        protected $_registry;
        protected $_request;

        public function __construct(
           \Magento\Framework\Registry $registry,
            \Magento\Framework\App\Request\Http $request)
        {
            $this->_registry = $registry;
            $this->_request = $request;
        }
    /**
    * Using after method change  return value of toHtml
    */
    public function afterToHtml(\Magento\Framework\View\Element\AbstractBlock $block ,$result){
            $handle = $this->_request->getFullActionName();
            $moduleName = $block->getModuleName();
            $nameInLayout = $block->getNameInLayout();
            $_disableBlocksNameInLayout = array(
                 'copyright','logo','page.main.title','dealer_extra',
             );
            /** 
            * If block name is match then return Blank
            */
            if($handle =="customerconnect_arpayments_archeckout") {  
                if(in_array($nameInLayout ,$_disableBlocksNameInLayout)){
                return "";
                }
            }
           return $result;
     
    }

}