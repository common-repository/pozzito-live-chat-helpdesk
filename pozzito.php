<?php

/**
 * WP POZZITO Plugin v1.1
 * Author: Alen Begovic - POZZITO TEAM
 *
 * Copyright (c) 2017 POZZITO (www.pozzito.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package WordPress
 */


define( 'POZZITO_ADMIN_CUSTOM_STYLE', 'css/override-style.css' );
define( 'POZZITO_ADMIN_POZZITO_STYLE', 'css/override-style.css' );
define( 'POZZITO_WIDGET_SCRIPT_FILE', 'js/wp-pozzito-widget.js' );
define( 'POZZITO_SETTINGS_SCRIPT_FILE', 'js/wp-pozzito-settings.js' );

class PozzitoPlugin {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $app_id;
	private $api_key;

	/**
	 * Prepare scripts
	 */
	public function __construct() {

		// PREPARE POZZITO WIDGET SCRIPT BUT DONT RUN IT
		add_action( 'wp_footer', array( $this, 'initialize_pozzito_config' ) );

		// PREPARE POZZITO SETTINGS SCRIPT BUT DONT RUN IT
		add_action( 'wp_footer', array( $this, 'initialize_pozzito_widget' ) );

	}

	/**
	 * Pozzito PHP > JS KEY exchange (apply keys)
	 */
	public function initialize_pozzito_config() {

			   // Set class property
		$this->app_id = get_option( 'pozzito-app-id' );
		$this->api_key = get_option( 'pozzito-api-key' );

		// Create Pozzito Settings Script for global var with APP ID and API KEY
		$this->create_pozzito_settings_js( $this->app_id, $this->api_key );

		// Load Pozzito Config Script
		$this->wp_pozzito_load_settings();
	}

	/**
	 * Pozzito API KEY & APP ID - Create Settings JS File
	 */
	public function create_pozzito_settings_js( $app_id, $api_key ) {

		// Construct Settings => Pozzito Config JS Object
		$settings_string = ' var pozzitoConfig = { apiKey : "' . $api_key . '", appId : "' . $app_id . '" }; ';

		file_put_contents( plugin_dir_path( __FILE__ ) . POZZITO_SETTINGS_SCRIPT_FILE, $settings_string );
		$response = file_exists( plugin_dir_path( __FILE__ ) . POZZITO_SETTINGS_SCRIPT_FILE );

	}

	/**
	 * Load Pozzito Settings (APP ID & API KEY)
	 */
	public function wp_pozzito_load_settings() {

		// Pozzito settings js file path
		$pozzito_settings_file = plugin_dir_path( __FILE__ ) . POZZITO_SETTINGS_SCRIPT_FILE;
		$pozzito_inline_content = null;

		// Read file and load content
		if ( file_exists( $pozzito_settings_file ) ) {
			$pozzito_inline_content = file_get_contents( $pozzito_settings_file );
		}

		// Load Pozzito Config as Inline Script in Worpdress
		add_action( 'wp_footer',  printf( "<script type='text/javascript'>%s</script>", $pozzito_inline_content ) );

	}

	/**
	 * Include Pozzito Main JS Widget
	 */
	public function initialize_pozzito_widget() {

		// Pozzito widget js file path
		$pozzito_widget_file = plugin_dir_path( __FILE__ ) . POZZITO_WIDGET_SCRIPT_FILE;
		$pozzito_inline_content = null;

		// Read file and load content
		if ( file_exists( $pozzito_widget_file ) ) {
			$pozzito_inline_content = file_get_contents( $pozzito_widget_file );
		}

		// Load Pozzito Widget as Inline Script
		add_action( 'wp_footer',  printf( "<script type='text/javascript'>%s</script>", $pozzito_inline_content ) );

	}

}


class PozzitoAdmin {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $app_id;
	private $api_key;

	/**
	 * Start up & main callbacks
	 */
	public function __construct() {

		// Create Pozzito Plugin Admin Options Menu
		add_action( 'admin_menu', array( $this, 'pozzito_options_menu' ) );

		// Display and handle Input fields and DB options
		add_action( 'admin_init', array( $this, 'initialize_pozzito_settings' ) );

		// Custom Admin Menu Icon (Options page)
		add_action( 'admin_head', array( $this, 'option_page_icon' ) );

		// CUSTOM ADMIN PAGE STYLES
		add_action( 'admin_head', array( $this, 'register_custom_styles' ) );
	}


