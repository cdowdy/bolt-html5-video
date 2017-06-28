<?php

namespace Bolt\Extension\cdowdy\html5video;

use Bolt\Asset\Snippet\Snippet;
use Bolt\Asset\Target;
use Bolt\Controller\Zone;
use Bolt\Extension\cdowdy\html5video\Provider\HTML5FieldProvider;
use Bolt\Extension\cdowdy\html5video\Handler\CDNHandler;
use Bolt\Extension\SimpleExtension;
use Bolt\Library as Lib;

/**
 * Html5Video extension class.
 *
 * @author Cory Dowdy <cory@corydowdy.com>
 */
class Html5VideoExtension extends SimpleExtension {




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
		$options = [ 'is_safe' => [ 'html' ], 'safe' => true ];
		$this->getConfig();

		return [
			'html5video' => [ 'html5video', $options ],
		];
	}

	public function getServiceProviders()
	{
		return [
			$this,
			new HTML5FieldProvider()
		];
	}


	/**
	 * The callback function when {{ html5video() }} is used in a template.
	 *
	 * @param       $file
	 * @param       $name
	 * @param array $options
	 *
	 * @return string
	 */
	public function html5video( $file, $name = 'default', array $options = array() )
	{


		// get the config file name if using one. otherwise its 'default'
		$configName = $this->getConfigName( $name );

		// check for config settings
		$defaultOptions = $this->getOptions( $configName );
		$defaultConfig  = $this->getDefaultConfig();

		/*
		 * Merge the options set in either 'default' or named config with the options passed in through the twig template
		 */
		$mergedOptions = array_merge( $defaultOptions, $options );
//        $attributes = $this->combineOptions($configName, $options, 'attributes');
		$attributes = $this->checkConfig( $mergedOptions, 'attributes', $defaultConfig );

		$poster        = $mergedOptions['video_poster'];
		$isCDN         = $mergedOptions['use_cdn'];
		$preload       = $mergedOptions['preload'];
		$widthHeight   = $this->checkIndex( $mergedOptions, 'width_height', null );
		$mediaFragment = $mergedOptions['media_fragment'];


		$videoID        = $mergedOptions['video_id'];

		// get tracks if present
		$tracks = $mergedOptions['tracks'];

		// class passed through the twig template
		$templateClass = $this->checkIndex( $options, 'class', null );
		// classes in the config
//        $classes = $defaultOptions['class'];
		$classes = $this->checkIndex( $defaultOptions, 'class', null );

		if ( $templateClass && $classes ) {
			$htmlClass = array_merge( $classes, $templateClass );
		} elseif ( $templateClass ) {
			$htmlClass = $templateClass;
		} else {
			$htmlClass = $classes;
		}


		$singleVid  = $this->videoFile( $file, $isCDN );

		$context = [
			'singleSrc'   => $singleVid,
			'poster'      => $poster,
			'preload'     => $preload,
			'widthHeight' => $widthHeight,
			'attributes'  => $attributes,
			'class'       => $htmlClass,
			'video_id'    => $videoID,
			'is_cdn'      => $isCDN,
			'tracks'      => $tracks,
			'fragment'    => $mediaFragment
		];

		return $this->renderTemplate( 'video.twig', $context );
	}


	/**
	 * @param $configOption
	 * @param $configType
	 * @param $defaultConfig
	 *
	 * @return mixed
	 */
	protected function checkConfig( $configOption, $configType, $defaultConfig )
	{
		return ( isset( $configOption[ $configType ] ) ? $configOption[ $configType ] : $defaultConfig['default'][ $configType ] );
	}

	/**
	 * @param $option
	 * @param $optionType
	 * @param $fallback
	 *
	 * @return mixed
	 */
	protected function checkIndex( $option, $optionType, $fallback )
	{
		return ( isset( $option[ $optionType ] ) ? $option[ $optionType ] : $fallback );
	}


	/**
	 * @param $name
	 *
	 * @return string
	 *
	 * get the config name. If no name is passed in the twig function then use
	 * the default settings in our config file under defaults
	 */
	function getConfigName( $name )
	{

		if ( empty( $name ) ) {

			$configName = 'default';

		} else {

			$configName = $name;

		}

		return $configName;
	}


	/**
	 * @param $config
	 * @param $type
	 *
	 * @return mixed
	 *
	 *  get the HTML Class or ID set in the config and pass this to the
	 *  getOptions method :)
	 */
	protected function getClassID( $config, $type )
	{
		$cfg        = $this->getConfig();
		$configName = $this->getConfigName( $config );


		return isset( $cfg[ $configName ][ $type ] ) ? $cfg[ $configName ][ $type ] : null;

	}


	/**
	 * @param $config
	 *
	 * @return array
	 *
	 * gather all the config options and config option methods
	 * create one array that is then passed into the  twig callback function for HTML5 video
	 */
	function getOptions( $config )
	{
		$cfg           = $this->getConfig();
		$configName    = $this->getConfigName( $config );
		$defaultConfig = $this->getDefaultConfig();

//        $cdn = isset($cfg[$configName ]['use_cdn']) ;
		$cdn = $this->checkConfig( $cfg[ $configName ], 'use_cdn', $defaultConfig );
//        $videoID = $cfg[ $configName ]['video_id'];





//        $attributes = $cfg[ $configName ]['attributes'];
		$attributes  = $this->checkConfig( $cfg[ $configName ], 'attributes', $defaultConfig );
		$preload     = $this->checkConfig( $cfg[ $configName ], 'preload', $defaultConfig );
		$widthHeight = $this->checkIndex( $cfg[ $configName ], 'width_height', null );


		$poster        = $this->checkIndex( $cfg[ $configName ], 'video_poster', null );
		$mediaFragment = $this->mediaFragment( $configName );
		$tracks        = $this->vidTracks( $configName );

		$class   = $this->getClassID( $configName, 'class' );
		$videoID = $this->getClassID( $configName, 'video_id' );



		$defaults = [
			'use_cdn'         => $cdn,
			'video_id'        => $videoID,
			'class'           => $class,
//            'video_id' => $id,
			'attributes'      => $attributes,
			'preload'         => $preload,
			'width_height'    => $widthHeight,
			'video_poster'    => $poster,
			'media_fragment'  => $mediaFragment,
			'tracks'          => $tracks
		];

		return $defaults;
	}


	/**
	 * @param       $config
	 * @param array $options
	 * @param       $option
	 *
	 * @return array|mixed
	 */
	protected function combineOptions( $config, $options = array(), $option )
	{
		$configName     = $this->getConfigName( $config );
		$defaultOptions = $this->getOptions( $configName );

		if ( isset( $options[ $option ] ) ) {
			$combined = array_merge( $defaultOptions[ $option ], $options[ $option ] );
		} else {
			$combined = $defaultOptions[ $option ];
		}

		return $combined;
	}


	/**
	 * Our custom field gives us a json encoded array. Here we'll decode that and set the bool for associative
	 * array to true.
	 * Then check to see if the filename is in fact an array. If it's not it came in as a string from a template
	 * or a contentType editor.
	 * @param $filename
	 *
	 * @return array
	 */
	protected function createVideoSrcArray( $filename )
	{
		$app      = $this->getContainer();
		$filePath = $app['resources']->getUrl( 'files' );
		$file     = json_decode( $filename, true );

		$videoFile = [];

		if ( is_array( $filename ) ) {

			foreach ( $file as $key => $value ) {
				$videoFile += [ $key => $filePath . $value['filename'] ];
			}

		} else {
			// this is a "string" passed in from the template. It doesn't come from our HTML5 video custom field
			// Like: {{ html5video( 'sw_crit_fixed.webm', 'default' ) }}
			$videoFile[] = $filePath . $filename;
		}


		return $videoFile;
	}

	/**
	 * @param $filename
	 *
	 * @return string
	 */
	protected function cdnFile( $filename )
	{
		$confg = $this->getConfig();

		if ( is_array( $filename ) ) {
			$filename = isset( $filename['filename'] ) ? $filename['filename'] : $filename['file'];
		}

//        $useCDN = $options['use_cdn'];

		$cdnURL    = $confg['cdn_url'];
		$cdnPrefix = $this->prefixCDNURL( $cdnURL );

		if ( $cdnURL ) {
			$video = $cdnURL . $filename;
		} else {

			$video = $this->prefixCDNURL( $filename );
		}

		return $video;
	}


	/**
	 * @param $cfg
	 *
	 * @return mixed
	 */
	protected function vidTracks( $cfg )
	{
		$config     = $this->getConfig();
		$configName = $this->getConfigName( $cfg );

//        $trackConfig = $config[ $configName ]['tracks'];
		$trackConfig = $this->checkIndex( $config[ $configName ], 'tracks', null );

		return $trackConfig;
	}

	/**
	 * @param $cfg
	 *
	 * @return mixed
	 */
	protected function mediaFragment( $cfg )
	{
		$config     = $this->getConfig();
		$configName = $this->getConfigName( $cfg );

		$mediaFragment = isset( $config[ $configName ]['media_fragment'] ) ? $config[ $configName ]['media_fragment'] : null;

		return $mediaFragment;
	}


	// since we can pass a CDN URL to our twig function in addition to
	// the files from either {{ record.videoFile }} or from the files directory.. ie. 'site/files/video.webm'
	// we'll get the host of the string(filename) if it exists.
	/**
	 * @param $string
	 *
	 * @return string
	 */
	public function getHost( $string )
	{
		$url = parse_url( trim( $string ) );

		return trim( $url['host'] ? $url['host'] : array_shift( explode( '/', $url['path'], 2 ) ) );
	}


	/**
	 * @return array
	 */
	protected function getDefaultConfig()
	{
		return [
			'default' => [
				'use_cdn'         => false,
				'attributes'      => [ 'controls' ],
				'preload'         => 'metadata',
			]
		];
	}



}