<?php
namespace Badabing;

/**
 * update the plugin using custom endpoint
 *
 * @example: (in plugin file)
 *
 * new Badabing\Updater( 'https://update.badabing.nl' , __FILE__ , 'product_id' , 'option_name_for_license' );
 *
 */
class Updater {

	private $file;						// should be file that has plugin-header template
	private $plugin;
	private $basename;
	private $active;
	private $updater_url;				// url to check for the updates
	private $option_name = false;			// option name to store the license key
	private $response_timeout = 2000;	// timeout in ms
	private $product_id;				// gumroad product property
	private $license_key = '';			// issued licensekey
	private $license_info;				// license info
	private $license_info_json;			// original gumroad license request response
	public	$license_active = false;    // is the license active

	private $updater_response;			// response from the updater

	/**
	 * configure the updater only when more than 2 arguments are passed
	 *
	 * @since 	1.0.0
	 */
	public function __construct( $updater_url , $file , $product_id , $option_name = '' ) {

		// set the updater url, gumroad api url, gumroad timeout
		$this->updater_url 		= $updater_url;
		// where to get the license key from
		if ( $option_name !== '' ) $this->option_name = $option_name;

		$this->file 				= $file;
		$this->product_id 			= $product_id;
		$this->license_active		= false;

		add_action( 'admin_init', array( $this, 'set_plugin_properties' ) );
	}

	/**
	 * set the plugin properties
	 *
	 * @since 	1.0.0
	 * @return  void
	 */
	public function set_plugin_properties() {

		$this->plugin 		=	get_plugin_data( $this->file );
		$this->basename 	=	plugin_basename( $this->file );
		$this->active 		=	is_plugin_active( $this->basename );
		$this->init();

	}

	/**
	 * Initialize the updater
	 *
	 * @since 	1.0.0
	 * @param 	array $args
	 * @return 	void
	 */
	function init() {

		$this->maybe_do_license_check();

		add_filter( 'pre_set_site_transient_update_plugins', 	array( $this , 'modify_transient' ), 10, 1);
		// plugin update info
		add_filter( 'plugins_api', 								array( $this, 'plugin_popup' ), 10, 3);
		// post install rename plugin folder
		add_filter( 'upgrader_post_install', 					array( $this, 'after_install' ), 10, 3 );

	}

	function maybe_do_license_check() {

		if ( !$this->option_name ) return;
		// try to get the license key
		$this->license_key = get_option( $this->option_name , false );

		if ( $this->license_key ):
			// get the license info
			$this->get_license_info();
			if ( $this->license_info->active ) $this->license_active = true;

		endif;

		// check if this is an active license
		if ( $this->option_name && !$this->license_active ) add_action(  'in_plugin_update_message-'.$this->basename , array( $this , 'plugin_update_message' ) ,1, 2 );
		
	}

	/**
	 * register the license key
	 *
	 * @param  [type] $license_key [description]
	 * @return [type]              [description]
	 */
	public function set_license_key( $license_key ) {
		$this->license_key = $license_key;
	}

	/**
	 * modify the transient for the plugin when version number in updater is higher
	 *
	 * @since 	1.0.0
	 * @param  	object $transient
	 * @return 	object
	 */
	public function modify_transient( $transient ) {

		if ( empty( $transient->checked) ) {
			return $transient;
		}

		if ( $this->get_updater_info( ) ) :

			$slug = current( explode('/', $this->basename ) ); // Create valid slug

			$plugin = array( // setup our plugin info
				'url' 			=> $this->plugin["PluginURI"],
				'slug' 			=> $slug,
				'package' 		=> $this->updater_response['package'],
				'download_link'	=> $this->updater_response['package'],
				'new_version' 	=> $this->updater_response['new_version'],
				'tested'		=> $this->updater_response['tested'],
				'icons' 		=> isset($this->updater_response['icons']) ? $this->updater_response['icons'] : [],
				'banners'		=> isset($this->updater_response['banners']) ? $this->updater_response['banners'] : [],
				'banners_rtl'	=> [],

			);
			$transient->response[$this->basename] = (object) $plugin; // Return it in response

		endif;

		return $transient;
	}

