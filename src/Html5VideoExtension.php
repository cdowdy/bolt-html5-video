<?php

namespace Bolt\Extension\cdowdy\html5video;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Controller\Zone;
use Bolt\Events\StorageEvent;
use Bolt\Events\StorageEvents;
use Bolt\Extension\SimpleExtension;

/**
 * Html5Video extension class.
 *
 * @author Cory Dowdy <cory@corydowdy.com>
 */
class Html5VideoExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    public function registerFields()
    {
        /*
         * Custom Field Types
         */

        return [
            new Field\HTML5VideoField(),
        ];
    }


    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return ['templates'];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFunctions()
    {
        $options = ['is_safe' => ['html']];
        return [
            'html5video' => [ 'html5video', $options ],
        ];
    }

    /**
     * The callback function when {{ html5video() }} is used in a template.
     *
     * @return string
     */
    public function html5video()
    {
        $context = [
        ];

        return $this->renderTemplate('video.twig', $context);
    }

}
