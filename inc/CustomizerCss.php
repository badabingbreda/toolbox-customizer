<?php

namespace ToolboxCustomizer;

class CustomizerCss {


	private $file_prefix = '';

	private $directory = '';

	private $path_to_less_file = false;

	private $version = '';

	private $final_css_url = '';

	private $final_css_path = '';

	private $use_compiler = "less";

	private $debug = false;		// output compiled variables.scss to the export path

	/**
	 * Initialize the class
	 * @param  array $settings settings for the customizer_css
	 * @return [type] [description]
	 */
	public function __construct( $settings = array() ) {

		if ( 
				!isset( $settings['file_prefix'] ) 
				&& !isset( $settings['directory'] ) 
				&& !isset( $settings['version'] ) 
			) return;

		$this->file_prefix = $settings[ 'file_prefix' ];

		$this->directory = $settings[ 'directory' ];

		$this->version = $settings[ 'version' ];

		if ( isset( $settings[ 'debug' ] ) ) {

			$this->debug = $settings[ 'debug' ];
		}

		if ( isset( $settings['path_to_less_file'] ) ) {

			$this->path_to_less_file = $settings[ 'path_to_less_file' ];

		}

		if (isset( $settings['use_compiler'] ) ) {

			switch ($settings['use_compiler']) {
				case "scss":

					$this->use_compiler = "scss";

					break;
				default:
				case "less":
					// do nothing, less is the default
					break;
			}
		}

		$this->final_css_path = $this->get_final_css_path();

		add_action( 'customize_preview_init' , array( $this , 'preview_css' ) , 1100 );

		add_action( 'customize_save_after' , array( $this , 'write_css' )  );

		// Check if the CSS exists
		// if not, create it
		if ( !file_exists( $this->final_css_path ) ) {

			// compile the CSS neede for this plugin
			add_action( 'init' , array( $this , 'write_css' ) , 1100 );

		}
		// enqueue the css
		add_action( 'wp_enqueue_scripts' , array( $this , 'enqueue_final_css' ) , 1100, 1 );



	}

	private function get_final_css_path() {
		return $this->dir_settings( $this->directory )['cache_dir'] . '/' . $this->file_prefix . '.css';
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
		add_action( 'wp_enqueue_scripts' , array( $this , 'enqueue_temp_css' ), 1000, 1 );

	}

	/**
	 * Write the temp CSS to a seperate temp-file on each change
	 * @return [type] [description]
	 */
	public function write_temp_css() {

		if ( $this->use_compiler == 'scss' ) {

			$this->parse_scss_css( $this->file_prefix . '_temp.css' , 'expanded', true );

		} else {

			$this->parse_less_css( $this->file_prefix . '_temp.css' , 'expanded', true );

		}

	}

	/**
	 * Write the final CSS when the publish button is clicked in the customizer
	 * @return [type] [description]
	 */
	public function write_css() {

		if ( ! \is_customize_preview() ) return;

		if ( $this->use_compiler == 'scss' ) {

			$this->parse_scss_css( $this->file_prefix . '.css' , $this->debug ? 'expanded' : 'compressed' , false );

		} else {
			$this->parse_less_css( $this->file_prefix . '.css' , $this->debug ? 'expanded' : 'compressed' , false );

		}
		do_action( 'toolbox_customizer_on_publish', $this->file_prefix );

	}
	
	/**
	 * use_cache
	 * 
	 * Return if saved-cache and settings are identical
	 *
	 * @return void
	 */
	public function use_cache() {

		// get the settings that we need
		$settings = apply_filters(  'toolbox_customizer_css_' . $this->file_prefix , array() );
		
		// build the cache file path
		$cache_path = $this->dir_settings( $this->directory )['cache_dir'] . '/' . $this->file_prefix . '.cache';

		// check if cache already exists
		if ( \file_exists( $cache_path ) ) {

			$cache = \file_get_contents( $cache_path );
			
			$cache_data = \json_decode( $cache , true );
			// return if the data is identical. Of so, we don't need to recompile, return false
			return ( $this->arrayRecursiveDiff( $settings , $cache_data ) == [] );

		}
		// don't use cache
		return false;
	}