	/**
	 * Get info from our updater
	 *
	 * @since 	1.0.0
	 * @return 	boolean
	 */
	public function get_updater_info( ) {

		global $wp_version;

		// we still need to send product_permalink to the api because older versions of toolbox will send that
		$request = array(
			'product_permalink' => $this->product_id,
			'license_key'	=>	$this->license_key,
			'version' => $this->plugin['Version'],
		);

		
		// determine if we should try to get the updater info or just the info
		// $action = ( $this->license_key == null )?'plugin_information':'plugin_update';
		$action = 'plugin_update';

		// Start checking for an update
		$send_for_check = array(
			
			'body' => array(
				'action' => $action,
				'request' => serialize( $request ),
				'api-key' => md5( get_bloginfo( 'url' ) ),
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url'),
		);
		
		$raw_response = wp_remote_post( $this->updater_url , $send_for_check );

		if ( !is_wp_error( $raw_response ) && ( $raw_response[ 'response' ][ 'code' ] == 200 ) ) $response = unserialize( $raw_response[ 'body' ] );

		// the request returned a response
		if ( !empty( $response ) ) {

			$this->updater_response = $response;
			return true;
		}
		return false;
	}

	/**
	 * Perform the gumroad license check
	 *
	 * @return 	array
	 */
	public function get_license_info() {

		global $wp_version;

		// we still need to send product_permalink to the api because older versions of toolbox will send that
		$request = array(
			'product_permalink' => $this->product_id,
			'license_key'	=>	$this->license_key,
			'version' => $this->plugin['Version'],
		);

		
		// determine if we should try to get the updater info or just the info
		// $action = ( $this->license_key == null )?'plugin_information':'plugin_update';
		$action = 'license';

		// Start checking for an update
		$send_for_check = array(
			
			'body' => array(
				'action' => $action,
				'request' => serialize( $request ),
				'api-key' => md5( get_bloginfo( 'url' ) ),
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url'),
		);
		
		$raw_response = wp_remote_post( $this->updater_url , $send_for_check );

		if ( !is_wp_error( $raw_response ) && ( $raw_response[ 'response' ][ 'code' ] == 200 ) ) $response = json_decode( $raw_response[ 'body' ] );

		// the request returned a response
		if ( !empty( $response ) ) {

			$this->license_info = $response;
		}

		return $this->license_info;

	}	

	/**
	 * Popup for the plugin update/information
	 *
	 * @param  	[type] $result [description]
	 * @param  	[type] $action [description]
	 * @param  	[type] $args   [description]
	 * @return 	object
	 */
	public function plugin_popup( $result, $action, $args ) {

		
		if( ! empty( $args->slug ) ) { // If there is a slug
			
			if( $args->slug == current( explode( '/' , $this->basename ) ) ) { // And it's our slug
				$this->get_updater_info(); // Get our repo info
				// Set it to an array
				$plugin = array(
					'name'				=> $this->plugin[ 'Name' ],
					'slug'				=> $this->basename,
					'requires'			=> $this->updater_response[ 'requires' ],
					'tested'			=> $this->updater_response[ 'tested' ],
					'rating'			=> false,
					'num_ratings'		=> 'n/a',
					'downloaded'		=> 'n/a',
					'added'				=> false,
					'version'			=> $this->updater_response[ 'new_version' ],
					'author'			=> $this->plugin[ 'AuthorName' ],
					'author_profile'	=> $this->plugin[ 'AuthorURI' ],
					'last_updated'		=> $this->updater_response[ 'version_date' ],
					'homepage'			=> $this->plugin[ 'PluginURI' ],
					'short_description' => $this->updater_response[ 'short_description'],
					'sections'			=> array(
						'Description'	=> $this->updater_response[ 'description' ],
						'Changelog'		=> $this->updater_response[ 'changelog' ],
					),
					'banners'			=> isset($this->updater_response['banners']) ? $this->updater_response['banners'] : [],
					'package' 			=> $this->updater_response['package'],
					'download_link'		=> $this->updater_response[ 'package' ],
				);

				return (object) $plugin; // Return the data
			}
		}

		return $result; // Otherwise return default
	}

	/**
	 * Plugin Update Message displays when user hasn't entered valid license yet
	 *
	 * @param  [type] $message     [description]
	 * @param  string $plugin_data [description]
	 * @param  string $r           [description]
	 * @return void
	 */
	function plugin_update_message( $data , $response ) {

		// vars
		$activate_message = sprintf(
			__('To enable updates for this plugin, you need to set an active license key.', 'badabing-updater'),
		);

		echo '<br><br><span style="display:block;background-color:red;color:white;padding:10px">' . $activate_message . '</span>';
	}

	/**
	 * rename the directory and activate the plugin accordingly
	 *
	 * @param  [type] $response   [description]
	 * @param  [type] $hook_extra [description]
	 * @param  [type] $result     [description]
	 * @return [type]             [description]
	 */
	public function after_install( $response, $hook_extra, $result ) {

		global $wp_filesystem; // Get global FS object

		// Our plugin directory
		$install_directory = plugin_dir_path( $this->file );

		// Move files to the plugin dir
		$wp_filesystem->move( $result['destination'], $install_directory );

		// Set the destination for the rest of the stack
		$result['destination'] = $install_directory;

		// If it was active, reactivate
		if ( $this->active ) activate_plugin( $this->basename );

		return $result;
	}

}