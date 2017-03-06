jQuery( function() {

jQuery( '#kgr-social-login-clear' ).click( function() {
	if ( !confirm( 'Clear all credentials?' ) )
		return false;
	jQuery.get( jQuery( this ).prop( 'href' ), function() {
		location.href = '';
	} );
	return false;
} );

} );
