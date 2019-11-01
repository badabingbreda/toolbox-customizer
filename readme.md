## Toolbox Customizer

Use LESS to create enqueued CSS (in your uploads folder) for your WP Customizer settings, rather than inline CSS.


### How to use
It's best to keep this plugin as is, it is intented to be a dependency for your own plugin. Download and install this plugin. Activate it. It will do nothing, but you will be able to use the loaded class.

Create your own plugin, in which you add your controls to the WP Customizer using Kirki, Meta Box Settings-plugin or any other framework or method available. Toolbox Customizer works with theme_mods directly, not with frameworks so there are no additional dependencies.

In your plugin add a callback-name to the 'init' hook.

    /**
     * Initialize the styles and creation of the css file(s) when needed
     */
    add_action( 'init' , 'myplugin_add_styles' );
    function myplugin_add_styles() {

    // return early if toolbox_customizer_css doesn't exist
    if( !class_exists( 'toolbox_customizer_css' ) ) return;

    // initialize the toolbox customizer css
    $faithmade_css = new toolbox_customizer_css(
                                        array(

                                            'file_prefix'           => 'faithmade' ,                        // used for filenames and style-enqueue handler
                                                                                                            // {file_prefix}.css and {file_prefix}_temp.css

                                            'directory'             => 'faithmade_css' ,                    // directory that is used to store the compiled css files in
                                                                                                            // in /wp-content/uploads/{directory}

                                            'version'               => '1.0.0',             // version for the css, probably the version of the plugin

                                            'path_to_less_file'     => plugin_dir_path( __FILE__ ) ) . 'less/',     // probably good practice to set this one also.
                                                                                                            // local dir-path, NOT an url please
                                        )
                                );


       add_filter( 'toolbox_customizer_css_faithmade' , 'myplugin_my_variables' );
    }




This assumes the code is added to the main plugin-file. Set the path_to_less_file to match the actual path to your LESS-file. Here it expects you to have created a directory called "less" and a file called "faithmade.less".

The first to last line of that code is another filter; It's name is always of format 'toolbox_customizer_css_{file_prefix}'. So next up, is the creation of the LESS-variables, which can be used to create the CSS-file.

    function myplugin_my_variables( $variables ) {


        return array_merge( $variables ,

            array(

                // add a variable called @fm_button_color
                // for it's value use toolbox_customizer_css::gtm( $theme_mod_name , $settings , $unit )    (..get theme mod..)
                //
                // theme_mod_name:      name of the theme mod to fetch
                // settings:            the settings for the return value
                //                      'value'     if defined us this value as the return value if theme mod does not exist
                //                      'filter'    if defined use this filter to return a value when theme mos does not exist. If filter has no hooked callbacks false will be returned.
                //                      'tostring'  if defined it will return the theme mod value to a string, otherwise it will return a keyword
                //  unit                append this unit measure at the end of the value if not empty
                //

                'fm_button_color'               => toolbox_customizer_css::gtm( 'fm_button_color' , array( 'value' => false )  ),

            )

        );
    }

This creates a single variable @fm_button_color, which can be used in the less file to do stuff. As you might expect, this variable expects to return a value like `#ff000`, `#00ff00`, `rgb(255,0,0)`, `rgba(255,0,0,.2)` or `purple`. But if there is no value, it will return a static value `false`.

### The LESS file
Now all that remains is to edit the less-file located in the plugin's 'less/' directory.
Edit the file called 'faithmade.less' (or whatever you set to use as a {file_prefix}.less):

    & when ( iscolor( @fm_button_color ) ) {

        a.fl-button,
        a.fl-button:visited {
                background-color: @fm_button_color;
        }
        a.fl-button:hover {
                background-color: darken( @fm_button_color , 20% );
        }
    }

**& when** means we only want to do the code enclosed in the curly brackets when @fm_button_color is actually a color. LESS will try to work that out for you.

Of course, you can add multiple properties that need to be set, but remember that it will require that one variable to be set to a color to be applied.

Now, when you open up the customizer and change the value of the fm_button_color mod control, when it returns a color it will change the appearance of any anchor with the fl-button class. And it will also add a hover-color that is always based on the main-color that is set, instead of having the user select a darker color! Of course, you can still add seperate controls if you want to.

#### toolbox_customizer_css::gtm()

gtm() stands for Get Theme Mod

