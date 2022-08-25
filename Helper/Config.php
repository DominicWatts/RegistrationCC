<?php
/**
 * Copyright © 2022 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PixieMedia\RegistrationCC\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const XML_PATH_COPY_ENABLED = 'registrationcc/general/enabled';
    const XML_PATH_COPY_MODE = 'registrationcc/general/mode';
    const XML_PATH_COPY_NAME = 'registrationcc/general/name';
    const XML_PATH_COPY_EMAIL = 'registrationcc/general/email';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Is Enabled
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COPY_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * CC/BCC
     * @return mixed
     */
    public function getMode()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COPY_MODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get copy recipient name
     * @return mixed
     */
    public function getName()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COPY_NAME,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get copy addresses
     * @return array
     */
    public function getEmailCopyTo()
    {
        $config = $this->scopeConfig->getValue(
            self::XML_PATH_COPY_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
        return explode(',', trim($config));
    }
}
