<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Controller;


/**
 * Faqs frontend controller
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 * 
 */
abstract class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Epicor\Faqs\Helper\Data
     */
    protected $faqsHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Faqs\Helper\Data $faqsHelper
    ) {
        $this->faqsHelper = $faqsHelper;
        parent::__construct(
            $context
        );
    }


    /**
     * Pre dispatch action that allows to redirect to no route page in case of disabled extension through admin panel
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->faqsHelper->isEnabled()) {
            $this->setFlag('', 'no-dispatch', true);
            $this->_redirect('noRoute');
        }
    }
}
