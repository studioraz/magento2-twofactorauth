<?php

namespace SR\TwoFactorAuth\Model\Services;

class SmsService extends \Magento\Framework\Model\AbstractModel implements ServiceInterface
{
    protected $_service;
    protected $result;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SR\TwoFactorAuth\Model\Services\SmsService\SmsFactory $service,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_service = $service;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function execute()
    {
        $smsMessage = __('To login to your account, please enter this verification code: %1. Enjoy Layam.com', $this->getCode());

        $service = $this->_service->create();
        $service->setRecipient($this->getCustomer()->getCustomAttribute('telephone')->getValue())
            ->setMessage($smsMessage->render());
        $this->result = $service->execute();

        return $this;
    }

    public function getResult()
    {
        return $this->result->getResult();
    }

    public function isSuccessResponse()
    {
        return $this->result->isSuccessResponse();
    }
}