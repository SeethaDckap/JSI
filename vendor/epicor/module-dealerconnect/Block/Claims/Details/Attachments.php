<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details;


/**
 * Claim details attachments grid container
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Attachments extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_claim_create";
    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_claim_edit';
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
        $this->setBoxClass('col-2');
        $this->setTemplate('Epicor_Dealerconnect::claims/details/container-boxed.phtml');
        $this->setEmptyText('Add Attachments for Claim');
    }

    protected function _setupGrid()
    {
        $this->_controller = 'claims_details_attachments';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('Add Attachments');

        $showAdd = false;
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
                'add_claim_attachment', array(
                'id' => 'add_claim_attachment',
                'label' => __('Add'),
                'class' => 'add',
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
        $this->buttonList->remove('add_attachment');

        foreach ($this->buttons->getItems() as $buttons) {
            /** @var \Magento\Backend\Block\Widget\Button\Item $item */
            foreach ($buttons as $item) {
                if (($region && $region != $item->getRegion()) || ($item->getId() == "add_quote")) {
                    continue;
                }
                $out .= $this->getChildHtml($item->getButtonKey());
            }
        }
        return $out;
    }

}
