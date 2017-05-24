<?php

namespace Meanbee\PWA\Result;

use Magento\Framework\View;
use Magento\Framework\App\ResponseInterface;

class PWA extends View\Result\Page
{
    const OUTPUT_CONTAINER_NAME = "columns";

    /** @var \Meanbee\PWA\Helper\Config $configHelper */
    protected $configHelper;

    /** @var \Magento\Framework\Json\EncoderInterface $jsonEncoder */
    protected $jsonEncoder;

    /** @var \Magento\Framework\Escaper $escaper */
    protected $escaper;

    /** @var  View\Asset\GroupedCollectionFactory $groupedCollectionFactory */
    protected $groupedCollectionFactory;

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
        View\Asset\GroupedCollectionFactory $groupedCollectionFactory,
        $template,
        $isIsolated
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
        $this->groupedCollectionFactory = $groupedCollectionFactory;
    }


    /**
     * @inheritdoc
     */
    protected function render(ResponseInterface $response)
    {
        if ($this->request->getParam($this->configHelper->getServiceWorkerUrlParamName(), false)) {
            /** @var \Meanbee\PWA\Model\View\Layout $layout */
            $layout = $this->getLayout();

            // Force the layout to build to ensure that the "root" element is generated and can be
            // removed from the list of output elements
            $layout->publicBuild();

            // Render the output container instead of the entire page
            $layout
                ->removeOutputElement("root")
                ->addOutputElement(static::OUTPUT_CONTAINER_NAME);

            // Migrate the body classes to the output container
            $containerClass = implode(" ", array_filter([
                $layout->getElementProperty(static::OUTPUT_CONTAINER_NAME, "htmlClass"),
                $this->getBodyClassString(),
            ]));
            $layout->setElementProperty(static::OUTPUT_CONTAINER_NAME, "htmlClass", $containerClass);

            $data = [
                "url"     => $this->getCurrentUrl(),
                "title"   => $this->getPageTitle(),
                "content" => $layout->getOutput() . $this->renderPageSpecificCss(),
            ];

            $response->representJson($this->jsonEncoder->encode($data));

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
     * @return string
     */
    protected function getBodyClassString()
    {
        $this->addDefaultBodyClasses();

        return $this->getConfig()->getElementAttribute(View\Page\Config::ELEMENT_TYPE_BODY, "class") ?: "";
    }

    /**
     * Render CSS assets specific to the handles on this page (excluding the default handle).
     *
     * @return string
     */
    protected function renderPageSpecificCss()
    {
        $defaultAssetIds = $this->getDefaultHandleAssetIds();
        $collection = $this->groupedCollectionFactory->create();

        // Loop over each asset group on the page
        foreach ($this->getConfig()->getAssetCollection()->getGroups() as $group) {
            // Skip groups that contain non-css assets
            if ($group->getProperty(View\Asset\GroupedCollection::PROPERTY_CONTENT_TYPE) !== "css") {
                continue;
            }

            // Filter out the assets in the group that appear on the default handle
            $assets = array_filter($group->getAll(), function ($key) use ($defaultAssetIds) {
                return !in_array($key, $defaultAssetIds);
            }, ARRAY_FILTER_USE_KEY);

            // Add the remaining assets to an identical group in a separate grouped collection
            foreach ($assets as $identifier => $asset) {
                $collection->add($identifier, $asset, $group->getProperties());
            }
        }

        if (count($collection->getAll()) > 0) {
            return $this->pageConfigRenderer->renderAssetCollection($collection);
        }

        return "";
    }

    /**
     * Get the list of asset identifiers assigned to the "default" layout handle.
     * @return array
     */
    protected function getDefaultHandleAssetIds()
    {
        /** @var \Magento\Framework\View\Layout $defaultLayout */
        $defaultLayout = $this->layoutFactory->create();
        $defaultLayout->getUpdate()->addHandle("default");

        $defaultLayoutBuilder = $this->layoutBuilderFactory->create(View\Layout\BuilderFactory::TYPE_LAYOUT, [
            "layout" => $defaultLayout,
        ]);

        $defaultLayoutBuilder->build();

        return array_keys(
            $defaultLayout->getReaderContext()
                ->getPageConfigStructure()
                ->getAssets()
        );
    }
}
