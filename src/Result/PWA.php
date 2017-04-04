<?php

namespace Meanbee\PWA\Result;

use Magento\Framework\View;
use Magento\Framework\App\ResponseInterface;

class PWA extends View\Result\Page
{
    /** @var \Meanbee\PWA\Helper\Config $configHelper */
    protected $configHelper;

    /** @var \Magento\Framework\Json\EncoderInterface $jsonEncoder */
    protected $jsonEncoder;

    /** @var  View\Asset\GroupedCollection $groupedCollection */
    protected $groupedCollection;

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
        View\Asset\GroupedCollection $groupedCollection,
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
        $this->groupedCollection = $groupedCollection;
    }


    /**
     * @inheritdoc
     */
    protected function render(ResponseInterface $response)
    {
        if ($this->request->getParam($this->configHelper->getServiceWorkerUrlParamName(), false)) {
            $this->pageConfig->publicBuild();
            $this->getLayout()
                ->removeOutputElement("root")
                ->addOutputElement("columns");

            $data = [
                "content" => $this->getLayout()->getOutput() . $this->renderPageSpecificCss(),
            ];

            $response->representJson($this->jsonEncoder->encode($data));

            return $this;
        } else {
            return parent::render($response);
        }
    }

    /**
     * Render CSS assets specific to the handles on this page (excluding the default handle).
     *
     * @return string
     */
    protected function renderPageSpecificCss()
    {
        $defaultAssetIds = $this->getDefaultHandleAssetIds();

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
                $this->groupedCollection->add($identifier, $asset, $group->getProperties());
            }
        }

        if (count($this->groupedCollection->getAll()) > 0) {
            return $this->pageConfigRenderer->renderAssetCollection($this->groupedCollection);
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
