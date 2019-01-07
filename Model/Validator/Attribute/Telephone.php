<?php

namespace SR\TwoFactorAuth\Model\Validator\Attribute;

class Telephone extends \Magento\Framework\Validator\AbstractValidator
{
    public function isValid($entity)
    {
        $telephone = $entity->getData('telephone');
        $pattern = '/^(?:(?:(?:\s|\.|-)?)|(0[23489]{1})|(0[57]{1}[0-9]{1,2}))?(\d{2}(?:\s|\.|-)?\d{4})$/i';
        if (!preg_match($pattern, $telephone)) {
            $this->_messages['telephone'] = [__('Please fill in a "Telephone" field without hyphens and spaces')];

            return false;
        }

        return true;
    }
}