	/**
	 * Custom function - replacement for
	 * original mime_content_type() beacuse of mime_magic extension need
	 */
	public function _mime_content_type( $filename ) {
		$result = new finfo();

		if ( is_resource( $result ) === true ) {
			return $result->file( $filename, FILEINFO_MIME_TYPE );
		}

		return false;
	}


	/**
	 * Add plugin main admin OPTIONS menu
	 */
	public function load_svg_data_uri() {

		// A few settings
		$image = 'logo.png';
		$imagePath = plugin_dir_path( __FILE__ ) . 'img/';

		// Check is file existing in plugin
		if ( ! file_exists( $imagePath . $image ) ) {
			return false;
		}

		// Read image path, convert to base64 encoding
		$imageData = base64_encode( file_get_contents( $imagePath . $image ) );

		// Get MIME type
		$mime = $this->_mime_content_type( $imagePath . $image );

		// Format the image SRC:  data:{mime};base64,{data};
		$data_uri = 'data:' . $mime . ';base64,' . $imageData;

				// $data_uri = 'data:img/png;base64,'.$imageData;
		return  $data_uri;
	}


	/**
	 * Add plugin main admin OPTIONS menu
	 */
	public function pozzito_options_menu() {
		// This page will add Pozztio Options to  WP Root menu
		// Try to resolve custom logo image
		$logo_exits = $this->load_svg_data_uri();

		$page_title = 'Pozzito Plugin options';
		$menu_title = 'Pozzito Options';
		$capability = 'manage_options';
		$menu_slug  = 'pozzito-options-menu';
		$callback_function   = 'pozzito_options_page_html';
		// $logo_image   = 'img/logo.png';
		$menu_position   = null; // 4

		add_menu_page(
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			array( $this, $callback_function ),
			$logo_exits,
			$menu_position
		);
	}


	/**
	 * Add plugin main admin SETTINGS menu
	 */
	public function pozzito_settings_menu() {
		// This page will be under "WP Settings"
		$page_title = 'Pozzito Plugin Settings';
		$menu_title = 'Pozzito Settings';
		$capability = 'manage_options';
		$menu_slug  = 'pozzito-settings-menu';
		$callback_function   = 'pozzito_settings_page_html';

		add_options_page(
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			array( $this, $callback_function )
		);
	}


	/**
	 * Options admin page callback
	 */
	public function pozzito_options_page_html() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		/**
		* LOAD CUSTOM CSS STYLES
		*/
		// $this->load_custom_styles();
		?>

		<div class="wrap">
			<div class="container">
				<h1>Pozzito Plugin Options</h1>
				<!-- DISPLAY ADMIN NOTICE & ERROR MESSAGES -->
				<?php settings_errors(); ?> 

			<?php $this->options_form_html(); ?>


			</div> <!-- CLOSE CUSTOM STYLE -->                
		</div> <!-- CLOSE POZZITO OPTIONS PAGE -->

				<?php

