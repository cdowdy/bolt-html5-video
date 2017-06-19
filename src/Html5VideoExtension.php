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

    /**
     * @var string
     */
    private $_currentSD = 'save-data-video.2cd6b5ea.min.js';
    /**
     * @var bool
     */
    private $_scriptAdded = FALSE;



    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return [ 'templates' ];
    }



    /**
     * {@inheritdoc}
     */
    protected function registerTwigFunctions()
    {
        $options = ['is_safe' => ['html']];
        $this->getConfig();
        return [
            'html5video' => [ 'html5video', $options ],
        ];
    }


    /**
     * The callback function when {{ html5video() }} is used in a template.
     *
     * @param $file
     * @param $name
     * @param array $options
     * @return string
     */
    public function html5video($file, $name, array $options = array() )
    {


        // get the config file name if using one. otherwise its 'default'
        $configName = $this->getConfigName($name);

        // check for config settings
        $defaultOptions = $this->getOptions($configName);
        $defaultConfig = $this->getDefaultConfig();

        /*
         * Merge the options set in either 'default' or named config with the options passed in through the twig template
         */
        $mergedOptions = array_merge($defaultOptions, $options);
//        $attributes = $this->combineOptions($configName, $options, 'attributes');
        $attributes = $this->checkConfig( $mergedOptions, 'attributes', $defaultConfig);

        $poster = $mergedOptions['video_poster'];
        $isCDN = $mergedOptions['use_cdn'];
        $saveData = $this->checkIndex($mergedOptions, 'save_data', FALSE );
        $preload = $mergedOptions['preload'];
        $widthHeight = $this->checkIndex($mergedOptions, 'width_height' , NULL );
        $mediaFragment = $mergedOptions['media_fragment'];
//        $mediaFragment = (isset($mergedOptions['media_fragment']) ? $mergedOptions['media_fragment'] : null);
        $videoTypes = $mergedOptions['video_types'];


//        $multipleSource = $mergedOptions['multiple_source'];
        $multipleSource = (isset($mergedOptions['multiple_source']) ? $mergedOptions['multiple_source'] : FALSE);
        $videoID = $mergedOptions['video_id'];

        // get tracks if present
        $tracks = $mergedOptions['tracks'];

        // class passed through the twig template
        $templateClass = $this->checkIndex( $options, 'class',  null);
        // classes in the config
//        $classes = $defaultOptions['class'];
        $classes = $this->checkIndex( $defaultOptions, 'class', null );

        if ( $templateClass && $classes ) {
            $htmlClass = array_merge($classes, $templateClass );
        } elseif ($templateClass){
            $htmlClass = $templateClass;
        } else {
            $htmlClass = $classes;
        }

        if ($multipleSource && empty($videoTypes) ) {
            $this->multiVidErrors($multipleSource, $videoTypes);
            $multipleSource = FALSE;
        }

        $multiVideo = $this->multipleVids($file, $isCDN, $multipleSource, $videoTypes);
        $singleVid = $this->videoFile($file, $isCDN);

//        isset($multipleSource) ? $multiVideo : $singleVid;

        $saveDataFile = [];
        $sdOptions = [];

        if ($saveData) {
            $saveDataFile = $this->saveDataFile($file, $isCDN, $multipleSource, $videoTypes, $mediaFragment);
            $sdOptions = $this->sdOptions();
        }

//        $config = $this->getConfig();

        $this->addAssets($configName, $saveData);


        $context = [
            'singleSrc' => $singleVid,
            'poster' => $poster,
            'save_data' => $saveData,
            'sd_file' => $saveDataFile,
            'sdOpt' => $sdOptions,
            'preload' => $preload,
            'widthHeight' => $widthHeight ,
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
     * @param $configOption
     * @param $configType
     * @param $defaultConfig
     * @return mixed
     */
    protected function checkConfig ( $configOption, $configType, $defaultConfig )
    {
        return ( isset( $configOption[$configType]) ? $configOption[$configType] : $defaultConfig['default'][$configType] );
    }

    /**
     * @param $option
     * @param $optionType
     * @param $fallback
     * @return mixed
     */
    protected function checkIndex( $option, $optionType, $fallback )
    {
        return ( isset( $option[$optionType]) ? $option[$optionType] : $fallback );
    }

    /**
     * @param $msrc
     * @param $types
     */
    protected function multiVidErrors( $msrc, $types ) {
        $app = $this->getContainer();

        if ($msrc && empty($types ) ) {
            $app['logger.flash']->error("Bolt HTML5 VIDEO ERROR: You Selected Multiple Sources For Your Video in and Haven't Supplied Any Video Types. Please add In At Least Two Types, ie: webm , mp4,  To the Extensions Config or Template. A Single Source Has Been Used Instead" );

        }
    }


//
//    protected function saveDataFileSize($bytes, $decimals = 2)
//    {
//        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
//        $factor = floor((strlen($bytes) - 1) / 3);
//        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
//    }

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
        $defaultConfig = $this->getDefaultConfig();

//        $cdn = isset($cfg[$configName ]['use_cdn']) ;
        $cdn = $this->checkConfig($cfg[$configName], 'use_cdn', $defaultConfig);
//        $videoID = $cfg[ $configName ]['video_id'];
//        $saveData = $this->checkConfig( $cfg[ $configName ], 'save_data',$defaultConfig) ;


        $saveData = $this->checkConfig($cfg[$config], 'save_data', $defaultConfig);

//        $attributes = $cfg[ $configName ]['attributes'];
        $attributes = $this->checkConfig($cfg[$configName], 'attributes', $defaultConfig);
        $preload = $this->checkConfig($cfg[$configName], 'preload', $defaultConfig);
        $widthHeight = $this->checkIndex($cfg[$configName], 'width_height', NULL );




        $poster = $this->checkIndex($cfg[$configName], 'video_poster', null);
        $mediaFragment = $this->mediaFragment($configName);
        $tracks = $this->vidTracks($configName);

        $class = $this->getClassID($configName, 'class');
        $videoID = $this->getClassID($configName, 'video_id');

        $multiple_source = $this->checkConfig($cfg[$configName], 'multiple_source', $defaultConfig);

        $videoTypes = $this->checkIndex($cfg[$configName], 'video_types', null);

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
     * @param $fragment
     * @return string
     */
    protected function saveDataFile($filename, $isCDN, $msrc, $types, $fragment)
    {

        $fileInfo = pathinfo($this->cdnFile($filename));
        $singlePath = pathinfo($this->videoFile($filename, $isCDN));
        $mediaFragment = '';
        if ($fragment) {
            $mediaFragment = '#t=' . $this->savedDataFragment($fragment);
        }

        $saveDataFile = [];

        if ($msrc && $isCDN) {
            foreach ($types as $type => $value) {
                $saveDataFile += [
                    $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '.' . $value . $mediaFragment => $value
                ];
            }
        }


        if ($msrc && !$isCDN) {
            foreach ($types as $type => $value) {
                $saveDataFile += [$singlePath['dirname'] . '/' . $singlePath['filename'] . '.' . $value . $mediaFragment => $value];
            }
        }

        if (!$msrc && $isCDN) {
            $saveDataFile = [$fileInfo['dirname'] . '/' . $fileInfo['basename'] . $mediaFragment => $fileInfo['extension']];
        }

        if (!$msrc && !$isCDN) {
            $saveDataFile = [$singlePath['dirname'] . '/' . $singlePath['basename'] . $mediaFragment => $singlePath['extension']];
        }

        return json_encode($saveDataFile);
    }

    /**
     * @param $fragment
     * @return string
     */
    protected function savedDataFragment($fragment)
    {
        $mfrag = [];

        foreach ($fragment as $key => $value) {
            $mfrag[] = $value;
        }


        return implode(',', $mfrag);

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
        $usePoster = $sdConfig['use_poster'];
        $posterClass = $sdConfig['img_placeholder_class'];
        $buttonClass = $sdConfig['button_class'];
        $wrapDiv = $sdConfig['wrapping_div'];
        $divClass = $sdConfig['wrapping_div_class'];


        return $sdOptions = [
            'message' => $message,
            'message_class' => $messageClass,
            'use_poster' => $usePoster,
            'img_placeholder_class' => $posterClass,
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
    protected function combineOptions( $config, $options = array(), $option ) {
        $configName = $this->getConfigName($config);
        $defaultOptions = $this->getOptions($configName);

        if (isset($options[$option])) {
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

        if (is_array($filename)) {
            $filename = isset($filename['filename']) ? $filename['filename'] : $filename['file'];
        }

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

//        $trackConfig = $config[ $configName ]['tracks'];
        $trackConfig = $this->checkIndex( $config[$configName], 'tracks', NULL);

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
     * @param $cfg
     */
    protected function addAssets($cfg, $saveData )
    {
        $app = $this->getContainer();

        $config = $this->getConfig();
        $configName = $this->getConfigName( $cfg );
        $defaultConfig = $this->getDefaultConfig();

//        $saveData = $this->checkConfig( $config[$configName], 'save_data', $defaultConfig );

        $extPath = $app['resources']->getUrl('extensions');

        $vendor = 'vendor/cdowdy/';
        $extName = 'html5video/';

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

    /**
     * @return array
     */
    protected function getDefaultConfig()
    {
        return [
            'default' => [
                'use_cdn' => false,
                'save_data' => false,
                'attributes' => [ 'controls' ],
                'preload' => 'metadata',
                'multiple_source' => false,
            ]
        ];
    }

}