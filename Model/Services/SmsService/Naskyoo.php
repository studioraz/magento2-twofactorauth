<?php
/**
 * Copyright © 2019 Studio Raz. All rights reserved.
 * See LICENSE file for license details.
 */

namespace SR\TwoFactorAuth\Model\Services\SmsService;


class Naskyoo extends AbstractService
{

    const RESPONSE_CODE_SUCCESS = '200';
    const RESPONSE_CODE_INVALID_CREDENTIALS = '203';
    const RESPONSE_CODE_MISSING_CELLPHONE = '341';

    protected $info;

    public function prepareRequest()
    {
        $sender = $this->_scopeConfig->getValue('two_factor_auth/general/from_cellphone');
        $sms_user = $this->_scopeConfig->getValue('two_factor_auth/general/api_username');
        $sms_password = $this->_scopeConfig->getValue('two_factor_auth/general/api_password');
        $message = urlencode($this->getMessage());
        $url_query_string = $this->getUrl().'?';
        $url_query_string.="service=send_sms";
        $url_query_string.="&message={$message}";
        $url_query_string.="&dest={$this->getRecipients()}";
        $url_query_string.="&sender=$sender";
        $url_query_string.="&username=$sms_user";
        $url_query_string.="&password=$sms_password";

        return $url_query_string;
    }

    public function execute()
    {

        $headers = array();

        $url_query_string = $this->prepareRequest();

        $response = file_get_contents($url_query_string);

        try {
            $this->handleResponse($response);
        } catch (\Exception $e) {
            throw new \Exception('Naskyoo SMS XML response can\'t be parsed');
        }

        return $this;
    }

    public function getUrl()
    {
        return $this->_scopeConfig->getValue('two_factor_auth/general/api_gateway_url');
    }

    public function setRecipient($recipient)
    {
        $this->setRecipients($recipient);

        return $this;
    }

    /**
     * @param $response
     */
    protected function handleResponse($response)
    {
        $info = ['http_code' => '200'];

        $xmlParser = $this->parser->loadXML($response);
        $this->response = $xmlParser->getDom();

        $status = $this->response->getElementsByTagName('status')->item(0);
        if(!empty($status)){
            $info['http_code'] == $status->nodeValue;
        }

        $this->info = $info;

    }

    /**
     * @return string
     */
    public function getResponseCode()
    {
        return $this->info['http_code'];
    }

    /**
     * @return string
     */
    public function getResponseMessage()
    {
        return $this->response->getElementsByTagName('description')->item(0)->nodeValue;
    }

    /**
     * @return bool
     */
    public function isSuccessResponse()
    {
        return $this->getResponseCode() == self::RESPONSE_CODE_SUCCESS
            || strtolower($this->getResponseMessage()) == 'success';
    }

    public function getResult(){
        return [
            'status' => $this->getResponseCode(),
            'message' => $this->getResponseMessage()
        ];
    }

}