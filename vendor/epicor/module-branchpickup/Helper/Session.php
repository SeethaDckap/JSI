<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Helper;


/**
 * Branch Helper
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Session extends \Epicor\Lists\Helper\Session
{

    private $knownKeys = array(
        'ecc_selected_branchpickup'
    );

    /**
     * Gets a value from the session
     *
     * @param string $key
     *
     * @return mixed
     */
    public function clear()
    {
        foreach ($this->knownKeys as $key) {
            $this->getSession()->unsetData($key);
        }
    }

}