	/**
	 * Parse the plugins less file with the variables that are set in the get_variables() callback
	 * @param  [type] $filename [description]
	 * @return [type]           [description]
	 */
	public function parse_scss_css( $filename , $output , $use_cache = false ) {


		if ( $use_cache && $this->use_cache() ) return;

		$return_alert = false;

		if ( !class_exists( '\ScssPhp\ScssPhp\Compiler') ) require_once( TOOLBOXCUSTOMIZER_DIR . 'vendor/autoload.php' );

		$css = '';

		$parser = new \ScssPhp\ScssPhp\Compiler();
		
		$parser->setOutputStyle( $output );

		$scss_file = ( $this->path_to_less_file ? $this->path_to_less_file : TOOLBOXCUSTOMIZER_DIR . 'scss/' ) . $this->file_prefix . '.scss';

		$less_path = '/';

		try {

			//$parser->parseFile( $less_file , $less_path );
			$parser->setImportPaths( $this->path_to_less_file ? $this->path_to_less_file : TOOLBOXCUSTOMIZER_DIR . 'scss/' );

			// create a stylesheet for the constants because passing variables did not work correctly, because of returned keywords instead of colors
			$stylesheet_variables = $this->variables_to_stylesheet( apply_filters(  'toolbox_customizer_css_' . $this->file_prefix , array() ) );

			if ( $this->debug ) $this->write_file( $this->directory , 'variables.scss' , $stylesheet_variables );
			
			// compile the css by adding the variables before the import
			$css = $parser->compile( $stylesheet_variables . '@import "'.$this->file_prefix.'.scss"' );

		} catch (\Exception $e) {

				$css = "\/* an error in the SCSS file generated an error: ". $e->getMessage() ." *\/";

				// check for TOOLBOXCUSTOMIZER_SILENT CONSTANT, if found and set to true hide compile-error messages when they occur
				// if not defined show
				// if set to false also show
				if ( !defined('TOOLBOXCUSTOMIZER_SILENT') ||  ( defined('TOOLBOXCUSTOMIZER_SILENT') && !TOOLBOXCUSTOMIZER_SILENT ) ) {

					wp_enqueue_script( $this->file_prefix . '_error' , TOOLBOXCUSTOMIZER_URL . 'js/error_alert.js' , null, TOOLBOXCUSTOMIZER_VERSION , true );

					wp_localize_script( $this->file_prefix . '_error', 'tbCustomizer' , array(
																								'compiled_css' 	=> $css,
																								'whoops'		=> __( 'Whoops, the following error occured in the SCSS files processed by the Toolbox-Customizer Plugin:', 'toolbox-customizer' ),
																								'addconstant'	=> __( 'Add a constant TOOLBOXCUSTOMIZER_SILENT to your functions.php to hide this alert.', 'toolbox-customizer' ),
																							) );

				}
		}

		$this->create_dir( $this->directory );

		$this->write_file( $this->directory , $filename , $css );

		$this->write_file( $this->directory , $this->file_prefix . '.cache' , json_encode( apply_filters(  'toolbox_customizer_css_' . $this->file_prefix , array() ) ) );


	}


