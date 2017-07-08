jQuery( document ).ready( function( $ ) {

var form = $( '#loginform' ).add( '#registerform' );

var p = form.find( '.kgr-social-login' );

p.detach().css( 'margin-bottom', '16px' );

form.prepend( p );

} );
