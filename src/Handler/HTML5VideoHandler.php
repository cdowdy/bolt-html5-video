<?php

namespace Bolt\Extension\cdowdy\html5video\Handler;

use Silex\Application;

class HTML5VideoHandler {
	/**
	 * @var
	 */
	protected $app;

	/**
	 * @var array
	 */
	protected $_extensionConfig;
	protected $_configname;

	protected $_normalizedFiles;

	protected $_filenames;


	public function __construct( array $_extensionConfig, $configname, Application $app )
	{
		$this->_extensionConfig = $_extensionConfig;
		$this->_configname      = $configname;
		$this->app              = $app;
	}

	/**
	 * @return mixed
	 */
	public function getFilenames()
	{
		return $this->_filenames;
	}

	/**
	 * @param mixed $filenames
	 *
	 * @return HTML5VideoHandler
	 */
	public function setFilenames( $filenames )
	{
		$this->_filenames = $filenames;

		return $this;
	}


	/**
	 * @param $file
	 *
	 * @return mixed|string
	 * for backwards compatablity we use this to see if we are still using the old type of "fileList". That comes
	 * to us as an array instead of a json_encoded object.
	 */
	public function endcodeData( $file )
	{
		return is_string( $file ) && is_array( json_decode( $file, true ) ) ? json_decode( $file, true ) : $file;
	}


	/**
	 * @param $files
	 *
	 * @return array
	 */
	public function videoSources( $files )
	{
		$filePath     = $this->app['resources']->getUrl( 'files' );
		$videoSources = [];
		$passedFiles  = $this->endcodeData( $files );

		if ( is_array( $passedFiles ) ) {

			foreach ( $passedFiles as $key => $value ) {
				$pathInfo     = pathinfo( $filePath . $value['filename'] );
				$videoSources += [ $filePath . $value['filename'] => $pathInfo['extension'] ];
			}

		} else {
			$pathInfo = pathinfo( $filePath . $passedFiles );
			// this is a "string" passed in from the template. It doesn't come from our HTML5 video custom field
			// Like: {{ html5video( 'sw_crit_fixed.webm', 'default' ) }}
			$videoSources += [ $filePath . $passedFiles => $pathInfo['extension'] ];
		}

		return $videoSources;

	}


}