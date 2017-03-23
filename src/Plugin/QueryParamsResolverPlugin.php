<?php

namespace Meanbee\PWA\Plugin;

class QueryParamsResolverPlugin
{
    /**
     * @var \Meanbee\PWA\Helper\Config
     */
    protected $config;

    public function __construct(\Meanbee\PWA\Helper\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Plugin for the setQueryParam method, removing the service worker URL param from
     * rendered URLs.
     *
     * @param \Magento\Framework\Url\QueryParamsResolver $subject
     * @param callable                                   $proceed
     * @param string                                     $key
     * @param mixed                                      $data
     *
     * @return \Magento\Framework\Url\QueryParamsResolver
     */
    public function aroundSetQueryParam(\Magento\Framework\Url\QueryParamsResolver $subject, callable $proceed, $key, $data)
    {
        if ($key == $this->config->getServiceWorkerUrlParamName()) {
            // Don't add the service worker URL parameter to URLs
            return $subject;
        }

        return $proceed($key, $data);
    }
}
