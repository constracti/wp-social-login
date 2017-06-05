jQuery( document ).ready( function( $ ) {

$( '#kgr-social-login-clear' ).click( function() {
	if ( !confirm( 'Clear all credentials?' ) )
		return false;
	$.get( $( this ).prop( 'href' ), function() {
		location.href = '';
	} );
	return false;
} );

} );
