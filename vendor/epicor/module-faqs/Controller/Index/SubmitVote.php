<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Controller\Index;

class SubmitVote extends \Epicor\Faqs\Controller\Index
{

    /**
     * @var \Epicor\Faqs\Helper\Data
     */
    protected $faqsHelper;

    /**
     * @var \Epicor\Faqs\Model\VoteFactory
     */
    protected $faqsVoteFactory;

    /**
     * @var \Epicor\Faqs\Model\FaqsFactory
     */
    protected $faqsFaqsFactory;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Faqs\Helper\Data $faqsHelper,
        \Epicor\Faqs\Model\VoteFactory $faqsVoteFactory,
        \Epicor\Faqs\Model\FaqsFactory $faqsFaqsFactory)
    {
        $this->faqsVoteFactory = $faqsVoteFactory;
        $this->faqsFaqsFactory = $faqsFaqsFactory;
        parent::__construct($context, $faqsHelper);
    }


    /**
     * voteAjax action
     * 
     * 1. Checks wether the user has already submitted a vote for the voted F.A.Q.
     * 
     * 2a. If the user had never submitted a vote for the selected F.A.Q.: 
     *  i.Register the vote in the ecc_faq_votes table
     *  ii.Add the vote value to the corresponding column in the epicor_faqs table.
     * 
     * 2b.If the user has previously submitted a vote for the selected F.A.Q., but
     *  the submitted value is different from the existing one:
     *  i.Modify the vote value in the ecc_faq_votes table
     *  ii.Modify the corresponding columns in epicor_faqs
     */
    public function execute()
    {
        $msg = "";
        $post = $this->getRequest();
        if (empty($post)) {
            $msg = "Request Error";
        } else {
            $model_vote = $this->faqsVoteFactory->create();
            $voted_registered = false;
            $changed_vote = false;

            $collection = $model_vote->getCollection()->addFilter('faqs_id', $post->getParam('faqId'))->addFilter('customer_id', $this->faqsHelper->getUserId());
            if ($collection->getSize() > 0) {
                $model_vote->setId($collection->getFirstItem()->getId());
                $voted_registered = true;
                $changed_vote = $collection->getFirstItem()->getValue() != $post->getParam('vote');
            }

            $model_vote->setFaqsId($post->getParam('faqId'));
            $model_vote->setCustomerId($this->faqsHelper->getUserId());
            $model_vote->setValue($post->getParam('vote'));
            $model_vote->save();

            if (!$voted_registered || $changed_vote) {
                $model_faq = $this->faqsFaqsFactory->create();
                $model_faq->load($post->getParam('faqId'));
                if ($changed_vote) {
                    if ($post->getParam('vote') > 0) {
                        if ($model_faq->getUseless() > 0) {
                            $model_faq->setUseless($model_faq->getUseless() - 1);
                        }
                    } else {
                        if ($model_faq->getUseful() > 0) {
                            $model_faq->setUseful($model_faq->getUseful() - 1);
                        }
                    }
                }
                if ($post->getParam('vote') > 0) {
                    $model_faq->setUseful($model_faq->getUseful() + 1);
                } else {
                    $model_faq->setUseless($model_faq->getUseless() + 1);
                }
                $model_faq->save();
            }

            $msg = "Voted!";
        }
        //This is the tricky part for the output of a json message to the browser   

        //M1 > M2 Translation Begin (Rule p2-4)
        //$this->_redirectUrl(Mage::getUrl('epicor_faqs/index/vote', array('id' => $post->getParam('faqId'))));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($this->_url->getUrl('epicor_faqs/index/vote', array('id' => $post->getParam('faqId'))));

        //M1 > M2 Translation End
    }

    }
