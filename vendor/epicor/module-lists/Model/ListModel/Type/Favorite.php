<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Type;


/**
 * Type Class for Favorites
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Favorite extends \Epicor\Lists\Model\ListModel\Type\AbstractModel
{

    protected $supportedSettings = array(
        'D',
        'Q',
    );
    protected $visibleSections = array(
        'erpaccounts',
        'customers',
        'products',
    );

}
