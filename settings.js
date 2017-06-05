jQuery( document ).ready( function( $ ) {

$( '.kgr-social-login-button' ).click( function() {
	var button = $( this );
	if ( button.attr( 'disabled' ) )
		return false;
	var description = button.siblings( '.description' ).html();
	if ( !confirm( description ) )
		return false;
	button.attr( 'disabled', true );
	var spinner = button.siblings( '.spinner' );
	spinner.addClass( 'is-active' );
	$.get( button.prop( 'href' ) ).success( function( data ) {
		if ( data !== '' )
			console.log( data );
		else
			location.href = '';
	} ).always( function() {
		spinner.removeClass( 'is-active' );
		button.attr( 'disabled', false );
	} );
	return false;
} );

} );
