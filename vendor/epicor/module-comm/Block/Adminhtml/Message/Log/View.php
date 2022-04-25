<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Message\Log;


class View extends \Magento\Backend\Block\Widget\Container
{

    private $log = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
        if (!$this->hasData('template')) {
            $this->setTemplate('Magento_Backend::widget/form/container.phtml');
        }
        $this->addButton('back', array(
            'label' => __('Back'),
            'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
            'class' => 'back',
            ), -1);

        $log = $this->getLog();
        if ($log->getMessageParent() == 'Upload') {
            $this->addButton('resubmit', array(
                'label' => __('Reprocess'),
                'onclick' => 'setLocation(\'' . $this->getReprocessUrl() . '\')',
                'class' => 'primary',
                ), -1);
        }
    }

    public function getLog()
    {
        if (empty($this->log)) {
            $this->log = $this->registry->registry('message_log_data');
        }
        return $this->log;
    }

    public function setHeaderText($str)
    {
        $this->_headerText = $str;
    }

    public function getBackUrl()
    {
        $source = $this->registry->registry('message_log_source');
        $param = $this->registry->registry('message_log_sourceparam');
        return $this->getUrl($source, $param);
    }

    public function getReprocessUrl()
    {
        $log = $this->getLog();
        return $this->getUrl('*/*/reprocess', array('id' => $log->getId()));
    }

}
