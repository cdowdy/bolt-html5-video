<?php

namespace Bolt\Extension\cdowdy\html5video;

use Bolt\Asset\Snippet\Snippet;
use Bolt\Asset\Target;
use Bolt\Controller\Zone;
use Bolt\Extension\SimpleExtension;
use Bolt\Library as Lib;

/**
 * Html5Video extension class.
 *
 * @author Cory Dowdy <cory@corydowdy.com>
 */
class Html5VideoExtension extends SimpleExtension
{

    private $_currentSD = 'save-data-video.07-12-2016-27.min.js';
    private $_scriptAdded = FALSE;

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
        $saveData = $mergedOptions['save_data'];
        $preload = $mergedOptions['preload'];
        $widthHeight = $mergedOptions['width_height'];
        $mediaFragment = $mergedOptions['media_fragment'];
//        $mediaFragment = (isset($mergedOptions['media_fragment']) ? $mergedOptions['media_fragment'] : null);
        $videoTypes = $mergedOptions['video_types'];
        $multipleSource = $mergedOptions['multiple_source'];

        $videoID = $mergedOptions['video_id'];

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


        $multiVideo = $this->multipleVids($file, $isCDN, $multipleSource, $videoTypes);
        $singleVid = $this->videoFile($file, $isCDN);

        isset($multipleSource) ? $multiVideo : $singleVid;

        if ($saveData) {
            $saveDataFile = $this->saveDataFile($file, $isCDN, $multipleSource, $videoTypes);
            $sdOptions = $this->sdOptions();
        }

        $config = $this->getConfig();

        $this->addAssets($configName);

        $context = [
            'singleSrc' => $singleVid,
            'poster' => $poster,
            'save_data' => $saveData,
            'sd_file' => $saveDataFile,
            'sdOpt' => $sdOptions,
            'preload' => $preload,
            'widthHeight' => [ 'width' => $widthHeight[0], 'height' => $widthHeight[1]],
            'attributes' => $attributes,
            'class' => $htmlClass,
            'video_id' => $videoID,
            'is_cdn' => $isCDN,
            'tracks' => $tracks,

            'multiSrc' => $multipleSource,
            'multiVid' => $multiVideo,
            'video_types' => $videoTypes,
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
     * @param $type
     * @return mixed
     *
     *  get the HTML Class or ID set in the config and pass this to the
     *  getOptions method :)
     */
    protected function getClassID( $config, $type )
    {
        $cfg = $this->getConfig();
        $configName = $this->getConfigName($config);



        return isset($cfg[ $configName ][ $type ]) ? $cfg[ $configName ][ $type ] : null;

    }




