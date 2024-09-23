<?php
/**
 * Toolbox Customizer
 *
 * @package     Toolbox Customizer
 * @author      Badabingbreda
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Toolbox Customizer
 * Plugin URI:  https://www.badabing.nl
 * Description: Create plugin based Customizer styles that work
 * Version:     2.2.1
 * Author:      Badabingbreda
 * Author URI:  https://www.badabing.nl
 * Text Domain: toolbox-customizer
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */


use ToolboxCustomizer\Autoloader;
use ToolboxCustomizer\Init;


 if ( defined( 'ABSPATH' ) && ! defined( 'TOOLBOXCUSTOMIZER_VERSION' ) ) {
	register_activation_hook( __FILE__, 'TOOLBOXCUSTOMIZER_check_php_version' );

	/**
	 * Display notice for old PHP version.
	 */
	function TOOLBOXCUSTOMIZER_check_php_version() {
		if ( version_compare( phpversion(), '5.3', '<' ) ) {
			die( esc_html__( 'Toolbox Customizer requires PHP version 5.3+. Please contact your host to upgrade.', 'toolbox-customizer' ) );
		}
	}

    define( 'TOOLBOXCUSTOMIZER_VERSION' 	, '2.2.1' );
    define( 'TOOLBOXCUSTOMIZER_DIR'		, plugin_dir_path( __FILE__ ) );
    define( 'TOOLBOXCUSTOMIZER_FILE'	, __FILE__ );
    define( 'TOOLBOXCUSTOMIZER_URL' 	, plugins_url( '/', __FILE__ ) );

    define( 'CHECK_TOOLBOXCUSTOMIZER_PLUGIN_FILE', __FILE__ );

}

if ( ! class_exists( 'ToolboxCustomizer\Init' ) ) {

	/**
	 * The file where the Autoloader class is defined.
	 */
	require_once 'inc/Autoloader.php';

    // load the class so other plugins can initialize it
	require_once 'inc/CustomizerCss.php';

	require_once( 'badabing-updater.php' );

	spl_autoload_register( array( new Autoloader(), 'autoload' ) );

	new Init();

	new Badabing\Updater( 'https://update.badabing.nl' , __FILE__ , '6cRa9j2RC8CaY7k' , 'toolbox-customizer-license' );
 
}

