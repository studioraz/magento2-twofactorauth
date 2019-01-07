<?php
/**
 * Copyright © 2019 Studio Raz. All rights reserved.
 * See LICENSE file for license details.
 */

namespace SR\TwoFactorAuth\Model\Services;

interface ServiceInterface
{
    public function execute();

    public function getResult();

    public function isSuccessResponse();
}