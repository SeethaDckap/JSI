<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Generic;


/**
 * Generic list functionality
 * 
 * Used by modules to create grids from message data
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 * 
 * @method setBoxed(boolean)
 * @method getBoxed
 * @method setBoxClass(string)
 * @method getBoxClass
 */
class Listing extends \Magento\Backend\Block\Widget\Grid\Container
{


    const FRONTEND_RESOURCE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_CREATE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_EDIT = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const ACCESS_MESSAGE_DISPLAY = FALSE;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $data
        );
        $this->_setupGrid();
        $this->_postSetup();
        if ($this->getBoxed()) {
            //M1 > M2 Translation Begin (Rule 57)
            //$this->setTemplate('widget/grid/container-boxed.phtml');
            $this->setTemplate('Epicor_Common::widget/grid/container-boxed.phtml');
            //M1 > M2 Translation End
        } else {
            $this->setTemplate('Epicor_Common::widget/grid/container.phtml');
        }
    }

    /**
     * Do any pre-construct stuff here
     */
    protected function _setupGrid()
    {
        $this->_controller = '';
        $this->_blockGroup = '';
        $this->_headerText = __('Generic list');
    }

    /**
     * Do any post construct stuff here
     */
    protected function _postSetup()
    {
        $this->removeButton('add');
    }
    public function toHtml()
    {
        if(!$this->_isAllowed()) {
            if(static::ACCESS_MESSAGE_DISPLAY){
                return $this->_accessauthorization->getMessage();
            }
            return '';
         }
        return parent::toHtml();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE);
    }

   /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }

    /*
     * This replaces the adminhtml/block/widget/grid/container.php which sets setSaveParametersInSession to true
     */
    protected function _prepareLayout()
    {
        // this is needed for frontend grid use to stop search options being retained for future users. the omission of calling the parent is intentional
        // as all the processing required when calling parent:: should be included
        if( !$this->getLayout()->getBlock($this->_controller . '.grid')){
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

        $this->toolbar->pushButtons($this, $this->buttonList);
        }
        return $this;
    }

    public function _isFormAccessAllowed()
    {
        $allowed = true;
        $action = $this->getRequest()->getActionName();
        switch($action) {
            case 'new':
            case 'duplicate':
                $allowed = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE);
                break;
            case 'details':
                $allowed = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT);
                break;
        }
        return $allowed;
    }

}
