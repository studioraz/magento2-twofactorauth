<?php

namespace SR\TwoFactorAuth\Helper;

use SR\TwoFactorAuth\Model\Source\AuthenticationType;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;


class Config extends AbstractHelper {


    const XML_PATH_VERIFICATION_TOOLTIP_TEXT = 'two_factor_auth/general/verification_tooltip_text';

    const XML_PATH_ENABLED = 'two_factor_auth/general/enabled';

    const XML_PATH_SIGN_IN_FORM_NOTE = 'two_factor_auth/general/sign_in_form_note';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


    public function __construct(
        Context $context
    ){
        parent::__construct($context);
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig */
        $this->scopeConfig = $context->getScopeConfig();
    }


    public function getAuthType() {
        return $this->scopeConfig->getValue('two_factor_auth/general/auth_type',
            ScopeInterface::SCOPE_STORE);
    }


    public function isEmailAuthType() {
        return $this->getAuthType() == AuthenticationType::AUTH_TYPE_EMAIL;
    }

    public function isSmsAuthType() {
            return $this->getAuthType() == AuthenticationType::AUTH_TYPE_SMS;
    }

    public function getVerificationTooltipText() {
        return $this->scopeConfig->getValue(self::XML_PATH_VERIFICATION_TOOLTIP_TEXT,
            ScopeInterface::SCOPE_STORE);
    }

    public function isEnabled() {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_WEBSITE);
    }

    public function getSignInFormNote() {
        return $this->scopeConfig->getValue(self::XML_PATH_SIGN_IN_FORM_NOTE,
            ScopeInterface::SCOPE_STORE);
    }
}
