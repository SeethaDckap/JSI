<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details;


/**
 * RFQ details - non-editable info block
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Quotes extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_claim_create";
    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_claim_edit';
    const FRONTEND_RESOURCE_CONFIRMREJECT = 'Dealer_Connect::dealer_claim_confirmrejects';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttons;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry = $registry;
        $this->buttons = $context->getButtonList();
        parent::__construct(
            $context,
            $data
        );
        $this->setEmptyText('');
    }

    protected function _setupGrid()
    {
        $this->_controller = 'claims_details_quotes';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        
        $this->_headerText = __('Quotes');

        $showAdd = false;
        //$showAdd = true;
        $action = $this->getRequest()->getActionName();

        if($this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT) &&
            $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE)){
            $showAdd = true;
        }else if($this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT) &&
            !$this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE)){
            $showAdd = true;
        }else if(!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT) &&
            $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE) &&
            $action =='new'
        ){
            $showAdd = true;
        }

        if($showAdd){

            $this->buttons->add(
                'add_quote', array(
                'id' => 'add_quote',
                'label' => __('Add'),
                'class' => 'add',
                'onclick' => 'dealerClaim.newQuote()'
            ), -100
            );
        }
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

    protected function _prepareLayout()
    {
        // this is needed for frontend grid use to stop search options being retained for future users. the omission of calling the parent is intentional
        // as all the processing required when calling parent:: should be included
        $this->setChild( 'grid',
            $this->getLayout()->createBlock(
                str_replace(
                    '_',
                    '\\',
                    $this->_blockGroup
                ) . '\\Block\\' . str_replace(
                    ' ',
                    '\\',
                    ucwords(str_replace('_', ' ', $this->_controller))
                ) . '\\Grid',
                $this->_controller . '.grid'
            )->setSaveParametersInSession(false) );

        $this->toolbar->pushButtons($this, $this->buttons);
        return $this;
    }

    public function getButtonsHtml($region = null)
    {
        $out = '';
        $this->buttonList->remove('add_search');
        $this->buttonList->remove('add_line');
        $this->buttonList->remove('add_contact');
        $this->buttonList->remove('newline_button');
        $this->buttonList->remove('clone_selected');
        $this->buttonList->remove('delete_selected');
        $this->buttonList->remove('add_claim_attachment');
        
        foreach ($this->buttons->getItems() as $buttons) {
            /** @var \Magento\Backend\Block\Widget\Button\Item $item */
            foreach ($buttons as $item) {
                if ($region && $region != $item->getRegion()) {
                    continue;
                }
                $out .= $this->getChildHtml($item->getButtonKey());
            }
        }
        return $out;
    }
    
    
}
