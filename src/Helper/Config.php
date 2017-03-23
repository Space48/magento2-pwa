<?php

namespace Meanbee\PWA\Helper;

class Config
{
    /**
     * @var string
     */
    protected $serviceWorkerUrlParamName;

    public function __construct($serviceWorkerUrlParamName)
    {
        $this->serviceWorkerUrlParamName = $serviceWorkerUrlParamName;
    }

    /**
     * Get the URL parameter name used for service worker requests.
     *
     * @return string
     */
    public function getServiceWorkerUrlParamName()
    {
        return $this->serviceWorkerUrlParamName;
    }
}