				$this->set_pozzito_keys();
	}

	/**
	 * Options Pozzito HTML Form
	 */
	private function options_form_html() {
		echo "  <div class='card'>
                    <form id='contact' action='http://www.pozzito.com/#start' method='post'>";

		echo "          <h1>Engage with Your Customer!</h1>
                        <h2>Pozzito is the safest, easiest way to help your customer in real time!</h2>                        
                        <p> If you're not a registered user at <a href='http://www.pozzito.com'>Pozzito.com</a>, please create a new account: </p>                        
                        <fieldset>
                            <input type='submit' value='Register' class='button' />
                        </fieldset>                                      
                        <br>
                    </form>
                </div> <!-- END FORM 1 CARD DIV -->
            ";

						echo "  <div class='card'>
                    <form id='contact' action='options.php' method='post'>" ;

											// This prints out all hidden setting fields
						settings_fields( 'pozzito-settings-group' );

												// Prints out all settings sections added to a particular settings page.
						do_settings_sections( 'pozzito-settings-group' );

		echo "        <h2>Hello dear Pozzito user!</h2>
                        <p>To fill API KEY & API ID fields, go to Pozzito account and under 'Administration' tab, choose 'Chat' channel. Copy API KEY and API ID from 'Widget Code' section, then return to WordPress Pozzito Plugin Options and paste it. After changes, press 'Save'.</p>
                        <fieldset>
                            <table class='form-table'>
                                <tbody>
                                    <tr>
                                        <th><label for='pozzito-api-key'>API KEY:</label></th>
                                        <td><input id='pozzito-api-key' placeholder='Pozzito API KEY' name='pozzito-api-key' type='text'  class='regular-text' value='" . get_option( 'pozzito-api-key' ) . "' tabindex='2' required></td>
                                    </tr>
                                    <tr>
                                        <th><label for='pozzito-app-id'>APP ID:</label></th>
                                        <td><input id='pozzito-app-id' placeholder='Pozzito APP ID' name='pozzito-app-id' type='text'  class='regular-text' value='" . get_option( 'pozzito-app-id' ) . "'  tabindex='3' required></td>
                                    </tr>
                                    <tr>
                                        <th>
                                        </th>
                                        <td><input type='submit' value='Save' name='submit' class='button button-primary'></td>
                                    </tr>
                                </tbody>                            
                            </table>
                        </fieldset>
                        <br>
                        <hr>
                        <p>If you want the details on installing the widget on WordPress, get our <a href='https://pozzito.com/support/wordpress'>Manual</a></p>    
                    </form>
                </div> <!-- END FORM 2 CARD DIV -->
              ";
	}

	/**
	 * Pozzito PHP > JS KEY exchange (apply keys)
	 */
	public function set_pozzito_keys() {
		$this->app_id = get_option( 'pozzito-app-id' );
		$this->api_key = get_option( 'pozzito-api-key' );
	}


	/**
	 * Options page callback
	 */
	public function pozzito_settings_page_html() {
		// Set class property
		// $this->options = get_option( 'my_option_name' );
		?>
		<div class="wrap">
			<h1>Pozzito Plugin Settings</h1>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'pozzito-settings-group' );
			do_settings_sections( 'pozzito-settings-group' );
			submit_button();
		?>
			</form>
		</div>
		<?php

	}

	/**
	 * Register to add Options to WP DB
	 */
	public function initialize_pozzito_settings() {

		// Create new options in DB
		register_setting(
			'pozzito-settings-group', // Option group
			'pozzito-app-id', // Option name
			array( $this, 'sanitize' ), // Sanitize
			true // Show in REST
		);

		// Create new options in DB
		register_setting(
			'pozzito-settings-group', // Option group
			'pozzito-api-key', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		$new_input = array();

		if ( isset( $input['pozzito-app-id'] ) ) {
			$new_input['pozzito-app-id'] = absint( $input['pozzito-app-id'] );
		}

		if ( isset( $input['pozzito-app-id'] ) ) {
			$new_input['pozzito-app-id'] = sanitize_text_field( $input['pozzito-app-id'] );
		}

		if ( isset( $input['pozzito-api-key'] ) ) {
			$new_input['pozzito-api-key'] = absint( $input['pozzito-api-key'] );
		}

		if ( isset( $input['pozzito-api-key'] ) ) {
			$new_input['pozzito-api-key'] = sanitize_text_field( $input['pozzito-api-key'] );
		}

		return $input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		print 'Enter your settings below:';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function id_number_callback() {
		printf(
			'<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
			isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function title_callback() {
		printf(
			'<input type="text" id="title" name="my_option_name[title]" value="%s" />',
			isset( $this->options['title'] ) ? esc_attr( $this->options['title'] ) : ''
		);
	}

	/**
	 * Custom Post Type Icon for Admin Menu & Post Screen
	 */
	public function custom_post_type_icon() {
		?>
		<style>
			/* Admin Menu - 16px */
			#menu-posts-YOUR_POSTTYPE_NAME .wp-menu-image {
				background: url(<?php bloginfo( 'template_url' ); ?>/img/icon-adminmenu16-sprite.png) no-repeat 6px 6px !important;
			}
			#menu-posts-YOUR_POSTTYPE_NAME:hover .wp-menu-image, #menu-posts-YOUR_POSTTYPE_NAME.wp-has-current-submenu .wp-menu-image {
				background-position: 6px -26px !important;
			}
			/* Post Screen - 32px */
			.icon32-posts-YOUR_POSTTYPE_NAME {
				background: url(<?php bloginfo( 'template_url' ); ?>/img/icon-adminpage32.png) no-repeat left top !important;
			}
			@media
			only screen and (-webkit-min-device-pixel-ratio: 1.5),
			only screen and (   min--moz-device-pixel-ratio: 1.5),
			only screen and (     -o-min-device-pixel-ratio: 3/2),
			only screen and (        min-device-pixel-ratio: 1.5),
			only screen and (                min-resolution: 1.5dppx) {

				/* Admin Menu - 16px @2x */
				#menu-posts-YOUR_POSTTYPE_NAME .wp-menu-image {
					background-image: url('<?php bloginfo( 'template_url' ); ?>/img/icon-adminmenu16-sprite_2x.png') !important;
					-webkit-background-size: 16px 48px;
					-moz-background-size: 16px 48px;
					background-size: 16px 48px;
				}
				/* Post Screen - 32px @2x */
				.icon32-posts-YOUR_POSTTYPE_NAME {
					background-image: url('<?php bloginfo( 'template_url' ); ?>/img/icon-adminpage32_2x.png') !important;
					-webkit-background-size: 32px 32px;
					-moz-background-size: 32px 32px;
					background-size: 32px 32px;
				}
			}
		</style>

	<?php
	}

	/**
	 * Option Page Icon for Admin Menu & Option Screen
	 */
	public function option_page_icon() {
		?>

		<style>
			/* Admin Menu - 16px
			Use only if you put your plugin or option page in the top level via add_menu_page()
			*/
			#toplevel_page_PLUGINNAME-FILENAME .wp-menu-image {
				/*background: url(<?php bloginfo( 'template_url' ); ?>/images/icon-adminmenu16-sprite.png) no-repeat 6px 6px !important;*/
				background: url(<?php echo plugin_dir_url( __FILE__ ); ?>img/icon-adminmenu16-sprite.png) no-repeat 6px 6px !important;                 
			}
			/* We need to hide the generic.png img element inserted by default */
			#toplevel_page_PLUGINNAME-FILENAME .wp-menu-image img {
				display: none;
			}
			#toplevel_page_PLUGINNAME-FILENAME:hover .wp-menu-image, #toplevel_page_PLUGINNAME-FILENAME.wp-has-current-submenu .wp-menu-image {
				background-position: 6px -26px !important;
			}

			/* Option Screen - 32px */
			#PLUGINNAME.icon32 {
				/*background: url(<?php bloginfo( 'template_url' ); ?>/images/icon-adminpage32.png) no-repeat left top !important;*/
				background: url(<?php echo plugin_dir_url( __FILE__ ); ?>img/icon-adminpage32.png) no-repeat left top !important;

			}

			@media
			only screen and (-webkit-min-device-pixel-ratio: 1.5),
			only screen and (   min--moz-device-pixel-ratio: 1.5),
			only screen and (     -o-min-device-pixel-ratio: 3/2),
			only screen and (        min-device-pixel-ratio: 1.5),
			only screen and (                min-resolution: 1.5dppx) {
				/* Admin Menu - 16px @2x
				Use only if you put your plugin or option page in the top level via add_menu_page()
				*/
				#toplevel_page_PLUGINNAME-FILENAME .wp-menu-image {
					/*background-image: url('<?php bloginfo( 'template_url' ); ?>/images/icon-adminmenu16-sprite_2x.png') !important;*/
					background-image: url(<?php plugin_dir_url( __FILE__ ); ?>img/icon-adminmenu16-sprite_2x.png) !important;
					-webkit-background-size: 16px 48px;
					-moz-background-size: 16px 48px;
					background-size: 16px 48px;
				}

				/* Option Screen - 32px @2x */
				#PLUGINNAME.icon32 {
					/*background-image: url('<?php bloginfo( 'template_url' ); ?>/images/icon-adminpage32_2x.png') !important;*/
					background-image: url(<?php plugin_dir_url( __FILE__ ); ?>img/icon-adminpage32_2x.png) !important;
					-webkit-background-size: 32px 32px;
					-moz-background-size: 32px 32px;
					background-size: 32px 32px;
				}
			}
		</style>

	<?php
	}


	/**
	 * Option Page Custom CSS Register (Preaper to Load on Demand )
	 */
	public function register_custom_styles() {
		wp_register_style( 'custom_namespace', plugins_url( POZZITO_ADMIN_CUSTOM_STYLE, __FILE__ ) );
		wp_register_style( 'pozzito_namespace', plugins_url( POZZITO_ADMIN_POZZITO_STYLE, __FILE__ ) );
	}

	/**
	 * Option Page Custom CSS loader (Load on Demand )
	 */
	public function load_custom_styles( $pozzito_style = null ) {
		if ( $pozzito_style ) {
			return  wp_enqueue_style( 'pozzito_namespace' );
		} else {
			return  wp_enqueue_style( 'custom_namespace' );
		}
	}
}

?>
