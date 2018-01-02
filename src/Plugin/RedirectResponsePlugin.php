<?php

namespace Meanbee\PWA\Plugin;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Meanbee\PWA\Helper\Config;

class RedirectResponsePlugin
{
    /**
     * @var Config
     */
    private $configHelper;
    /**
     * @var Http
     */
    private $request;
    
    /**
     * RedirectResponsePlugin constructor.
     *
     * @param Config      $configHelper
     * @param HttpRequest $request
     */
    public function __construct(Config $configHelper, HttpRequest $request)
    {
        $this->configHelper = $configHelper;
        $this->request      = $request;
    }
    
    /**
     * @param HttpResponse $subject
     * @param                                      $url
     * @param                                      $code
     * @return array
     */
    public function beforeSetRedirect(HttpResponse $subject, $url, $code = 302)
    {
        if ($this->isServiceWorkerRequest()) {
            $url = $this->withServiceWorkerParam($url);
        }
        
        return [$url, $code];
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