<?php
namespace SR\TwoFactorAuth\Model\Services\SmsService;

class Unicell extends AbstractService
{
    const RESPONSE_CODE_SUCCESS = '0';
    const RESPONSE_CODE_INVALID_CREDENTIALS = '203';
    const RESPONSE_CODE_MISSING_CELLPHONE = '341';

    /**
     * @var mixed;
     */
    private $_recipients = [];

    /**
     * @return string
     */
    public function prepareRequest()
    {


        $this->generator->setIndexedArrayItemName('cellphone');

        $request = [
            'sms' => [
                'account' => [
                    'id' => $this->_scopeConfig->getValue('two_factor_auth/general/api_username'),
                    'password' => $this->_scopeConfig->getValue('two_factor_auth/general/api_password')
                ],
                'attributes' => [
                    'replyPath' => $this->_scopeConfig->getValue('two_factor_auth/general/from_cellphone')
                ],
                'schedule' => [
                    'relative' => '0'

                ],
                'targets' => $this->getCellPhones(),

                'data' => $this->getMessage()
            ]
        ];

        $xmlRequest = $this->generator->arrayToXml($request);

        return (string)$xmlRequest;
    }

    public function getUrl()
    {
        return $this->_scopeConfig->getValue('two_factor_auth/general/api_gateway_url');
    }

    /**
     * @param $response
     */
    protected function handleResponse($response)
    {
        $xmlParser = $this->parser->loadXML($response);
        $this->response = $xmlParser->getDom();
    }

    /**
     * @return string
     */
    public function getResponseCode()
    {
        return $this->response->getElementsByTagName('code')->item(0)->nodeValue;
    }

    /**
     * @return string
     */
    public function getResponseMessage()
    {
        return $this->response->getElementsByTagName('message')->item(0)->nodeValue;
    }

    /**
     * @return bool
     */
    public function isSuccessResponse()
    {
        return $this->getResponseCode() == self::RESPONSE_CODE_SUCCESS
            || strtolower($this->getResponseMessage()) == 'success';
    }

    public function setRecipient($recipient)
    {
        $this->setRecipients([$recipient]);

        return $this;
    }
    /**
     * @param $recipients
     * @return $this
     */
    public function setRecipients($recipients)
    {

        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $this->_recipients = $recipients;

        return $this;
    }

    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->_recipients;
    }

    public function formatCellPhone($number)
    {
        return preg_replace('/^0(.*)$/', '972$1', $number);
    }

    private function getCellPhones()
    {

        $cellphones = [];

        foreach (array_map(array($this, 'formatCellPhone'), $this->_recipients) as $recipient) {
            $cellphones[] = $recipient;
        }

        return $cellphones;
    }

    public function getResult(){
        return [
            'status' => $this->getResponseCode(),
            'message' => $this->getResponseMessage()
        ];
    }


}
