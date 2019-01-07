<?php
/**
 * Copyright © 2019 Studio Raz. All rights reserved.
 * See LICENSE file for license details.
 */

namespace SR\TwoFactorAuth\Plugin\Controller;

use Magento\Store\Model\ScopeInterface;

class AccountLoginPost
{
    public function __construct(
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->resultFactory = $resultFactory;
    }

    public function aroundExecute(\Magento\Customer\Controller\Account\LoginPost $subject, callable $proceed)
    {
        if (!$this->_scopeConfig->getValue('two_factor_auth/general/enabled',
            ScopeInterface::SCOPE_STORE)) {
            return $proceed();
        }
        
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);

        return $resultForward->setModule('twoFactorAuth')->setController('account')->forward('loginPost');
    }
}
