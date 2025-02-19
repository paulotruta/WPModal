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
	 */
	public static function get_instance() {
		// create an object if not already instantiated, and register it in the class.
		self::$instance = (null === self::$instance) ? new self : null;
		return self::$instance;
	}

	/**
	 * The function build the basic plugin logic, filling all the necessary instance variables.
	 */
	public function __construct() {

		// error_log("WPMODAL: Constructing plugin.");

		$this -> error_text = __( 'Something is making the system unable to correctly build a modal.', 'wpmodal' );
		// Prefix all template path variables with the plugin dir path.
		$this -> shortcode_template_file = plugin_dir_path( __FILE__ ) . $this -> shortcode_template_file;

		// Hook to make some work on plugin activation.
		// Run the activate (in this Class scope) when the plugin is activated.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Add the locale loading action.
		add_action( 'init', array( $this, 'load_locale' ) );

		// Checking if we are in the Wordpress Administration Interface before setting up the tinyMCE Plugin and enqueuing admin scripts.
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'setup_tinymce_plugin' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			// Add tinymce translations.
			add_filter( 'mce_external_languages', array( $this, 'add_tinymce_translations' ) );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_footer', array( $this, 'wpmodal_generator_func' ) ); // This method will generate all the necessary modals for the given page.

		// Defining settings page for this plugin.
		add_action( 'admin_menu', array( $this, 'wpmodal_add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'wpmodal_settings_init' ) );
		// add_action( 'wp_enqueue_scripts', array( $this, 'wpa_inspect_styles'), 9999 );

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
	 * @param string $content The inner content of the shortcode tags.
	 * @return string     The generated shortcode content in string form.
	 */
	function wpmodal_shortcode_func( $attributes, $content = null ) {

		$modal_type = $content ? '' : 'bootstrap';

		// Although not in the tinyMCE button configuration, this shortcode also accepts the following parameters:
		// - type [bootstrap or generic] (to force load a specific modal type).
		// - size [modal-sm or modal-lg] (The modal size class from bootstrap (if applicable)).
		$defaults = array(
			'id'    => '',
			'label' => '',
			'tag' => '',
			'classes' => '',
			'modal_title' => '',
			'modal_picture' => '',
			'type'  => $modal_type,
			'size'  => '',
		);

		$attributes = array_merge( $defaults, $attributes );


		global $modal_vars; // This global is necessary in order to post process the shortcode using the wpmodal_generator_func method.
		if ( ! isset( $modal_vars['modals'] ) ) {
			$modal_vars['modals'] = array();
		}
		$modal_vars['modals'][] = array_merge(
			$attributes,
			array(
				'inner_content' => $content,
			)
		);

		$classes = empty( $attributes['classes'] ) ? '' : ' class="' . esc_attr( $attributes['classes'] ) . '"';
		$modal_index = count( $modal_vars['modals'] ) - 1;
		$link = '<' . $attributes['tag'] . ' data-toggle="modal" data-target="#wpmodal-' . esc_attr( $modal_index ) . '"' . $classes . '>' . $attributes['label'] . '</' . $attributes['tag'] . '>';
		return $link;

	}

	/**
	 * Builds the necessary markup to output to the footer (where the modal definition should always be).
	 */
	function wpmodal_generator_func() {

		global $modal_vars; // Access the global created by wp_modal_shortcode_func method, containing all information about all the existant modals for this page.

		if ( isset( $modal_vars['modals'] ) && is_array( $modal_vars['modals'] ) ) {
			foreach ( $modal_vars['modals'] as $i => $atts ) {
				include( $this -> shortcode_template_file );
			} // End foreach().
		} // End if().
	}

	/**
	 * Setup locale method
	 */
	public function load_locale() {
		load_plugin_textdomain( $this->td, false, dirname( plugin_basename( __FILE__ ) ) . '/translations' );
	}

	/**
	 * Activation function - Can be used to trigger operations upon activating the plugin.
	 */
	public function activate() {
		// Nothing to do here... for now.
	}

  	/**
  	 *	Filters to register the plugin with tinyMCE.
  	 */
  	function setup_tinymce_plugin() {
  		// Check if the logged in user can edit posts or pages.
  		if( ! current_user_can( 'edit_posts' ) || ! current_user_can( 'edit_pages' ) ){
  			return;
  		}

  		// Check if the logged in WordPress user has the Visual Editor enabled. In contrary, the plugin is not necessary.
  		if( get_user_option( 'rich_editing' ) !== 'true') {
  			return;
  		}

  		// Register the necessary filters.
  		add_filter( 'mce_external_plugins', array( &$this, 'add_tinymce_plugin' ) );
  		add_filter( 'mce_buttons', array( &$this, 'add_tinymce_button' ) );
  	}

  	/**
  	 * This method adds a TinyMCE plugin compatible Javascript file to the TinyMCE / Visual Editor instance.
  	 *
  	 * @param array $arr Array of TinyMCE Plugins.
  	 * @return array Modified array with the new plugin to register.
  	 */
  	function add_tinymce_plugin( $arr ) {

  		$arr['wpmodal'] = plugin_dir_url( __FILE__ ) . 'assets/js/tinymce-plugin.js';
  		return $arr;

  	}

  	/**
  	 * Includes a TinyMCE toolbar button wich the user can click to generate a modal.
  	 *
  	 * @param array $buttons The registered TinyMCE buttons array.
  	 * @return array The new array with the new button config.
  	 */
  	function add_tinymce_button( $buttons ) {

  		array_push( $buttons, '|', 'wpmodal');
  		return $buttons;

  	}

  	/**
  	 * This function composes the action of adding custom translations for TinyMCE.
  	 * Impl reference: https://codex.wordpress.org/Plugin_API/Filter_Reference/mce_external_languages#Example
	 *
	 * @param Array $arr The current TinyMCE locales array.
	 * @return Array The array with the new translations setup.
	 */
  	function add_tinymce_translations( $arr ) {

  		// TIP: you can pass instance variables inside the translations and access them later in js.
  		// Check the /translations/js/pt.php for example reference.
  		error_log("Adding translations.");
  		$arr['wpmodal'] = plugin_dir_path( __FILE__ ) . '/translations/js/pt.php';
  		return $arr;

  	}

  	/**
  	 * Enqueue scripts destined only for wp-admin.
  	 */
  	public function enqueue_admin_scripts() {

  		// Register select2 lib intro Wordpress (to be used by the TinyMCE plugin).
  		wp_register_style( 'select2css', plugin_dir_url( __FILE__ ) . 'assets/js/vendor/select2/css/select2.min.css', array(), '4.0.3' );
  		wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . 'assets/js/vendor/select2/js/select2.full.min.js', array( 'jquery' ), '4.0.3' );

  		// Register javascript and css for this plugin.
  		wp_register_style( 'wpmodal_admin_styles', plugin_dir_url( __FILE__ ) . 'assets/css/style_admin.css' );
  		wp_register_script( 'wpmodal_admin_scripts', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', array( 'jquery' ) );

  		// TIP: Admin scripts can be translated using wp_localize_script.
  		// wp_localize_script( 'wp_modal_admin_scripts', 'vars', array( 'translatable_key' => __('Translatable string', $this -> td ) ) );

  		wp_enqueue_style( 'select2css' );
  		wp_enqueue_script( 'select2' );

  		wp_enqueue_style( 'wpmodal_admin_styles' );
  		wp_enqueue_script( 'wpmodal_admin_scripts' );

  	}

  	function wpa_inspect_styles(){
	    global $wp_styles;
	    var_dump( $wp_styles );
	}

  	/**
  	 * Enqueue scripts for the frontend.
  	 */
  	public function enqueue_scripts() {

  		$generic_modal = false;

  		$options = get_option( 'wpmodal_settings' );
  		if( isset( $options['wpmodal_select_field_2'] ) && ( $options['wpmodal_select_field_2'] > 2 ) ){
  			$generic_modal = true;
  		}

  		if( $options['wpmodal_select_field_2'] == 0 ) {
  			// Try to autodetect bootstrap.
  			$style = 'bootstrap';
			if( ( ! wp_style_is( $style, 'queue' ) ) && ( ! wp_style_is( $style, 'done' ) ) ) {
				$generic_modal = false;
			}
  		}
  		
  		wp_register_style( 'wpmodal_styles', plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
  		if( $generic_modal ) wp_register_style( 'jquery_modal_css', plugin_dir_url( __FILE__ ) . 'assets/js/vendor/jquery_modal/jquery.modal.min.css' );
  		wp_register_script( 'wpmodal_scripts', plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array( 'jquery' ), '1.0', true );
  		if( $generic_modal ) wp_register_script( 'jquery_modal', plugin_dir_url( __FILE__ ) . 'assets/js/vendor/jquery_modal/jquery.modal.min.js', array( 'jquery' ), '1.0', true );


  		// Localize the script with new data
		$translation_array = array(
			'generic_modal' => $generic_modal,
		);
		wp_localize_script( 'wpmodal_scripts', 'context', $translation_array );


  		wp_enqueue_style( 'wpmodal_styles' );
  		wp_enqueue_script( 'wpmodal_scripts' );
  		wp_enqueue_style( 'jquery_modal_css' );
  		wp_enqueue_script( 'jquery_modal' );

  	}


	function wpmodal_add_admin_menu(  ) { 

		add_options_page( 'WPModal', 'WPModal', 'manage_options', 'wpmodal', array($this, 'wpmodal_options_page' ) );

	}


	function wpmodal_settings_init(  ) { 

		register_setting( 'pluginPage', 'wpmodal_settings' );

		add_settings_section(
			'wpmodal_pluginPage_section', 
			__( 'Configuration page', 'wpmodal' ), 
			array( $this, 'wpmodal_settings_section_callback' ), 
			'pluginPage'
		);

		// add_settings_field( 
		// 	'wpmodal_checkbox_field_0', 
		// 	__( 'Disable auto bootstrap theme detection', 'wpmodal' ), 
		// 	array( $this, 'wpmodal_checkbox_field_0_render' ), 
		// 	'pluginPage', 
		// 	'wpmodal_pluginPage_section' 
		// );

		// add_settings_field( 
		// 	'wpmodal_checkbox_field_1', 
		// 	__( 'Settings field description', 'wpmodal' ), 
		// 	array( $this, 'wpmodal_checkbox_field_1_render' ), 
		// 	'pluginPage', 
		// 	'wpmodal_pluginPage_section' 
		// );

		add_settings_field( 
			'wpmodal_select_field_2', 
			__( 'Use the following render method', 'wpmodal' ), 
			array( $this, 'wpmodal_select_field_2_render' ), 
			'pluginPage', 
			'wpmodal_pluginPage_section' 
		);


	}


	function wpmodal_checkbox_field_0_render(  ) { 

		$options = get_option( 'wpmodal_settings' );
		?>
		<input type='checkbox' name='wpmodal_settings[wpmodal_checkbox_field_0]' <?php checked( $options['wpmodal_checkbox_field_0'], 1 ); ?> value='1'>
		<?php

	}


	function wpmodal_checkbox_field_1_render(  ) { 

		$options = get_option( 'wpmodal_settings' );
		?>
		<input type='checkbox' name='wpmodal_settings[wpmodal_checkbox_field_1]' <?php checked( $options['wpmodal_checkbox_field_1'], 1 ); ?> value='1'>
		<?php

	}


	function wpmodal_select_field_2_render(  ) { 

		$options = get_option( 'wpmodal_settings' );
		?>
		<select name='wpmodal_settings[wpmodal_select_field_2]'>
			<option value='1' <?php selected( $options['wpmodal_select_field_2'], 1 ); ?>>Autodetect</option>
			<option value='2' <?php selected( $options['wpmodal_select_field_2'], 2 ); ?>>Bootstrap</option>
			<option value='3' <?php selected( $options['wpmodal_select_field_2'], 3 ); ?>>Jquery Modal</option>
		</select>

	<?php

	}


	function wpmodal_settings_section_callback(  ) { 

		echo __( 'This plugin is compatible with Bootstrap Themes out-of-the box.', 'wpmodal' );

		?>
			<br>
		<?php


		echo __( 'You can disable bootstrap detection below. You can also force the render method used.', 'wpmodal' );

	}


	function wpmodal_options_page(  ) { 

		?>
		<form action='options.php' method='post'>

			<h2>WPModal</h2>

			<?php
			settings_fields( 'pluginPage' );
			do_settings_sections( 'pluginPage' );
			submit_button();
			?>

		</form>
		<?php

	}

} // End class WPModal.
// Initialize the plugin class by instantiating the shortcode function!
add_shortcode( 'wpmodal', array( WPModal::get_instance(), 'wpmodal_shortcode_func' ) );
?>