    /**
     * @param $config
     * @return array
     *
     * gather all the config options and config option methods
     * create one array that is then passed into the  twig callback function for HTML5 video
     */
    function getOptions( $config )
    {
        $cfg = $this->getConfig();
        $configName = $this->getConfigName($config);

        $cdn = $cfg[ $configName ][ 'use_cdn' ];
//        $videoID = $cfg[ $configName ]['video_id'];
        $saveData = $cfg[ $configName ]['save_data'];

        $attributes = $cfg[ $configName ]['attributes'];
        $preload = $cfg[ $configName ]['preload'];
        $widthHeight = $cfg[ $configName ]['width_height'];
        $poster = $cfg[ $configName ]['video_poster'];
        $mediaFragment = $this->mediaFragment( $configName );
        $tracks = $this->vidTracks( $configName );

        $class = $this->getClassID( $configName, 'class');
        $videoID = $this->getClassID( $configName, 'video_id');

        $multiple_source = $cfg[$configName]['multiple_source'];

        $videoTypes = $cfg[ $configName ]['video_types'];

        $defaults = [
            'use_cdn' => $cdn,
            'video_id' => $videoID,
            'class' => $class,
//            'video_id' => $id,
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

        return $defaults;
    }


    /**
     * @param $filename
     * @param $isCDN
     * @param $msrc
     * @param $types
     * @return array|string
     */
    protected function saveDataFile( $filename, $isCDN, $msrc, $types )
    {

        $fileInfo = pathinfo($this->cdnFile($filename));
        $singlePath = pathinfo($this->videoFile($filename, $isCDN));

        $saveDataFile = [];

        if ($msrc) {
            $saveDataFile = $this->multipleVids($filename, $isCDN, $msrc, $types );
        }

        if (!$msrc && $isCDN) {
            $saveDataFile = [ $fileInfo['dirname'] . '/' . $fileInfo['basename']  => $fileInfo['extension'] ]  ;
        }

        if (!$msrc && !$isCDN) {
            $saveDataFile = [ $singlePath['dirname'] . '/' . $singlePath['basename']  => $singlePath['extension'] ] ;
        }

        return $saveDataFile;
    }

    /**
     * @return array
     *
     * get all the options for the save data option
     * a Wrapping div
     * Div and Paragraph tags classes
     * a custom message
     */
    protected function sdOptions(  )
    {
        $cfg = $this->getConfig();
        $sdConfig = $cfg['save_data_options'];

        $message = $sdConfig['message'];
        $messageClass = $sdConfig['message_class'];
        $buttonClass = $sdConfig['button_class'];
        $wrapDiv = $sdConfig['wrapping_div'];
        $divClass = $sdConfig['wrapping_div_class'];


        return $sdOptions = [
            'message' => $message,
            'message_class' => $messageClass,
            'button_class' => $buttonClass,
            'wrapping_div' => $wrapDiv,
            'wrapping_div_class' => $divClass
        ];
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
    /**
     * @param $filename
     * @param $cdn
     * @return string
     */
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

    /**
     * @param $filename
     * @return string
     */
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

    /**
     * @param $filename
     * @param $isCDN
     * @param $msrc
     * @param $types
     * @return array
     */
    protected function multipleVids($filename, $isCDN, $msrc, $types )
    {

        $fileInfo = pathinfo($this->cdnFile($filename));
        $singlePath = pathinfo($this->videoFile($filename, $isCDN));

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

    /**
     * @param $cfg
     * @return mixed
     */
    protected function vidTracks( $cfg )
    {
        $config = $this->getConfig();
        $configName = $this->getConfigName( $cfg );

        $trackConfig = $config[ $configName ]['tracks'];

        return $trackConfig;
    }

    /**
     * @param $cfg
     * @return mixed
     */
    protected function mediaFragment( $cfg )
    {
        $config = $this->getConfig();
        $configName = $this->getConfigName($cfg);

        $mediaFragment = isset($config[$configName]['media_fragment']) ? $config[$configName]['media_fragment'] : null;

        return $mediaFragment;
    }


    // since we can pass a CDN URL to our twig function in addition to
    // the files from either {{ record.videoFile }} or from the files directory.. ie. 'site/files/video.webm'
    // we'll get the host of the string(filename) if it exists.
    /**
     * @param $string
     * @return string
     */
    public function getHost($string) {
        $url = parse_url(trim($string));

        return trim($url['host'] ? $url['host'] : array_shift(explode('/', $url['path'], 2)));
    }

    /**
     * You can't rely on bolts methods to insert javascript/css in the location you want.
     * So we have to hack around it. Use the Snippet Class with their location methods and insert
     * Save-Data script into the head. Add a check to make sure the script isn't loaded more than once ($_scriptAdded)
     * and stop the insertion of the files multiple times because bolt's registerAssets method will blindly insert
     * the files on every page
     *
     */

    protected function addAssets($cfg)
    {
        $app = $this->getContainer();

        $config = $this->getConfig();
        $configName = $this->getConfigName( $cfg );

        $saveData = $config[$configName]['save_data'];

        $extPath = $app['resources']->getUrl('extensions');

        $vendor = 'vendor/cdowdy/';
        $extName = 'bolt-html5-video/';

        $saveDataJS = $extPath . $vendor . $extName . 'js/' . $this->_currentSD;
        $saveDataScript = <<<SD
<script src="{$saveDataJS}" async defer></script>
SD;
        $asset = new Snippet();
        $asset->setCallback($saveDataScript)
            ->setZone(ZONE::FRONTEND)
            ->setLocation(Target::AFTER_HEAD_CSS);

        // variable to check if script is added to the page

        if ($saveData){
            if ($this->_scriptAdded == FALSE ) {
                $app['asset.queue.snippet']->add($asset);
                $this->_scriptAdded = TRUE;
            } else {

                $this->_scriptAdded = TRUE;
            }
        }
    }

}
