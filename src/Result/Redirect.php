<?php

namespace Meanbee\PWA\Result;

use Magento\Framework\App;
use Magento\Framework\Controller\Result;
use Magento\Framework\UrlInterface;
use Meanbee\PWA\Helper\Config;

class Redirect extends Result\Redirect
{
    /** @var App\RequestInterface */
    protected $request;

    /** @var Config */
    protected $configHelper;

    public function __construct(
        App\RequestInterface $request,
        App\Response\RedirectInterface $redirect,
        UrlInterface $urlBuilder,
        Config $configHelper
    ) {
        parent::__construct($redirect, $urlBuilder);

        $this->request = $request;
        $this->configHelper = $configHelper;
    }

    /**
     * @inheritdoc
     */
    public function render(App\Response\HttpInterface $response)
    {
        // Carry the service worker parameter through to the redirect URL
        $serviceWorkerParam = $this->configHelper->getServiceWorkerUrlParamName();
        if ($this->request->getParam($serviceWorkerParam, false)) {
            $this->url .= (strpos($this->url, "?") === false ? "?" : "&") . sprintf("%s=true", $serviceWorkerParam);
        }

        return parent::render($response);
    }
}
