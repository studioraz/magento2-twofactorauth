<?php

namespace SR\TwoFactorAuth\Model\Services;

interface ServiceInterface
{
    public function execute();

    public function getResult();

    public function isSuccessResponse();
}