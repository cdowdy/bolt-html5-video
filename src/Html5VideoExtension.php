<?php

namespace Bolt\Extension\cdowdy\html5video;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Controller\Zone;
use Bolt\Events\StorageEvent;
use Bolt\Events\StorageEvents;
use Bolt\Extension\SimpleExtension;
use Bolt\Library as Lib;

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
    public function html5video($file, $name, array $options = array() )
    {

        // get the config file name if using one. otherwise its 'default'
        $configName = $this->getConfigName($name);

        // check for config settings
        $defaultOptions = $this->getOptions($configName);

        $mergedOptions = array_merge($defaultOptions, $options);


        $attributes = $this->combineOptions($configName, $options, 'attributes');

        $poster = $mergedOptions['video_poster'];
        $isCDN = $mergedOptions['use_cdn'];
        $preload = $mergedOptions['preload'];
        $widthHeight = $mergedOptions['width_height'];
        $mediaFragment = $mergedOptions['media_fragment'];
        $videoTypes = $mergedOptions['video_types'];
        $multipleSource = $mergedOptions['multiple_source'];

        $videoID = $mergedOptions['video_id'];
//        $videoID = $this->getClassID( $configName, 'video_id');

        // get tracks if present
        $tracks = $mergedOptions['tracks'];

        // class passed through the twig template
        $templateClass = $options['class'];
        // classes in the config
        $classes = $defaultOptions['class'];

        if ( $templateClass && $classes ) {
            $htmlClass = array_merge($classes, $templateClass );
        } elseif ($templateClass){
            $htmlClass = $templateClass;
        } else {
            $htmlClass = $classes;
        }


        if ($multipleSource) {
            $multiVideo = $this->multipleVids($file, $isCDN, $multipleSource, $videoTypes );
        } else {
            $singleVid = $this->videoFile( $file, $isCDN );
        }

        $config = $this->getConfig();


        $context = [
            'singleSrc' => $singleVid,
            'poster' => $poster,
            'preload' => $preload,
            'widthHeight' => $widthHeight,
            'attributes' => $attributes,
            'class' => $htmlClass,
            'video_id' => $videoID,
            'is_cdn' => $isCDN,
            'tracks' => $tracks,

            'multiSrc' => $multipleSource,
            'multiVid' => $multiVideo,
            'fragment' => $mediaFragment
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
//    protected function getHTMLClass($config)
//    {
//        $confg = $this->getConfig();
//        $configName = $this->getConfigName($config);
//        $htmlClass = $confg[ $configName ][ 'class' ];
//
//        $class = $confg[ 'default' ][ 'class' ];
//
//        // if a class array is in the config set the $class variable to the class array
//        if ( isset($htmlClass ) ) {
//            $class = $htmlClass;
//        }
//
//        return $class;
//    }

    protected function getClassID( $config, $type )
    {
        $cfg = $this->getConfig();
        $configName = $this->getConfigName($config);

        return $cfg[ $configName ][ $type ];

    }


//    protected function getVideoId($config)
//    {
//        $confg = $this->getConfig();
//        $configName = $this->getConfigName($config);
//        $videoID = $confg[ $configName ][ 'video_id' ];
//
//        $id = $confg[ 'default' ][ 'video_id' ];
//
//        // if a class array is in the config set the $class variable to the class array
//        if ( isset($videoID ) ) {
//            $id = $videoID;
//        }
//
//        return $id;
//    }


    /**
     * @param $config
     * @return array
     */
    function getOptions( $config )
    {
        $cfg = $this->getConfig();
        $configName = $this->getConfigName($config);
        $cdn = $cfg[ $configName ][ 'use_cdn' ];
        $videoID = $cfg[ $configName ]['video_id'];
        $saveData = $cfg[ $configName ]['save_data'];

        $attributes = $cfg[ $configName ]['attributes'];
        $preload = $cfg[ $configName ]['preload'];
        $widthHeight = $cfg[ $configName ]['width_height'];
        $poster = $cfg[ $configName ]['video_poster'];
        $mediaFragment = $this->mediaFragment( $configName );

        $tracks = $this->vidTracks( $configName );

//        $class = $this->getHTMLClass($configName);
//        $id = $this->getVideoId($configName);
        $class = $this->getClassID( $configName, 'class');
        $id = $this->getClassID( $configName, 'video_id');
        $multiple_source = $cfg[$configName]['multiple_source'];

        $videoTypes = $cfg[ $configName ]['video_types'];

        $defaults = [
            'use_cdn' => $cdn,
            'video_id' => $videoID,
            'class' => $class,
            'video_id' => $id,
            'multiple_source' => $multiple_source,
            'video_types' => $videoTypes,
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
        $app = $this->getContainer();
        $config = $this->getConfig();
        $enforceSSL = $app['config']->get('general/enforce_ssl');
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

    // Get the video URL or relative path.
    // If its a URL then we'll just pass it along
    // if it isn't a URL then pass the filename to Bolt's "safefilename" function and attach it to the
    // filepath of hte site
    public function videoFile($filename, $cdn)
    {
        $app = $this->getContainer();

        if (is_array($filename)) {
            $filename = isset($filename['filename']) ? $filename['filename'] : $filename['file'];
        }

        if ($cdn) {
            $video = $this->cdnFile($filename);
        } else {
            $video = sprintf(
                '%sfiles/%s',
                $app['paths']['root'],
                Lib::safeFilename($filename)
            );
        }

        return $video;
    }

    protected function cdnFile($filename)
    {
        $confg = $this->getConfig();

//        $useCDN = $options['use_cdn'];

        $cdnURL = $confg['cdn_url'];
        $cdnPrefix = $this->prefixCDNURL($cdnURL);

        if ($cdnURL) {
            $video = $cdnURL . $filename;
        } else {

            $video = $this->prefixCDNURL($filename);
        }

        return $video;
    }

    protected function multipleVids($filename, $isCDN, $msrc, $types )
    {

        $fileInfo = pathinfo($this->cdnFile($filename));
        $singlePath = pathinfo($this->videoFile($filename));

        $multiVideo = [];

        if ($msrc && $isCDN) {
            foreach ($types as $type => $value) {
                $multiVideo += [ $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '.' . $value => $value ];
//                $multiVideo[] .= $value;
            }
        }


        if ($msrc && !$isCDN) {
            foreach ($types as $type => $value) {
                $multiVideo += [ $singlePath['dirname'] . '/' . $singlePath['filename'] . '.' . $value   => $value ];
//                $multiVideo[] .= $value;
            }
        }

        return $multiVideo;

    }

    protected function vidTracks( $cfg )
    {
        $config = $this->getConfig();
        $configName = $this->getConfigName( $cfg );

        $trackConfig = $config[ $configName ]['tracks'];

        return $trackConfig;
    }

    protected function mediaFragment( $cfg )
    {
        $config = $this->getConfig();
        $configName = $this->getConfigName( $cfg );

        $mediaFragment = $config[ $configName ]['media_fragment'];

        return $mediaFragment;
    }


    // since we can pass a CDN URL to our twig function in addition to
    // the files from either {{ record.videoFile }} or from the files directory.. ie. 'site/files/video.webm'
    // we'll get the host of the string(filename) if it exists.
    public function getHost($string) {
        $url = parse_url(trim($string));

        return trim($url['host'] ? $url['host'] : array_shift(explode('/', $url['path'], 2)));
    }

}
