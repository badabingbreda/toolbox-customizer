=== Toolbox Customizer ===
Author URI: https://www.badabing.nl
Plugin URI: https://www.beaverplugins.com/toolbox-customizer
Contributors: BadabingBreda
Tags: Plugins, Customizer, CSS
Requires at least: 3.5
Tested up to: 5.9.3
Stable Tag: 1.0.1

Compile static CSS from customizer settings instead of inline

== Description ==

This plugin allows you to add CSS-styles for your Customizer settings based on a LESS file. LESS is highly efficient and can be used to write rules for
when certain CSS properties need to be added, or not. LESS is also specifically aimed at working with CSS properties, so that you can work faster and smarter
with colors and units.

== Installation ==

1. Activate the plugin
2. Open the Customizer and use the settings to change the look of your page or pages
3. Click publish to commit the changes to a seperate CSS file that will be enqueued rather than created inline on each page-call

== Changelog ==

= 2.1.1 = 

SCSS and debug enabled
Added priority to settings

= 1.6.5 =

Fixed mistake in regex that matches hex-color

= 1.6.4 =

Added regex check for hex-color. We need colors to return without the #

= 1.6.3 =

Added action 'toolbox_customizer_on_publish' on Customizer Publish so that callbacks can be run that clear asset cache.
Added separate toolbox-customizer connector for photo fields. This tries to get media from ID, Array or URL.

= 1.6.2 =

Added Logic Rules for Beaver Builder, ability to render nodes if theme-mod isset, notset, equals, does_not_equal

= 1.6.1 =

Removed last apply_filter in toolbox_customizer_css::gtm(), which is called statically and not as an instance.

= 1.6.0 =

Added 2 parameters to the filter that can be specified through toolbox_customizer_css::gtm();
You can pass in $theme_mod and $unit as second and third parameter to process through a filter.
Made the get_theme_mod(), 'value' and 'filter' to test individually so that 'filter' gets run if set, making it possible to change the behavior even if
the theme_mod is set by the user.

= 1.5.1 =

Fix for version 1.5.0, lastchanged-check in css-file needs to be done on local path, not url

= 1.5.0 =

Added ability to force reload when css gets rebuilt. Use version = -1 as parameter in toolbox_customizer_css() class.

= 1.4.0 =

Added an alert-box that pops up when LESS files result in a compile-error. Downside is that you will receive a message everytime time you make a change.
You can define a constant TOOLBOXCUSTOMIZER_SILENT, if found and set to true it will not show the error in the Customizer.

= 1.3.0 =

Added Twig-function 'toolbox_gtm()' for easier access to theme mods in Twig templates. This is a helper function that parses the get_theme_mod( $mod , (optional) $default ) function.
Added Theme Mod String connector. Connect theme mods by entering their mod name, fallback return value and optional appended string.

= 1.2.0 =

* Wrapped CSS compiling in try-catch to prevent error 500's from occuring. Compiler errors will be printed to the CSS

= 1.1.0 =

* Priority for loading .CSS and temp.CSS upped to 1000, to load after builder, theme and themer css

= 1.0.1 =

* Small update to readme.md for improved readability

= 1.0.0 =

* Initial release!
