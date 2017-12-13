<?php

namespace Meanbee\PWA\Response;

use Magento\Framework\App\Response\Http as Base;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\Request\Http as HttpRequest;
use Meanbee\PWA\Helper\Config;

class Http extends Base
{
    /**
     * @var Config
     */
    protected $configHelper;
    
    /**
     * @param HttpRequest $request
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Context $context
     * @param DateTime $dateTime
     */
    public function __construct(
        HttpRequest $request,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Context $context,
        DateTime $dateTime,
        Config $configHelper
    ) {
        parent::__construct($request, $cookieManager, $cookieMetadataFactory, $context, $dateTime);
    
        $this->configHelper = $configHelper;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setRedirect($url, $code = 302)
    {
        if ($this->isServiceWorkerRequest()) {
            $url = $this->withServiceWorkerParam($url);
        }
        
        return parent::setRedirect($url, $code);
    }
    
    /**
     * Get the request and assert whether or not it came from the service worker.
     *
     * @return bool
     */
    private function isServiceWorkerRequest()
    {
        return $this->request->getParam($this->getServiceWorkerParamName(), false) !== false;
    }
    
    /**
     * Retrieve the name of the GET parameter used to signify a service worker request.
     *
     * @return string
     */
    private function getServiceWorkerParamName()
    {
        return $this->configHelper->getServiceWorkerUrlParamName();
    }
    
    /**
     * Return the url guaranteeing that the service worker parameter is provided.
     *
     * @param $url
     * @return string
     */
    private function withServiceWorkerParam($url)
    {
        $initialChar = strpos($url, '?') === false ? '?' : '&';
        
        return implode('', [
            $url,
            $initialChar,
            $this->getServiceWorkerParamName(),
            '=',
            'true'
        ]);
    }
}