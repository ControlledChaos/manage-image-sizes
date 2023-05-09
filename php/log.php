<?php
/**
 * Log classes
 *
 * @package    Manage_Image_Sizes
 * @subpackage Classes
 * @category   Log
 * @since      1.0.0
 *
 * @todo From the original author:
 *
 * * Find the best place for the require log (only when it's really needed, create an init function?).
 * * Change all the log calls?
 * * Rip out everything that's not a CONSTANT or a hook in here.
 * * Make this an object.
 * * Add a tour for new users.
 */

namespace MISP\Classes\Log;

use function MISP\get_plugin_options;
use function MISP\misp_tmp_dir;

class PteLogMessage {

	public static $ERROR = 1;
	public static $WARN  = 2;
	public static $INFO  = 4;
	public static $DEBUG = 8;
	protected $message;
	protected $type;
	protected $date;

	private function get_type_string() {

		switch ( $this->type ) {
			case self :: $ERROR:
				return __( 'ERROR', 'manage-image-sizes' );
				break;
			case self :: $WARN:
				return __( 'WARNING', 'manage-image-sizes' );
				break;
			case self :: $INFO:
				return __( 'INFO', 'manage-image-sizes' );
				break;
			default:
				return __( 'DEBUG', 'manage-image-sizes' );
		}
	}

	/**
	 * Constructor method
	 *
	 * @since  PTE 2.4.8
	 * @access public
	 * @param  string $type
	 * @param  string $message
	 * @return self
	 */
	public function __construct( $type, $message ) {

		if ( ! is_int( $type ) || ! ( $type & self :: max_log_level() ) ) {
			throw new Exception( "Invalid Log Type: '{$type}'" );
		}
		$this->type    = $type;
		$this->message = trim( $message );
		$this->date    = time();
	}

	public function __to_string() {

		$type = $this->get_type_string();

		return sprintf(
			'[%-7s][%s][ %s ]',
			$type,
		   	gmdate( 'c', $this->date ),
			$this->message
		);
	}

	public function getType(){
		return $this->type;
	}

	public function getMessage(){
		return $this->message;
	}

	public static function max_log_level(){
		return self :: $ERROR | self :: $WARN | self :: $INFO | self :: $DEBUG;
	}
}

interface PteLogHandler {
	public function handle( PteLogMessage $message );
}

class PteLogChromeHandler implements PteLogHandler {

	protected $chrome = null;

	/**
	 * Constructor method
	 *
	 * @since  PTE 2.4.8
	 * @access public
	 * @return self
	 */
	public function __construct() {

		if ( ! class_exists( 'ChromePhp' ) ) {
			require_once( MISP_PATH . 'php/chromephp/ChromePhp.php' );
		}
		ChromePhp :: getInstance()->addSetting( ChromePhp :: BACKTRACE_LEVEL, 5 );
	}

	/**
	 * Using ChromePhp, log the message
	 */
	public function handle( PteLogMessage $message ) {

		switch ( $message->getType() ) {
			case PteLogMessage :: $ERROR:
				ChromePhp :: error( $message->getMessage() );
				break;
			case PteLogMessage :: $WARN:
				ChromePhp :: warn( $message->getMessage() );
				break;
			case PteLogMessage :: $INFO:
				ChromePhp :: info( $message->getMessage() );
				break;
			case PteLogMessage :: $DEBUG:
			default:
				ChromePhp :: log( $message->getMessage() );
				break;
		}
	}
}

class PteLogFileHandler implements PteLogHandler {

	protected $filename;
	protected $lines = 0;

	/**
	 * Constructor method
	 *
	 * @since  PTE 2.4.8
	 * @access public
	 * @return self
	 */
	public function __construct() {

		$this->filename = self :: getLogFileName();
		wp_mkdir_p( dirname( $this->filename ) );
		touch( $this->filename );
	}

	public static function getLogFileUrl() {

		// Sets MISP_TMP_DIR and MISP_TMP_URL.
		extract( misp_tmp_dir() );
		return $MISP_TMP_URL . 'log.txt';
	}

	public static function getLogFileName() {

		// Sets MISP_TMP_DIR and MISP_TMP_URL.
		extract( misp_tmp_dir() );
		return $MISP_TMP_DIR . 'log.txt';
	}

	private function logAndTruncate( $message ) {

		$content = file( $this->filename, FILE_IGNORE_NEW_LINES );
		if ( $content === false ) {
			$content = array();
		}

		$content = array_merge( $content, explode( "\n", (string) $message ) );

		if ( count( $content ) > $this->lines ) {
			$content = array_slice( $content, $this->lines * -1);
		}
		file_put_contents( $this->filename, implode( "\n", $content ) );
	}

