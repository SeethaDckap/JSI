<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Epicor\Punchout\Api\ConnectionsRepositoryInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class GenericElement extends Generic
{

    /**
     * Connection repository interface.
     *
     * @var ConnectionsRepositoryInterface
     */
    protected $connectionRepository;


    /**
     * Rules constructor.
     *
     * @param Context                        $context              Context.
     * @param Registry                       $registry             Registry.
     * @param FormFactory                    $formFactory          Form factory.
     * @param ConnectionsRepositoryInterface $connectionRepository Connection repo.
     * @param array                          $data                 Data array.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ConnectionsRepositoryInterface $connectionRepository,
        array $data=[]
    ) {
        $this->connectionRepository = $connectionRepository;
        parent::__construct($context, $registry, $formFactory, $data);

    }//end __construct()


}//end class

