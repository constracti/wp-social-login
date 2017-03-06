jQuery( function() {

var form = jQuery( '#loginform' ).add( '#registerform' );

var p = form.find( '.kgr-social-login-p' );

p.detach().css( 'margin-bottom', '16px' );

form.prepend( p );

} );
