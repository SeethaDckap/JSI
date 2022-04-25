<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Dcld;


class InformationLayout extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationLayout
{

    protected $_messageBase = 'dealerconnect';
    protected $_messageType = 'dcld';
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

    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td class="" colspan="5">';
        $html .= '<table cellpadding="2" cellspacing="4" style="border: 1px solid burlywood;margin-top: 20px;border-collapse: separate;border-radius: 5px;background: oldlace;"><tr><td>'
            . '<h1 style="padding:0px 0px 0px 10px">'.$this->_sectionTitle.'</h1>'
            . '<table style="margin-left: 10px;">'
            . '<tr><td>'
            . '<select id="address_column" name="address_column" class=" select admin__control-select" data-ui-id="">'
            . '<option value="1" selected="selected">Column</option></select>'
            . '</td>'
            . '<td>'
            . '<select id="address_column_count" name="address_column_count" class=" select admin__control-select" data-ui-id="">'
            . '<option value="2" selected="selected">1</option></select>'
            . '</td>'
            . '<td>'
            . '<select id="address_column_section" name="address_column_section" class=" select admin__control-select" data-ui-id="">'
            . '<option value="2" selected="selected">Information</option></select>'
            . '</td>'
            . '</tr>'
            . '</table></td';
        $html .= '<br>';
        $html .= '<div style="margin-left:10px;margin-top: 12px;">';
        $html .= $this->_getElementHtml($element);
        if ($element->getComment()) {
            //  $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</div>';
        $html .= '</td></tr></table>';
        return $html;
    }
}