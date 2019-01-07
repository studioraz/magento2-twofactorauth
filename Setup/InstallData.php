<?php

namespace SR\TwoFactorAuth\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory, \Magento\Eav\Model\Config $eavConfig)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'telephone',
            [
                'type' => 'text',
                'label' => 'Telephone',
                'input' => 'text',
                'required' => true,
                'default' => null,
                'sort_order' => 100,
                'system' => false,
                'position' => 100
            ]
        );
        $attr = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'telephone');
        $attr->setData(
            'used_in_forms',
            ['checkout_register', 'customer_account_create', 'customer_account_edit']
        );
        $attr->save();
    }
}