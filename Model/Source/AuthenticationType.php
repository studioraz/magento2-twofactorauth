<?php
/**
 * Copyright © 2019 Studio Raz. All rights reserved.
 * See LICENSE file for license details.
 */

namespace SR\TwoFactorAuth\Model\Source;

class AuthenticationType implements \Magento\Framework\Option\ArrayInterface
{
    const AUTH_TYPE_EMAIL = 1;
    const AUTH_TYPE_SMS = 2;

    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::AUTH_TYPE_EMAIL => __('Email'),
            self::AUTH_TYPE_SMS => __('SMS')
        ];

        return $options;
    }
    public function getCurrentType(){
        switch ($this->_scopeConfig->getValue('two_factor_auth/general/auth_type')){
            case self::AUTH_TYPE_EMAIL:
                return 'Email';
            case self::AUTH_TYPE_SMS:
                return 'Sms';
            default:
                return 'Email';
        }
    }
}