<?php

namespace Meanbee\PWA\Model\View\Page\Config;

class Renderer extends \Magento\Framework\View\Page\Config\Renderer
{
    /**
     * Render the assets from the given collection.
     *
     * @param \Magento\Framework\View\Asset\GroupedCollection $groupedCollection
     *
     * @return string
     */
    public function renderAssetCollection(\Magento\Framework\View\Asset\GroupedCollection $groupedCollection)
    {
        $output = "";

        foreach ($groupedCollection->getGroups() as $group) {
            $output .= $this->renderAssetGroup($group);
        }

        return $output;
    }
}
