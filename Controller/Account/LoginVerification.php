<?php

namespace SR\TwoFactorAuth\Controller\Account;

class LoginVerification extends \Magento\Framework\App\Action\Action
{
    protected $scopeConfig;
    protected $cookieMetadataManager;
    protected $cookieMetadataFactory;
    protected $session;
    protected $accountRedirect;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Account\Redirect $accountRedirect,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->session = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->accountRedirect = $accountRedirect;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    public function execute()
    {
        $response = [];
        $redirectUrl = null;
        $message = null;
        $loginPageUrl = $this->_url->getUrl('customer/account/login');
        if ($this->session->isLoggedIn()) {
            $redirectUrl = $this->_url->getUrl('customer/account');
        } else if ($this->getRequest()->isPost()) {
            $code = $this->getRequest()->getPost('code');
            $data = $this->session->getPreAuthData();
            $timerange = $this->getScopeConfig()->getValue('two_factor_auth/general/time_range');
            if (empty($data) || !array_key_exists('code', $data) || !array_key_exists('customer_id', $data)) {
                $message = __('Please start login process again.');
                $this->session->unsPreAuthData();
                $redirectUrl = $loginPageUrl;
            } else if (time() - $data['timestamp'] > $timerange) {
                $message = __('%1 seconds have passed. Please start login process again.', $timerange);
                $redirectUrl = $loginPageUrl;
            } else if ($code == $data['code']) {
                $customer = $this->customerRepository->getById($data['customer_id']);
                $this->session->setCustomerDataAsLoggedIn($customer);
                $this->session->regenerateId();
                if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                    $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                }
                $redirectUrl = $this->accountRedirect->getRedirectCookie();
                if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                    $this->accountRedirect->clearRedirectCookie();
                    $redirectUrl = $this->_redirect->success($redirectUrl);
                }
                if (!$redirectUrl) {
                    $redirectUrl = $this->_url->getUrl('customer/account');
                }
            } else {
                $data['failed_attempts'] = array_key_exists('failed_attempts', $data)
                    ? $data['failed_attempts'] + 1
                    : 1
                ;
                $this->session->setPreAuthData($data);
                if ($data['failed_attempts'] >= $this->getScopeConfig()->getValue('two_factor_auth/general/attempts_number')) {
                    $redirectUrl = $loginPageUrl;
                    $this->session->unsPreAuthData();
                    $message = __('Too many failure attempts. Please start login process again.');
                } else {
                    $message = __('You\'ve entered a wrong code. Please try again.');
                }
            }
        } else {
            $redirectUrl = $loginPageUrl;
        }

        if ($message) {
            $this->messageManager->addErrorMessage($message);
        }

        if ($redirectUrl) {
            $response['redirect_url'] = $redirectUrl;
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($response);
    }

    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }
}
