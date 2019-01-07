<?php

namespace SR\TwoFactorAuth\Model\Source;

class SmsType  extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\Option\ArrayInterface
{
    const AUTH_TYPE_UNISELL = 1;
    const AUTH_TYPE_NASKYOO = 2;

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
            self::AUTH_TYPE_UNISELL => __('Unicell'),
            self::AUTH_TYPE_NASKYOO => __('Naskyoo')
        ];

        return $options;
    }
    public function getCurrentType(){
        switch ($this->_scopeConfig->getValue('two_factor_auth/general/sms_provider')){
            case self::AUTH_TYPE_UNISELL:
                return 'Unicell';
            case self::AUTH_TYPE_NASKYOO:
                return 'Naskyoo';
            default:
                return 'Unicell';
        }
    }
}