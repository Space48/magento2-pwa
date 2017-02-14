<?php

namespace Meanbee\PWA\Result;

use Magento\Framework\View;
use Magento\Framework\App\ResponseInterface;

class PWA extends View\Result\Page
{
    /** @var \Magento\Framework\Json\EncoderInterface $jsonEncoder */
    protected $jsonEncoder;

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

        $this->jsonEncoder = $jsonEncoder;
    }


    /**
     * @inheritdoc
     */
    protected function render(ResponseInterface $response)
    {
        if ($this->request->getParam("service_worker", false)) {
            $this->pageConfig->publicBuild();
            $this->getLayout()
                ->removeOutputElement("root")
                ->addOutputElement("columns");

            $data = [
                "content" => $this->getLayout()->getOutput(),
            ];

            $response->representJson($this->jsonEncoder->encode($data));

            return $this;
        } else {
            return parent::render($response);
        }
    }
}
