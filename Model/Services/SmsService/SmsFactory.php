<?php

namespace SR\TwoFactorAuth\Model\Services\SmsService;

use SR\TwoFactorAuth\Model\Source\SmsType;

class SmsFactory
{
    private $_objectManager;
    private $_type;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SmsType $type
    ) {
        $this->_objectManager = $objectManager;
        $this->_type = $type;
    }

    public function create()
    {
        $type = $this->_type->getCurrentType();
        return $this->_objectManager->create("SR\TwoFactorAuth\Model\Services\SmsService\\".$type, []);
    }
}