When you work with theme_mods, there is not always a value assigned. Theme mods are only that what users set it to, it's up to the creators of the theme to determine what is used as a default value when they don't change it at all. That means that we need a fallback value.

If you look at what gets the fm_button_color's value, you see

    toolbox_customizer_css::gtm()

It is a helper function that will do most of the work. It takes 3 parameters:

 - `theme_mod` option id ( required, string)
 - `settings` for the value and fallback value (optional, array):
     - `value`: static value when get_theme_mod() returns false or empty
     - `filter`: a filter-name that return a value when get_theme_mod() return false or empty
     - `tostring`: `true/false` . Convert the returned value to a string, by default it returns it as a keyword. The use-case is a little more complex, see below for details.
 - `unit` (optional, string)

#### A few examples:

**example 1:**

            // get the border width. Once set it can be 0px as well
            'fm_button_border_width'        => toolbox_customizer_css::gtm( 'fm_button_border_width' ,array( 'value'=> false ) , 'px' ),

will add a LESS-variable called @fm_button_border_width. The value will try to get the theme mod 'fm_button_border_width', which returns a number if set. If not set, return a static value `false`. If it has a value, append the unit 'px' to it.

So the value of @fm_button_border_width might be `0px`,  `2px` or something like `8px`.

Now, in the LESS-file:


      & when not (@fm_button_border_width = false){
            border-width: @fm_button_border_width;
        }

**example 2:**

            // get the google_font from the dropdown and return as a string, because otherwise we van only check for it as a keyword (not very useful in this context)
            'fm_google_font'                => toolbox_customizer_css::gtm( 'fm_google_font' , array( 'value' => false , 'tostring' => true ) ),

the helper function is expecting that the returned value is of a singular value. That usually generates a problem when working with theme mods that are returned as an array. You will need to get the required value a little differently:

Adds a LESS-variable called @fm_google_font. The value will try to get the theme mod 'fm_google_font' which might be a pulldown menu where we can select a predefined font-name. Here it would make sense to return false if there's no font-name selected and to convert the selected font-name to a string.

In the LESS-file:

    & when ( isstring( @fm_google_font ) ) {

        h1, h2, h3, h4, h5, h6 {
            font-family: e("@{fm_google_font}");
        }

    }

provided that the Google-font is also enqueued into the page's CSS-files you can change the font-family used for the headings.

**example 3:**

        // theme mod using Kirki Framework's typograhy field
        'page_h6_font_family'                       => get_theme_mod( 'page_h6_font_family' )?'"' . get_theme_mod( 'page_h6_font_family' )['font-family'] . '"':false,
        'page_h6_font_family_variant'               => get_theme_mod( 'page_h6_font_family' )?get_theme_mod( 'page_h6_font_family' )['variant']:false,


Example 3 checks if there's a theme mod for the customizer-setting and wraps it in " " so it will be seen as a string within LESS. If there's no customizer-setting set, it will be false. If you're unfamiliar with writing the value this way, [check here for more info on the ternary operator](https://www.codementor.io/sayantinideb/ternary-operator-in-php-how-to-use-the-php-ternary-operator-x0ubd3po6).

In the LESS file, you could then test for the variable being false (or rather, NOT false):

    & when ( isstring( @page_h6_font_family ) ) {
        .fl-accordion-button-label {
            font-family: @page_h6_font_family;

            & when not ( @page_h6_font_family_variant = false ) {
                font-weight: @page_h6_font_family_variant;
            }
        }
    }


### Converting a returned value with the 'tostring' setting

LESS can be used to test for various types of variables. A variable for instance can be set to be `8px`, `5%`, `1.2em`, but also `black` or `#00ff00`. You can use test like `isnumber()`, `isunit()` or `iscolor()` to check if the value can be used. If everything was passed in as a string, you wouldn't be able to do that. But it also means that a value like `blue` might at times be the word `'blue'` and not the color.
To be able to distinguish between the two, use 'tostring' as a setting. If not, it will be passed in as a keyword, which might result in unexpected behavior.

### More on LESS
If you want to learn more about using LESS, you can look here:

[http://lesscss.org/functions/#string-functions](http://lesscss.org/functions/#string-functions)

## NOTICE
This plugin uses the php port of LESS found here [https://packagist.org/packages/wikimedia/less.php](https://packagist.org/packages/wikimedia/less.php)

I have found that not all features work the way I expected them to do, so if your LESS file isn't compiled as expected, please read the docs provided there.
