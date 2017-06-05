jQuery( document ).ready( function( $ ) {

$( '.kgr-social-login-button' ).click( function() {
	var button = $( this );
	var msg = button.parent().siblings( '.description' ).html();
	if ( !confirm( msg ) )
		return false;
	$.get( button.prop( 'href' ), function( data ) {
		if ( data !== '' )
			alert( data );
		else
			location.href = '';
	} );
	return false;
} );

} );
