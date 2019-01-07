<?php
/**
 * Copyright © 2019 Studio Raz. All rights reserved.
 * See LICENSE file for license details.
 */

namespace SR\TwoFactorAuth\Model\Services\SmsService;


abstract class AbstractService extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Xml\Generator
     */
    protected $generator;

    /**
     * @var \Magento\Framework\Xml\Parser
     */
    protected $parser;

    /**
     * @var \DOMDocument
     */
    protected $response;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Xml\Generator $generator,
        \Magento\Framework\Xml\Parser $parser,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->generator = $generator;
        $this->parser = $parser;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function execute()
    {

        $data = $this->prepareRequest();

        $headers = array(
            "Content-type: text/xml",
            "Connection: close",
            "Content-Length: " . strlen($data)
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->getUrl()); # http://api.soprano.co.il/
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);

        try {
            $this->handleResponse($response);
        } catch (\Exception $e) {
            throw new \Exception('Unicell SMS XML response can\'t be parsed');
        }

        return $this;
    }

    abstract public function prepareRequest();
    abstract public function getUrl();

    abstract protected function handleResponse($response);

    abstract public function isSuccessResponse();

    abstract public function getResponseCode();

    abstract public function getResponseMessage();


}
