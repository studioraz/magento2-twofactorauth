<?php
/**
 * Copyright © 2019 Studio Raz. All rights reserved.
 * See LICENSE file for license details.
 */

namespace SR\TwoFactorAuth\Controller\Account;

use SR\TwoFactorAuth\Model\Source\AuthenticationType;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;


class LoginPost extends \Magento\Customer\Controller\AbstractAccount
{
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var Validator */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \SR\TwoFactorAuth\Model\ServiceFactory
     */
    private $service;

    /**
     * @var \SR\TwoFactorAuth\Helper\Config
     */
    private $configHelper;

    /**
     * LoginPost constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \SR\TwoFactorAuth\Model\ServiceFactory $service
     * @param \SR\TwoFactorAuth\Helper\Config $configHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        \SR\TwoFactorAuth\Model\ServiceFactory $service,
        \SR\TwoFactorAuth\Helper\Config $configHelper

    )
    {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_storeManager = $storeManager;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;

        $this->scopeConfig = $scopeConfig;
        $this->service = $service;

        $this->configHelper = $configHelper;

        parent::__construct($context);
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $message = null;
        $redirectUrl = null;
        $response = [];
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            $redirectUrl = $this->_url->getUrl('customer/account');
        } else {
            $this->session->unsPreAuthData();
            $response = [
                'status' => 'error'
            ];

            if ($this->getRequest()->isPost()) {
                $login = $this->getRequest()->getPost('login');

                if (!empty($login['username']) && !empty($login['password'])) {
                    try {

                        $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);

                        $code = $this->_getGeneratedCode();

                        $service = $this->service->create()->setCode($code)->setCustomer($customer)->execute();
                        $isSuccess = $service->isSuccessResponse();

                        if ($isSuccess) {
                            $this->session->setPreAuthData([
                                'customer_id' => $customer->getId(),
                                'timestamp' => time(),
                                'code' => $code
                            ]);
                            $response['status'] = 'success';
                        } else {
                            $message = __('Something went wrong. Please try again or contact us.');
                            $templateVars = [
                                'failure_response' => print_r($service->getResult(), true)
                            ];
                            $from = ['email' => $this->scopeConfig->getValue('two_factor_auth/general/error_email'), 'name' => 'Layam'];
                            $this->_inlineTranslation->suspend();
                            $to = [
                                $this->scopeConfig->getValue('two_factor_auth/general/error_email')
                            ];
                            $templateOptions = [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                'store' => $this->_storeManager->getStore()->getId()
                            ];
                            $transport = $this->_transportBuilder
                                ->setTemplateOptions($templateOptions)
                                ->setTemplateIdentifier('auth_failed')
                                ->setTemplateVars($templateVars)
                                ->setFrom($from)
                                ->addTo($to)
                                ->getTransport();
                            $transport->sendMessage();
                            $this->_inlineTranslation->resume();
                        }
                    } catch (EmailNotConfirmedException $e) {
                        $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                        $message = __(
                            'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                            $value
                        );
                        $this->session->setUsername($login['username']);
                    } catch (UserLockedException $e) {
                        $message = __(
                            'The account is locked. Please wait and try again or contact %1.',
                            $this->scopeConfig->getValue('contact/email/recipient_email')
                        );
                        $this->session->setUsername($login['username']);
                    } catch (AuthenticationException $e) {
                        $message = __('Invalid login or password.');
                        $this->session->setUsername($login['username']);
                    } catch (LocalizedException $e) {
                        $message = $e->getMessage();
                        $this->session->setUsername($login['username']);
                    } catch (\Exception $e) {
                        // PA DSS violation: throwing or logging an exception here can disclose customer password
                        $message =
                            __('An unspecified error occurred. Please contact us for assistance.');
                    }
                } else {
                    $message = __('A login and a password are required.');
                }
            }
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

    protected function _getService()
    {
        if ($this->scopeConfig->getValue('two_factor_auth/general/auth_type') == AuthenticationType::AUTH_TYPE_EMAIL) {
            $result = $this->_objectManager->create('SR\TwoFactorAuth\Model\EmailService');
        } else {
            $result = $this->_objectManager->create('SR\UnicellSms\Model\Service');
        }

        return $result;
    }

    protected function _getGeneratedCode()
    {
        return mt_rand(1000, 9999);
    }
}
