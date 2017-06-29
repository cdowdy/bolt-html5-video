<?php


namespace Bolt\Extension\cdowdy\html5video\Handler;

use Silex\Application;


class CDNHandler {
	/**
	 * @var
	 */
	protected $app;

	/**
	 * @var array
	 */
	protected $_extensionConfig;
	protected $_configname;


	/**
	 * CDNHandler constructor.
	 *
	 * @param array       $_extensionConfig
	 * @param             $configname
	 * @param Application $app
	 */
	public function __construct( array $_extensionConfig, $configname, Application $app )
	{
		$this->_extensionConfig = $_extensionConfig;
		$this->_configname      = $configname;
		$this->app              = $app;
	}


	/**
	 * @param $filename
	 *
	 * @return array
	 */
	public function cdnFile( $filename )
	{

		$cdnURL = $this->_extensionConfig['cdn_url'];
		$video  = [];

		if ( $cdnURL ) {

			if ( is_array( $filename ) ) {
				foreach ( $filename as $key => $value ) {
					$pathInfo = pathinfo( $value );
					$video    += [ $cdnURL . $value => $pathInfo['extension'] ];
				}
			} else {
				$pathInfo = pathinfo( $filename );

				$video += [ $cdnURL . $filename => $pathInfo['extension'] ];
			}

		} else {


			if ( is_array( $filename ) ) {
				foreach ( $filename as $key => $value ) {
					$pathInfo = pathinfo( $value );
					$video    += [ $value => $pathInfo['extension'] ];
				}
			} else {
				$pathInfo = pathinfo( $filename );

				$video += [ $filename => $pathInfo['extension'] ];
			}

		}


		return $video;
	}

	/**
	 * @param $url
	 *
	 * @return bool
	 */
	public function checkForActualURL( $url )
	{
		$configCDNURL = $this->_extensionConfig['cdn_url'];
		// no cdn url found in the config so check if the string submitted in the template is an actual URL
		$templateURL  = parse_url( $url, PHP_URL_HOST );
		$errorMessage = "A CDN Url could not be found in the config and a proper url wasn't supplied in the template. The File used in the template is: {$url}";

		if ( ! $configCDNURL && ! $templateURL ) {
			$this->app['logger.flash']->error( 'HTML5Video:: ' . $errorMessage );

			$this->app['logger.system']->error( 'HTML5Video:: ' . $errorMessage, [ 'event' => 'extension' ] );

			return false;
		}

		return true;
	}
}