	/**
	 * Parse the plugins less file with the variables that are set in the get_variables() callback
	 * @param  [type] $filename [description]
	 * @return [type]           [description]
	 */
	public function parse_less_css( $filename , $output = 'expanded', $use_cache = false ) {

		$options = array( 'compress' => ( $output == 'compressed' ) );

		if ( $use_cache && $this->use_cache() ) return;

		$return_alert = false;

		if ( !class_exists( 'Less_Parser') ) require_once( TOOLBOXCUSTOMIZER_DIR . 'vendor/autoload.php' );

		$css = '';

		$parser = new \Less_Parser( $options );

		$less_file = ( $this->path_to_less_file ? $this->path_to_less_file : TOOLBOXCUSTOMIZER_DIR . 'less/' ) . $this->file_prefix . '.less';

		$less_path = '/';

		try {


			// create a stylesheet for the constants because passing variables did not work correctly, because of returned keywords instead of colors
			$stylesheet_variables = $this->variables_to_stylesheet( apply_filters(  'toolbox_customizer_css_' . $this->file_prefix , array() ) , '@' );

			//$parser->SetImportDirs( array( [ $this->path_to_less_file => '' ] ) );

			$parser->parse( $stylesheet_variables );

			$parser->parseFile( $less_file , $less_path );

			//$parser->ModifyVars( apply_filters(  'toolbox_customizer_css_' . $this->file_prefix , array() ) );

			$css = $parser->getCss();

		} catch (Exception $e) {

				$css = "\/* an error in the LESS file generated an error: ". $e->getMessage() ." *\/";

				// check for TOOLBOXCUSTOMIZER_SILENT CONSTANT, if found and set to true hide compile-error messages when they occur
				// if not defined show
				// if set to false also show
				if ( !defined('TOOLBOXCUSTOMIZER_SILENT') ||  ( defined('TOOLBOXCUSTOMIZER_SILENT') && !TOOLBOXCUSTOMIZER_SILENT ) ) {

					wp_enqueue_script( $this->file_prefix . '_error' , TOOLBOXCUSTOMIZER_URL . 'js/error_alert.js' , null, TOOLBOXCUSTOMIZER_VERSION , true );

					wp_localize_script( $this->file_prefix . '_error', 'tbCustomizer' , array(
																								'compiled_css' 	=> $css,
																								'whoops'		=> __( 'Whoops, the following error occured in the LESS files processed by the Toolbox-Customizer Plugin:', 'toolbox-customizer' ),
																								'addconstant'	=> __( 'Add a constant TOOLBOXCUSTOMIZER_SILENT to your functions.php to hide this alert.', 'toolbox-customizer' ),
																							) );

				}
		}

		$this->create_dir( $this->directory );

		$this->write_file( $this->directory , $filename , $css );

		$this->write_file( $this->directory , $this->file_prefix . '.cache' , json_encode( apply_filters(  'toolbox_customizer_css_' . $this->file_prefix , array() ) ) );

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
	 * variables_to_stylesheet
	 *
	 * Returns an array of named variables as a stylesheet
	 * 
	 * @param  mixed $variables
	 * @return void
	 */
	private function variables_to_stylesheet( $variables , $prefix = '$' ) {
		
		$template = "%s%s: %s;\n";

		$output = '';

		foreach ($variables as $var_name=>$value) {
			$output .= sprintf( $template , $prefix , $var_name , $value );
		}

		return $output;
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
	public static function gtm( $theme_mod , $default = array(), $unit = null ) {


		$theme_mod_value = false;

		// if get_theme_mod returns a value or the value is 0 (would evaluate to false so need to do a strict check here)
		if ( get_theme_mod( $theme_mod ) || ( get_theme_mod( $theme_mod ) !== false )  ) {

			if ( isset( $default['tostring'] ) && $default['tostring'] ) {

				$theme_mod_value =  "\"". ( get_theme_mod( $theme_mod ) . $unit ) . "\"";

			} else {

				if ( $unit ) {

					$theme_mod_value = ( get_theme_mod( $theme_mod ) . $unit );
				} else {

					$theme_mod_value = ( get_theme_mod( $theme_mod ) );

				}

			}
		}

		// if theme_mod_value is false and default value is set
		if ( !$theme_mod_value && ( isset( $default['value'] ) && $default[ 'value' ] ) ) $theme_mod_value = $default['value'] . $unit;

		// pass it through the filter if set
		if ( isset( $default['filter'] ) ) {

			$theme_mod_value  = apply_filters( $default['filter'] , $theme_mod_value , $theme_mod , $unit );

		}

		// return the value
		return $theme_mod_value;

	}
	
	/**
	 * gtm_param
	 *
	 * @param  mixed $theme_mod
	 * @param  mixed $key
	 * @param  mixed $default
	 * @return void
	 */
	public static function gtm_param( $theme_mod , $key , $default) {

		$mod_val = get_theme_mod( $theme_mod , false );

		if ( $mod_val === false || !isset($mod_val[$key]) || $mod_val[$key] === '' ) return $default;
		return $mod_val[$key];
	}

	/**
	 * Enqueue the temp css
	 * @return [type] [description]
	 */
	public function enqueue_temp_css() {

		wp_enqueue_style( $this->file_prefix , wp_upload_dir()['baseurl'] . '/' .$this->directory. '/' . $this->file_prefix . '_temp.css' , null, date( 'U' ) , 'all' );

	}

	/**
	 * Enqueue the final css, use forced css-refresh when css file has been resaved
	 * @return [type] [description]
	 */
	public function enqueue_final_css() {

		if ( $this->version == -1 ) {

			$use_latest_version = filemtime( $this->final_css_path );

			wp_enqueue_style( $this->file_prefix , wp_upload_dir()['baseurl'] . '/' . $this->directory . '/' . $this->file_prefix . '.css' , null, $use_latest_version , 'all' );

		} else {

			wp_enqueue_style( $this->file_prefix , wp_upload_dir()['baseurl'] . '/' . $this->directory . '/' . $this->file_prefix . '.css' , null, $this->version, 'all' );

		}

	}
	
	/**
	 * arrayRecursiveDiff
	 * 
	 * code courtesy of php.net
	 * https://www.php.net/manual/en/function.array-diff.php#91756
	 *
	 * @param  mixed $aArray1
	 * @param  mixed $aArray2
	 * @return void
	 */
	public function arrayRecursiveDiff($aArray1, $aArray2) {
		$aReturn = array();
	  
		foreach ($aArray1 as $mKey => $mValue) {
			if (array_key_exists($mKey, $aArray2)) {
				if (is_array($mValue)) {
					$aRecursiveDiff = arrayRecursiveDiff($mValue, $aArray2[$mKey]);
					if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
				} else {
					if ($mValue != $aArray2[$mKey]) {
						$aReturn[$mKey] = $mValue;
					}
				}
			} else {
				$aReturn[$mKey] = $mValue;
			}
		}
	  
		return $aReturn;
	}	

}

