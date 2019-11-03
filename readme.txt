=== Toolbox Customizer ===
Author URI: https://www.badabing.nl
Plugin URI: https://www.beaverplugins.com/toolbox-customizer
Contributors: BadabingBreda
Tags: Plugins, Customizer, CSS
Requires at least: 3.5
Tested up to: 5.2.3
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
