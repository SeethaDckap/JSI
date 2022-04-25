<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\Adminhtml\Quotes\Edit;


class Commenthistory extends \Epicor\Quotes\Block\Adminhtml\Quotes\Edit\AbstractBlock
{

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = [])
    {
        $this->backendHelper = $backendHelper;

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

    public function getPublishNoteUrl($note)
    {
        return $this->backendHelper->getUrl(
                "epicorquotes/quotes_quotes/publishnote/", array('id' => $note->getId())
        );
    }

    public function getNewNoteUrl()
    {
        return $this->backendHelper->getUrl(
                "epicorquotes/quotes_quotes/submitnewnote/", array('id' => $this->getQuote()->getId())
        );
    }

    public function getCommentStateUrl($note)
    {
        return $this->backendHelper->getUrl(
                "epicorquotes/quotes_quotes/changenotestate/", array('id' => $note->getId())
        );
    }

}
