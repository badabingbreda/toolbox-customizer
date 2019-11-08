( function() {
	alert( 'Whoops, the following error occured in the LESS files processed by the Toolbox-Customizer Plugin:\n\n' +
			tbCustomizer.compiled_css +
			'\n\nAdd a constant TOOLBOXCUSTOMIZER_SILENT to your functions.php to hide this alert.' );
})();