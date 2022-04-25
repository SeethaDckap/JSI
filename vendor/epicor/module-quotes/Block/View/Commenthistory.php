<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\View;


class Commenthistory extends \Epicor\Quotes\Block\View\AbstractBlock
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        parent::__construct($context, $registry, $data);
    }
    /**
     * 
     * @param \Epicor\Quotes\Model\Quote\Note $note
     * @return \Magento\User\Model\User|\Magento\Customer\Model\Customer
     */
    public function getNoteUser($note)
    {
        if ($note->getIsFormatted()) {
            $userInfo = $note;
        } else if ($note->isAdminNote()) {
            $userInfo = $note->getAdmin();
        } else {
            $userInfo = $this->getQuote()->getCustomer();
        }

        return $userInfo;
    }

    public function getNewNoteUrl()
    {
        return $this->getUrl('epicor_quotes/manage/newnote', array('id' => $this->getQuote()->getId()));
    }

}
