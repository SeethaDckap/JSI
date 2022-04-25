<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Spod;


class InformationLayout extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationLayout
{

    protected $_messageBase = 'supplierconnect';
    protected $_messageType = 'spod';
    protected $_allowOptions = true;
    protected $_mappingRenderer;
    protected $_typeRenderer;
    protected $_hiddenRenderer;

    protected $_messageSection = 'information_section';

    protected $_template = 'Epicor_Common::widget/grid/array.phtml';

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutInterface;


    public function getMessageTypes()
    {
        return $this->_messageType;
    }

    public function getMessageSection()
    {
        return $this->_messageSection;
    }


    public function _getMappingRenderer()
    {

        if (!$this->_mappingRenderer) {
            $this->_mappingRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" .
                ucfirst($this->_messageType) . "\\Information\\Mapping",
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );

            $this->_mappingRenderer->setInputName('index')->setClass('rel-to-selected infomappingrenderer');
        }
        return $this->_mappingRenderer;
    }


    public function _getTypeRenderer()
    {
        if (!$this->_typeRenderer) {
            $this->_typeRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" .
                ucfirst($this->_messageType) . "\\Information\\Type",
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_typeRenderer->setInputName('type')->setClass('rel-to-selected infotype');
        }
        return $this->_typeRenderer;

    }


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->layoutInterface = $context->getLayout();
        $this->setHtmlId('_information');

        $this->addColumn('header', array(
            'label' => __('Header'),
            'style' => 'width:190px'
        ));

        $this->addColumn('index', array(
            'label' => __('Mapping'),
            'style' => 'width: 50%;',
            'renderer' => $this->_getMappingRenderer(),
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::__construct(
            $context,
            $data
        );

    }

}