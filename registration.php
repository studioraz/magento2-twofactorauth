<?php
/**
 * Copyright ©2016 Studio Raz. All rights reserved.
 * For more information contact us at dev@studioraz.co.il
 * See COPYING_STUIDORAZ.txt for license details.
 */
use \Magento\Framework\Component\ComponentRegistrar;
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'SR_TwoFactorAuth',
    __DIR__
);