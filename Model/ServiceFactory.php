<?php
/**
 * Copyright © 2019 Studio Raz. All rights reserved.
 * See LICENSE file for license details.
 */

namespace SR\TwoFactorAuth\Model;

use SR\TwoFactorAuth\Model\Source\AuthenticationType;

class ServiceFactory
{
    private $_objectManager;
    private $_type;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        AuthenticationType $type
    ) {
        $this->_objectManager = $objectManager;
        $this->_type = $type;
    }

    public function create()
    {
        $type = $this->_type->getCurrentType() .'Service';
        return $this->_objectManager->create("SR\TwoFactorAuth\Model\Services\\".$type, []);
    }
}