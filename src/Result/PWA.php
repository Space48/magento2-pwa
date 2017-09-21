<?php

namespace Meanbee\PWA\Result;

use Magento\Framework\View;
use Magento\Framework\App\ResponseInterface;

class PWA extends View\Result\Page
{
    const OUTPUT_CONTAINER_NAME = "main.content";
    const ADDITIONAL_BLOCK_INCLUDE_ARGUMENT = "pwa_response_include";

    /** @var \Meanbee\PWA\Helper\Config $configHelper */
    protected $configHelper;

    /** @var \Magento\Framework\Json\EncoderInterface $jsonEncoder */
    protected $jsonEncoder;

    /** @var \Magento\Framework\Escaper $escaper */
    protected $escaper;

    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        View\Layout\ReaderPool $layoutReaderPool,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        View\Layout\BuilderFactory $layoutBuilderFactory,
        View\Layout\GeneratorPool $generatorPool,
        View\Page\Config\RendererFactory $pageConfigRendererFactory,
        View\Page\Layout\Reader $pageLayoutReader,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Meanbee\PWA\Helper\Config $configHelper,
        $template,
        $isIsolated = false
    ) {
        parent::__construct(
            $context,
            $layoutFactory,
            $layoutReaderPool,
            $translateInline,
            $layoutBuilderFactory,
            $generatorPool,
            $pageConfigRendererFactory,
            $pageLayoutReader,
            $template,
            $isIsolated
        );

        $this->configHelper = $configHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->escaper = $context->getEscaper();
    }

    /**
     * Generate and return the PWA response data.
     *
     * @return array
     */
    public function getResponseData()
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $this->getLayout();

        // Force the layout to build to ensure that the "root" element is generated and can be
        // removed from the list of output elements
        $layout->publicBuild();

        // Render the output container instead of the entire page
        $layout
            ->removeOutputElement("root")
            ->addOutputElement(static::OUTPUT_CONTAINER_NAME);

        $headBlock = $layout->getBlock("head.additional");

        $data = [
            "meta"      => [
                "url"   => $this->getCurrentUrl(),
                "title" => $this->getPageTitle(),
            ],
            "head"      => $headBlock ? $headBlock->toHtml() : "",
            "bodyClass" => $this->getBodyClass(),
            "content"   => $layout->getOutput(),
            "additionalBlocks" => $this->getAdditionalBlocksData($layout),
            "assets"    => $this->pageConfigRenderer->renderAssets(),
        ];

        // Allow observers to edit generated data
        $data_object = new \Magento\Framework\DataObject($data);
        $this->eventManager->dispatch("meanbee_pwa_data_generate_after", [
            "pwa_response" => $data_object,
        ]);
        $data = $data_object->getData();

        return $data;
    }

    /**
     * Generate the data for the additional blocks that were marked to be
     * included in the PWA result through layout.
     *
     * @param View\Layout $layout
     *
     * @return string[]
     */
    public function getAdditionalBlocksData(\Magento\Framework\View\Layout $layout)
    {
        $blocks = [];

        foreach ($layout->getAllBlocks() as $name => $block) {
            /** @var \Magento\Framework\View\Element\AbstractBlock $block */
            if ($block->getData(static::ADDITIONAL_BLOCK_INCLUDE_ARGUMENT) == true) {
                $blocks[$name] = $block->toHtml();
            }
        }

        return $blocks;
    }

    /**
     * @inheritdoc
     */
    protected function render(ResponseInterface $response)
    {
        if ($this->request->getParam($this->configHelper->getServiceWorkerUrlParamName(), false)) {
            $response->representJson($this->jsonEncoder->encode($this->getResponseData()));

            return $this;
        } else {
            return parent::render($response);
        }
    }

    /**
     * Get the URL of the current page.
     *
     * @return string
     */
    protected function getCurrentUrl()
    {
        return $this->urlBuilder->getUrl("*/*/*", [
            "_current"     => true,
            "_use_rewrite" => true,
        ]);
    }

    /**
     * Get the page title.
     *
     * @return string
     */
    protected function getPageTitle()
    {
        return $this->escaper->escapeHtml($this->pageConfig->getTitle()->get());
    }

    /**
     * Get the string of HTML classes assigned to the <body> element.
     *
     * @return string[]
     */
    protected function getBodyClass()
    {
        $this->addDefaultBodyClasses();

        $classString = $this->getConfig()->getElementAttribute(View\Page\Config::ELEMENT_TYPE_BODY, "class");

        return $classString ? explode(" ", $classString) : [];
    }
}
