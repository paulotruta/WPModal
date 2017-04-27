<?php
/**
 * Plugin Name: WPModal
 * Description: Easy modal shortcode generator. Insert any content in a modal wrapped by any valid html tag + css selectors.
 * Author: plug_wppt
 * Version: 1.0
 * Author URI: http://www.smithstories.xyz
 * Text Domain: wpmodal
 */
class WPModal {
	/**
	 * Text domain for translations.
	 *
	 * @var string
	 */
	public $td = 'wpmodal';

	/**
	 * The location of the shortcode template.
	 *
	 * @var string
	 */
	private $shortcode_template_file = 'assets/templates/modal.tpl.php';

	/**
	 * Error logs email.
	 *
	 * @var string
	 */
	private $logs_address = '';

	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * The plugin instance pointer var.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * Allows the plugin instance to be used as a singleton across a Wordpress requests instance.
	 *
	 * @return class WPModal current active instance.
	 *
	public static function get_instance() {
		// create an object if not already instantiated, and register it in the class.
		self::$instance = (null === self::$instance) ? new self : null;
		return self::$instance;
	}

	/**
	 * The function build the basic plugin logic, filling all the necessary instance variables.
	 */
	public function __construct() {

		$this -> error_text = __( 'Something is making the system unable to correctly build a modal.', 'wpmodal' );
		// Prefix all template path variables with the plugin dir path.
		$this -> shortcode_template_file = plugin_dir_path( __FILE__ ) . $this -> shortcode_template_file;

		// Hook to make some work on plugin activation.
		// Run the activate (in this Class scope) when the plugin is activated.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		add_action( 'init', array( $this, 'pluginsLoaded' ) );

		// Checking if we are in the Wordpress Administration Interface before setting up the tinyMCE Plugin and enqueuing admin scripts.
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'setup_tinymce_plugin' ) );
			add_action( 'enqueue_admin_scripts', array( $this, 'enqueue_admin_scripts' ) );
			// Add tinymce translations.
			add_filter( 'mce_external_languages', array( $this, 'add_tinymce_translations' ) );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		return true;
	}

	/**
	 * Logs any errors to php error log, optionally sending an e-mail to the development team.
	 *
	 * @param  string  $message   The message log.
	 * @param  boolean $send_logs Wether to send to dev team by email.
	 * @return bool             Returns the option to send logs to dev team.
	 */
	private function _log( $message, $send_logs = false ) {

		$prefix = '(WPModal plugin | ' . get_site_url() . ') => ';
		$message = $prefix . $message;

		// This ensures the development team does not get crowded with debugging e-mails. Hook to the wpmodal_mail_logs filter to force all logs to be sent to the dev team.
		$send_logs = $send_logs ? apply_filters( 'wpmodal_mail_logs', false ) : false;

		// First log to local error log.
		error_log( $message, 0 );
		if ( $send_logs ) {
			// If settings says that and email should be sent to the development team, so be it.
			error_log( $message, 1, $this -> logs_address );
		}

		return $send_logs; // Return the option, might be useful...
	}

	/**
	 * The shortcode handler for wpmodal
	 *
	 * @param string $attributes The shortcode attributes declared in the tag.
	 * @return string     The generated shortcode content in string form.
	 */
	function wpmodal_shortcode_func( $attributes ) {

		$shortcode_content = '';

		return $shortcode_content;

	}

}
