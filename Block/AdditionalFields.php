<?php

namespace SR\TwoFactorAuth\Block;

use SR\TwoFactorAuth\Helper\Config as HelperConfig;

class AdditionalFields extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Customer\Model\Session */
    protected $customerSession;

    /** @var \Magento\Customer\Model\CustomerFactory */
    protected $customerFactory;

    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        HelperConfig $helperConfig,
        array $data
    )
    {
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->helperConfig = $helperConfig;

        parent::__construct($context, $data);
    }

    /**
     * Return the Customer given the customer Id stored in the session.
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if ($customerId = $this->customerSession->getCustomerId()) {
            $customer = $this->customerFactory->create();
            $customer->getResource()->load($customer, $customerId);

            return $customer;
        }

        return new \Magento\Framework\DataObject();
    }


    public function isTelephoneRequired() {
        return $this->helperConfig->isSmsAuthType();
    }

}
