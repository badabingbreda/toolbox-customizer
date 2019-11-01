<?php

class toolbox_customizer_css {


	private $file_prefix = '';

	private $directory = '';

	private $path_to_less_file = false;

	private $version = '';

	private $final_css_url = '';
	private $final_css_path = '';

	/**
	 * Initialize the class
	 * @param  array $settings settings for the customizer_css
	 * @return [type] [description]
	 */
	public function __construct( $settings = array() ) {

		if ( !isset( $settings['file_prefix'] ) && !isset( $settings['directory'] ) && !isset( $settings['version'] ) ) return;

		$this->file_prefix = $settings[ 'file_prefix' ];

		$this->directory = $settings[ 'directory' ];

		$this->version = $settings[ 'version' ];

		if ( isset( $settings['path_to_less_file'] ) ) $this->path_to_less_file = $settings[ 'path_to_less_file' ];

		$this->final_css_path = wp_upload_dir()['baseurl'] . '/' . $this->directory . '/' . $this->file_prefix . '.css';

		add_action( 'customize_preview_init' , array( $this , 'preview_css' ) , 20 );

		// Let's add the CSS later so we can override the settings if we want
		//add_action( 'wpbf_before_customizer_css', __NAMESPACE__ . '\faithmade_css', 20 );

		// enqueue the final css only if the file exists, preventing a 404 error
		if ( file_exists( $this->dir_settings( $this->directory )['cache_dir'] . '/' . $this->file_prefix . '.css' ) ) {

			add_action( 'wp_enqueue_scripts' , array( $this , 'enqueue_final_css' ) , 1100, 1 );

		}

		add_action( 'customize_save_after' , array( $this , 'write_css' )  );

	}

	/**
	 * Return the URL for the final CSS after it has been published
	 * @return [type] [description]
	 */
	public function get_css_url() {
		return $this->final_css_url;
	}

	/**
	 * Create the temporary CSS using the mod settings and enqueue the CSS
	 * @return [type] [description]
	 */
	public function preview_css() {

		$this->write_temp_css();

		// enqueue with priority 10 so that when adding the regular css with the same handler it won't overwrite it
		add_action( 'wp_enqueue_scripts' , array( $this , 'enqueue_temp_css' ) , 1000, 1 );

	}

	/**
	 * Write the temp CSS to a seperate temp-file on each change
	 * @return [type] [description]
	 */
	public function write_temp_css() {

		$this->parse_less_css( $this->file_prefix . '_temp.css' );

	}

	/**
	 * Write the final CSS when the publish button is clicked in the customizer
	 * @return [type] [description]
	 */
	public function write_css() {

		$this->parse_less_css( $this->file_prefix . '.css' );

	}

	/**
	 * Parse the plugins less file with the variables that are set in the get_variables() callback
	 * @param  [type] $filename [description]
	 * @return [type]           [description]
	 */
	public function parse_less_css( $filename ) {


		if ( !class_exists( 'Less_Parser') ) require_once( TOOLBOXCUSTOMIZER_DIR . 'vendor/autoload.php' );

		$css = '';

		$parser = new \Less_Parser();

		$less_file = ( $this->path_to_less_file?$this->path_to_less_file:TOOLBOXCUSTOMIZER_DIR . 'less/' ) . $this->file_prefix . '.less';

		$less_path = '/';

		try {

			$parser->parseFile( $less_file , $less_path );

			$parser->ModifyVars( apply_filters(  'toolbox_customizer_css_' . $this->file_prefix , array() ) );

			$css = $parser->getCss();

		} catch (Exception $e) {

				$css = "\/* an error in the LESS file generated an error: ". $e->getMessage() ." *\/";
		}

		$this->create_dir( $this->directory );

		$this->write_file( $this->directory , $filename , $css );

	}


	/**
	 * Create a directory if needed
	 * @param  [type] $dirname [description]
	 * @return [type]          [description]
	 */
	private function create_dir( $dirname ) {

		$settings = $this->dir_settings( $dirname );

	    // create the directory if it doesn't already exist
	    if ( ! file_exists( $settings[ 'cache_dir' ] ) )  {
	    	wp_mkdir_p( $settings[ 'cache_dir' ] );
	    	chmod( $settings[ 'cache_dir' ] , 0755 );
	    }

	}

	/**
	 * Write a file with the stream
	 * @param  [type] $dirname  [description]
	 * @param  [type] $filename [description]
	 * @param  [type] $stream   [description]
	 * @return [type]           [description]
	 */
	private function write_file( $dirname , $filename , $stream ) {

		$settings = $this->dir_settings( $dirname );

	   // write the file
	    file_put_contents( $settings[ 'cache_dir' ] . '/' .  $filename  , $stream );
		chmod( $settings[ 'cache_dir' ] . '/' . $filename , 0755 );

	}


	/**
	 * Get the wp_upload_dir and set our cache dir
	 * @param  [type] $dirname [description]
	 * @return [type]          [description]
	 */
	private function dir_settings( $dirname ) {

		$upload_dir = wp_upload_dir();

		$settings = array(
									'upload_dir' => $upload_dir,
									'cache_dir'	 => $upload_dir['basedir'] . '/'. $dirname,
								);

		return $settings;
	}


	/**
	 * Get the theme_mod and if none is returned allow a default return value
	 * Also enable a appended unit string
	 *
	 * @param  [type] $theme_mod [description]
	 * @param  array  $default   [description]
	 * @param  string $unit      [description]
	 * @return [type]            [description]
	 */
	public static function gtm( $theme_mod , $default = array(), $unit = '' ) {

		// if get_theme_mod returns a value or the value is 0 (would evaluate to false so need to do a strict check here)
		if ( get_theme_mod( $theme_mod ) || !( get_theme_mod( $theme_mod ) === false )  ) {

			if ( isset( $default['tostring'] ) && $default['tostring'] ) {

				return "\"". ( get_theme_mod( $theme_mod ) . $unit ) . "\"";

			} else {

				return ( get_theme_mod( $theme_mod ) . $unit );

			}
		}

		if ( isset( $default['value'] ) && $default[ 'value' ] ) return $default['value'] . $unit;

		// return a filtered value (fm_filter_color)
		if ( isset( $default['filter'] ) ) {

			$filtered_value  = apply_filters( $default['filter'] , false );

			if ( $filtered_value ) return $filtered_value . $unit;

		}

		return false;

	}

	/**
	 * Enqueue the temp css
	 * @return [type] [description]
	 */
	public function enqueue_temp_css() {

		wp_enqueue_style( $this->file_prefix , wp_upload_dir()['baseurl'] . '/' .$this->directory. '/' . $this->file_prefix . '_temp.css' , null, date( 'U' ) , 'all' );

	}

	/**
	 * Enqueue the final css
	 * @return [type] [description]
	 */
	public function enqueue_final_css() {

		wp_enqueue_style( $this->file_prefix , wp_upload_dir()['baseurl'] . '/' . $this->directory . '/' . $this->file_prefix . '.css' , null, $this->version, 'all' );

	}

}

