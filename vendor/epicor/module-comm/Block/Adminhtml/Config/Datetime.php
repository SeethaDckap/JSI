<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Config;


class Datetime extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var \Magento\Framework\Data\Form\Element\DateFactory
     */
    protected $formElementDateFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\DateFactory $formElementDateFactory,
        array $data = []
    ) {
        $this->formElementDateFactory = $formElementDateFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $date = $this->formElementDateFactory->create();
        $format = 'yyyy-MM-dd HH:mm:ss';

        $data = array(
            'name' => $element->getName(),
            'html_id' => $element->getId(),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
        );

        $date->setData($data);
        $date->setValue($element->getValue(), $format);
        $date->setFormat($format);
        $date->setTime(true);
        $date->setForm($element->getForm());
        
        $date->setDateFormat('yyyy-MM-dd');
        $date->setTimeFormat('HH:mm:ss');
        $date->setShowsTime(true);

        return $date->getElementHtml();
    }

}
