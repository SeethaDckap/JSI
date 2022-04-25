<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Comm
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\UrlInterface;

/**
 * Customer address controller.
 */
abstract class CustomerAddress extends Action
{

    /**
     * Form key validator.
     *
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * Customer session.
     *
     * @var Session
     */
    protected $customerSession;

    /**
     * Address repository interface.
     *
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;


    /**
     * CustomerAddress constructor.
     *
     * @param Context                    $context           Context.
     * @param Validator                  $formKeyValidator  Form key validator.
     * @param Session                    $customerSession   Customer session.
     * @param AddressRepositoryInterface $addressRepository Address repository interface.
     * @param UrlInterface               $urlBuilder        Url builder interface.
     */
    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        Session $customerSession,
        AddressRepositoryInterface $addressRepository,
        UrlInterface $urlBuilder
    ) {
        $this->formKeyValidator  = $formKeyValidator;
        $this->customerSession   = $customerSession;
        $this->addressRepository = $addressRepository;
        $this->urlBuilder        = $urlBuilder;
        parent::__construct($context);

    }//end __construct()


}//end class
