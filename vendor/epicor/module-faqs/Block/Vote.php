<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Block;


/**
 * Front-end Faqs Vote block
 * 
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 */
class Vote extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Epicor\Faqs\Model\FaqsFactory
     */
    protected $faqsFaqsFactory;
    protected $faqId;

    /**
     * @var \Epicor\Faqs\Model\ResourceModel\Vote\CollectionFactory
     */
    protected $faqsResourceVoteCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Faqs\Model\FaqsFactory $faqsFaqsFactory,
        \Epicor\Faqs\Model\ResourceModel\Vote\CollectionFactory $faqsResourceVoteCollectionFactory,
        array $data = []
    ) {
        $this->faqsFaqsFactory = $faqsFaqsFactory;
        $this->faqsResourceVoteCollectionFactory = $faqsResourceVoteCollectionFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function getFaqItem($faqId)
    {
        $model_faq = $this->faqsFaqsFactory->create();
        $model_faq->load($faqId)->getResource();
        return $model_faq;
    }

    public function getMessageVote($faqId)
    {
        $collection = $this->faqsResourceVoteCollectionFactory->create();
        $collection->getSelect()
            ->columns(array(
                'voted_yes' => 'SUM(IF(value > 0, 1, 0))',
                'voted' => 'COUNT(*)'
            ))
            ->where("faqs_id = '{$faqId}'")
            ->group('faqs_id');

        $voted = $collection->getSize() > 0 ? $collection->getFirstItem()->getVoted() : 0;
        $voted_yes = $collection->getSize() > 0 ? $collection->getFirstItem()->getVotedYes() : 0;
        return sprintf(__('%s of %s voted this as helpful'), $voted_yes, $voted);
    }
    public function setFaqId($faqId)
    {
        $this->faqId = $faqId;
    }
    public function getFaqId()
    {
        return $this->faqId;
    }
  
}
