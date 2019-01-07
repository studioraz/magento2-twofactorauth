<?php

namespace SR\TwoFactorAuth\Model\Services;

class EmailService extends \Magento\Framework\Model\AbstractModel implements ServiceInterface
{

    protected $result = null;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
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
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function execute()
    {
        $result = [
            'message' => 'success'
        ];

        try {
            $templateVars = [
                'code' => $this->getCode()
            ];
            $emailSender = $this->_scopeConfig->getValue('trans_email/ident_general/email');
            $from = ['email' => $emailSender, 'name' => 'Layam'];
            $this->_inlineTranslation->suspend();
            $to = [
                $this->getEmail()
            ];
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId()
            ];
            $transport = $this->_transportBuilder
                ->setTemplateOptions($templateOptions)
                ->setTemplateIdentifier('verification_code')
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
        }

        $this->result = $result;

        return $this;
    }
    protected function getEmail()
    {
        return $this->getCustomer()->getEmail();
    }

    public function getResult()
    {
        return $this->result;
    }

    public function isSuccessResponse()
    {
        return $this->result['message'] == 'success';
    }
}