	public function handle( PteLogMessage $message ) {

		if ( isset( $this->lines ) && $this->lines ) {
			logAndTruncate( $message );
		}

		// Append to file.
		$fp = fopen( $this->filename, 'a+' );
		fwrite( $fp, $message . "\n" );
		fclose( $fp );
	}

}

class PteLogger implements PteLogHandler {

	private static $instance;
	private $messages    = array();
	private $counts      = array();
	//private $defaulttype = 4;
	//private $defaulttype = PteLogMessage :: $DEBUG;
	private $defaulttype = null;
	private $handlers = array();

	/**
	 * Constructor method
	 *
	 * @since  PTE 2.4.8
	 * @access private
	 * @return self
	 */
	private function __construct() {

		$this->defaulttype = PteLogMessage :: $DEBUG;

		$options = get_plugin_options();

		// Add chrome log handler.
		if ( $options['misp_debug_out_chrome'] ) {
			$this->handlers[] = new PteLogChromeHandler;
		}

		// Add file log handler.
		if ( $options['misp_debug_out_file'] ) {
			$this->handlers[] = new PteLogFileHandler;
		}

		$this->handlers[] = $this;
	}

	/**
	 * Class instance
	 *
	 * @access public
	 * @return self
	 */
	public static function singleton() {

		if ( ! isset( self :: $instance ) ) {
			$className = __CLASS__;
			self :: $instance = new $className();
		}
		return self :: $instance;
	}

	/**
	 * Message handler
	 *
	 * @access public
	 * @param  PteLogMessage $message
	 * @return void
	 */
	public function handle( PteLogMessage $message ) {

		// self::singleton()->chrome_log( $message );
		$type = $message->getType();

		if ( ! isset( $this->counts[ $type ] ) ) {
			$this->counts[ $message->getType() ] = 1;
		} else {
			$this->counts[ $message->getType() ]++;
		}
		$this->messages[] = $message;
	}

	/**
	 * Add message
	 *
	 * @access private
	 * @param  string $message
	 * @return void
	 */
	private function add_message( $message ) {
		foreach ( $this->handlers as $handler ) {
			$handler->handle( $message );
		}
	}

	/**
	 * Get log count
	 *
	 * @access public
	 * @param  string $type
	 * @return array
	 */
	public function get_log_count( $type ) {

		if (
			! isset( $this->counts[ $type ] ) ||
			! is_int( $this->counts[ $type ] )
		) {
			return 0;
		}
		return $this->counts[$type];
	}

	/**
	 * Log
	 *
	 * @access private
	 * @param  string $message
	 * @param  string $type
	 * @return mixed
	 */
	private function misp_log( $message, $type = null ) {

		if ( ! $message instanceof PteLogMessage ) {

			if ( is_string( $message ) ) {

				if ( is_null( $type ) ) {
					$type = $this->defaulttype;
				} try {
					$message = new PteLogMessage( $type, $message );
				} catch ( Exception $e ) {
					printf(
						__( 'ERROR Logging Message: %s', 'manage-image-sizes' ),
						$message
					);
				}
			} else {
				return false;
			}
		}

		/**
		 * If debug isn't enabled only track WARN and ERROR
		 * messages (throw away DEBUG messages).
		 */
		$options = get_plugin_options();
		if ( ! $options['misp_debug'] and $type == PteLogMessage :: $DEBUG ){
			return false;
		}

		$this->add_message( $message );
		return true;
	}

	/**
	 * Log error message
	 *
	 * @access public
	 * @param  string $message
	 * @return void
	 */
	public static function error( $message ) {
		self :: singleton()->misp_log( $message, PteLogMessage :: $ERROR );
	}

	/**
	 * Log warning message
	 *
	 * @access public
	 * @param  string $message
	 * @return void
	 */
	public static function warn( $message ) {
		self :: singleton()->misp_log( $message, PteLogMessage :: $WARN );
	}

	/**
	 * Log debug message
	 *
	 * @access public
	 * @param  string $message
	 * @return void
	 */
	public static function debug( $message ) {
		self :: singleton()->misp_log( $message, PteLogMessage :: $DEBUG );
	}

	/**
	 * Get logs
	 *
	 * @param  array $levels
	 * @return array
	 */
	public function get_logs( $levels = null ) {

		// Check that $levels is valid.
		$max = PteLogMessage :: max_log_level();
		if ( ! is_int( $levels ) or $levels < 0 or $levels > $max ) {
			$levels = $max;
		}

		foreach ( $this->messages as $message ) {

			// If the current Level is requested, add to output.
			if ( $levels & $message->getType() ) {
				$output[] = $message->__to_string();
			}
		}
		return $output;
	}
}
