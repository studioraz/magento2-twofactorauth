<?php

namespace SR\TwoFactorAuth\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    private $customerSetupFactory;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<=')) {
            $attr = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'telephone');
            $attr->setData(
                'used_in_forms',
                ['checkout_register', 'customer_account_create', 'customer_account_edit','adminhtml_customer']
            );
            $attr->save();
        }
    }
}
