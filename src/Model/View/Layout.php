<?php

namespace Meanbee\PWA\Model\View;

class Layout extends \Magento\Framework\View\Layout
{
    /**
     * Set a property value of an element
     *
     * @param string $name
     * @param string $attribute
     * @param mixed  $value
     *
     * @return $this
     */
    public function setElementProperty($name, $attribute, $value)
    {
        $this->build();
        $this->structure->setAttribute($name, $attribute, $value);

        return $this;
    }
}
