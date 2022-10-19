<?php

namespace ToolboxCustomizer;
use ToolboxCustomizer\CustomizerCss;
use ToolboxCustomizer\Beaverbuilder;

class Init {

    public function __construct() {

        // 
        new Beaverbuilder();

        /**
         * Add Twig Filters used in this plugin
         */
        add_filter( 'timber/twig'                       , __CLASS__ . '::add_twig_filters' );

        /**
         * Filter to get imagesizes
         */
        add_filter( 'toolbox-customizer/helpers/imagesizes' , __CLASS__ . '::get_image_sizes' );

    }

    /**
     * Callback that add a twig filter to Twig
     * @param [type] $twig [description]
     */
    public static function add_twig_filters( $twig ) {

        /**
         * Get a theme_mod value for use inside a twig template
        * @var [type]
        */
        $twig->addFunction( new \Timber\Twig_Function( 'toolbox_gtm'  , function( $theme_mod , $default = null ) {

            if ( $default ) return get_theme_mod( $theme_mod , $default );

            return get_theme_mod( $theme_mod );

        } ) );

        return $twig;
    }

    /**
    * Get all intermediate image sizes currently registered in WP
    *
    * @type   function
    * @since  1.6.3
    * @param  array    $array  function for filter, passed in array
    * @return array
    */
    public static function get_image_sizes( $array ) {

        $sizes = get_intermediate_image_sizes();

        foreach ($sizes as $size ) {
            $data[$size] = $size;
        }

        $data[ 'full' ] = __( 'full' , 'toolbox-customizer' );

        return $data;

    }




}


