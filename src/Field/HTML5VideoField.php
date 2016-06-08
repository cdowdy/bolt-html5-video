<?php

namespace Bolt\Extension\cdowdy\html5video\Field;

use Bolt\Field\FieldInterface;

/**
 * Custom field type class for use in ContentTypes.
 *
 * @author YCory Dowdy <cory@corydowdy.com>
 */
class HTML5VideoField implements FieldInterface
{
    /**
     * Returns the name of the field.
     *
     * @return string The field name
     */
    public function getName()
    {
        return 'html5video';
    }

    /**
     * Returns the path to the template.
     *
     * @return string The template name
     */
    public function getTemplate()
    {
        return '_video_backend.twig';
    }

    /**
     * Returns the storage type.
     *
     * @return string A Valid Storage Type
     */
    public function getStorageType()
    {
        return 'text';
    }

    /**
     * Returns additional options to be passed to the storage field.
     *
     * @return array An array of options
     */
    public function getStorageOptions()
    {
        return ['default' => ''];
    }
}
