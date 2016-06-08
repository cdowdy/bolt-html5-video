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
//    public function registerFields()
//    {
//        /*
//         * Custom Field Types
//         */
//
//        return [
//            new Field\HTML5VideoField(),
//        ];
//    }


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

    /**
     * @param $name
     *
     * @return string
     *
     * get the config name. If no name is passed in the twig function then use
     * the default settings in our config file under defaults
     */
    function getConfigName($name)
    {

        if (empty($name)) {

            $configName = 'default';

        } else {

            $configName = $name;

        }

        return $configName;
    }


    /**
     * @param $config
     *
     * @return mixed
     */
    protected function getHTMLClass($config)
    {
        $confg = $this->getConfig();
        $configName = $this->getConfigName($config);
        $htmlClass = $confg[ $configName ][ 'class' ];

        $class = $confg[ 'default' ][ 'class' ];

        // if a class array is in the config set the $class variable to the class array
        if ( isset($htmlClass ) ) {
            $class = $htmlClass;
        }

        return $class;
    }


    /**
     * @param $config
     * @return array
     */
    function getOptions( $config )
    {
        $confg = $this->getConfig();
        $configName = $this->getConfigName($config);
        $cdn = $confg[ $configName ][ 'use_cdn' ];
        $videoID = $confg[ $configName ]['video_id'];
        $saveData = $confg[ $configName ]['save_data'];

        $attributes = $confg[ $configName ]['attributes'];
        $preload = $confg[ $configName ]['preload'];
        $widthHeight = $confg[ $configName ]['width_height'];
        $poster = $confg[ $configName ]['video_poster'];
        $mediaFragment = $confg[ $configName ]['media_fragment'];
        $tracks = $confg[ $configName ]['tracks'];


        $class = $this->getHTMLClass($configName);
        $multiple_source = $confg[$configName]['multiple_source'];


        $defaults = [
            'use_cdn' => $cdn,
            'video_id' => $videoID,
            'class' => $class,
            'multiple_source' => $multiple_source,
            'save_data' => $saveData,
            'attributes' => $attributes,
            'preload' => $preload,
            'width_height' => $widthHeight,
            'video_poster' => $poster,
            'media_fragment' => $mediaFragment,
            'tracks' => $tracks
        ];

//        $defOptions = array_merge($defaults, $options);

        return $defaults;
    }


    /**
     * @param $config
     * @param array $options
     * @param $option
     * @return array|mixed
     */
    public function combineOptions( $config, $options = array(), $option ) {
        $configName = $this->getConfigName($config);
        $defaultOptions = $this->getOptions($configName);

        if ($options[$option]) {
            $combined = array_merge($defaultOptions[ $option ], $options[ $option ]);
        } else {
            $combined = $defaultOptions[$option];
        }

        return $combined;
    }


    /**
     * @param $url
     *
     * @return string
     *
     * see if the url passed as the video URL contains a protocol.
     * check the 'cdn_url' config option and see if it has a protocol
     * if no protocol is found and the global 'enforce_ssl' config option is set use https://
     * if no protocol is found on the cdn url AND enforce_ssl isn't set use http://
     * finally
     * if the cdn_url option has a protocol use that protocol
     */

    public function prefixCDNURL( $url )
    {
        $config = $this->getConfig();
        $enforceSSL = $this->app['config']->get('general/enforce_ssl');
        $cdnProtocol = parse_url($config[ 'cdn_url' ], PHP_URL_SCHEME);

        if (!$cdnProtocol && $enforceSSL ) {
            $prefix = 'https://';
        } elseif (!$cdnProtocol ) {
            $prefix = 'http://';
        } else {
            $prefix = $cdnProtocol . '://';
        }

        if(!preg_match("~^(?:f|ht)tps?://~i", $url) ) {
            $url = $prefix . $url;
        }
        return $url;
    }
}
