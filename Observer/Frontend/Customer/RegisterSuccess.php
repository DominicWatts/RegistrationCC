<?php
/**
 * Copyright © 2022 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PixieMedia\RegistrationCC\Observer\Frontend\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PixieMedia\RegistrationCC\Helper\Config;
use Psr\Log\LoggerInterface;

class RegisterSuccess implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \PixieMedia\RegistrationCC\Helper\Config
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \PixieMedia\RegistrationCC\Helper\Config $helper
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Config $helper,
        Escaper $escaper,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        LoggerInterface $logger
    ) {
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->helper = $helper;
        $this->_escaper = $escaper;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        $customer = $observer->getData('customer');
        $loaded = $this->customerFactory->create()->load($customer->getId());
        if ($loaded) {
            $ccEmailAddresses = $this->helper->getEmailCopyTo();
            $ccEmailName = $this->helper->getName();
            if (!empty($ccEmailAddresses)) {
                foreach ($ccEmailAddresses as $ccEmailAddress) {
                    $this->_sendEmailTemplate(
                        $loaded,
                        $ccEmailAddress,
                        $ccEmailName
                    );
                }
            }
        }
    }

    /**
     * Send copy email
     */
    protected function _sendEmailTemplate($customer, $copyEmail, $copyName)
    {
        try {
            $postObject = new DataObject();
            $postObject->setData($customer->getData());

            /** @var \Magento\Framework\Mail\TransportInterface $transport */
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                $this->_scopeConfig->getValue(
                    Customer::XML_PATH_REGISTER_EMAIL_TEMPLATE,
                    ScopeInterface::SCOPE_STORE
                )
            )->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId()
                ]
            )->setTemplateVars(
                [
                    'customer' => $postObject,
                    'back_url' => '',
                    'store' => $this->_storeManager->getStore()
                ]
            )->setFrom(
                $this->_scopeConfig->getValue(
                    Customer::XML_PATH_REGISTER_EMAIL_IDENTITY,
                    ScopeInterface::SCOPE_STORE
                )
            )->addTo(
                $copyEmail,
                $copyName
            )->getTransport();

            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
