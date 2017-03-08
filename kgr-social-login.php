<?php

/*
 * Plugin Name: KGR Social Login
 * Plugin URI: https://github.com/constracti/wp-social-login
 * Description: Users can register or login with their google, microsoft or yahoo account.
 * Author: constracti
 * Version: 1.2.1
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/*
 * http://oauth2-client.thephpleague.com/
 * composer require league/oauth2-google
 * composer require stevenmaguire/oauth2-microsoft
 * composer require hayageek/oauth2-yahoo
 */

if ( !defined( 'ABSPATH' ) )
	exit;

require_once( plugin_dir_path( __FILE__ ) . 'settings.php' );

function kgr_social_login_p( string $redirect_to ): string {
	$str = 'admin-ajax.php?action=kgr-social-login-%s&redirect_to=%s&login';
	$src = plugins_url( 'images' , __FILE__ );
	$html = '';
	$html .= '<p class="kgr-social-login-p">' . "\n";
	foreach ( ['google', 'microsoft', 'yahoo'] as $provider ) {
		$flag = TRUE;
		foreach ( ['client-id', 'client-secret'] as $credential )
			$flag = $flag && get_option( sprintf( 'kgr-social-login-%s-%s', $provider, $credential ), '' ) !== '';
		if ( !$flag )
			continue;
		$href = admin_url( sprintf( $str, $provider, urlencode( $redirect_to ) ) );
		$html .= sprintf( '<a href="%s" title="%s">', $href, ucfirst( $provider ) ) . "\n";
		$html .= sprintf( '<img src="%s/%s.png" alt="%s" />', $src, $provider, $provider ) . "\n";
		$html .= '</a>' . "\n";
	}
	$html .= '</p>' . "\n";
	return $html;
}

add_shortcode( 'kgr-social-login', function( $atts ): string {
	if ( is_user_logged_in() )
		return '';
	$html = '';
	if ( array_key_exists( 'prompt', $atts ) )
		$html .= sprintf( '<p>%s</p>', $atts['prompt'] ) . "\n";
	$html .= kgr_social_login_p( get_permalink() );
	return $html;
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'kgr-social-login-buttons', plugins_url( 'buttons.css', __FILE__ ) );
} );

add_action( 'login_enqueue_scripts', function() {
	wp_enqueue_style( 'kgr-social-login-buttons', plugins_url( 'buttons.css', __FILE__ ) );
	wp_enqueue_script( 'kgr-social-login-form', plugins_url( 'form.js', __FILE__ ), ['jquery'] );
} );

add_action( 'login_form', function() {
	$redirect_to = array_key_exists( 'redirect_to', $_GET ) ? $_GET['redirect_to'] : home_url();
	echo kgr_social_login_p( $redirect_to );
} );

add_action( 'register_form', function() {
	$redirect_to = array_key_exists( 'redirect_to', $_GET ) ? $_GET['redirect_to'] : admin_url();
	echo kgr_social_login_p( $redirect_to );
} );

function kgr_social_login_error( string $error ) {
	$function = apply_filters( 'wp_die_handler', '_default_wp_die_handler' );
	call_user_func( $function, $error, 'Error', [ 'back_link' => TRUE ] );
}

function kgr_social_login_callback( $provider, $scope ) {
	if ( array_key_exists( 'login', $_GET ) ) {
		if ( !array_key_exists( 'redirect_to', $_GET ) )
			kgr_social_login_error( 'redirection not set' );
		$options = [
			'scope' => $scope,
			'state' => urlencode( $_GET['redirect_to'] ),
		];
		header( 'location: ' . $provider->getAuthorizationUrl( $options ) );
		exit;
	} elseif ( array_key_exists( 'code', $_GET ) ) {
		if ( !array_key_exists( 'state', $_GET ) )
			kgr_social_login_error( 'state parameter not set' );
		$token = $provider->getAccessToken( 'authorization_code', ['code' => $_GET['code']] );
		$owner = $provider->getResourceOwner( $token );
		$email = $owner->getEmail();
		$user = get_user_by( 'email', $email );
		if ( $user !== FALSE ) {
			$user_id = $user->ID;
		} elseif ( intval( get_option( 'users_can_register' ) ) === 1 ) {
			$pref = substr( $email, 0, strpos( $email, '@' ) );
			$cnt = NULL;
			do {
				if ( is_null( $cnt ) ) {
					$login = $pref;
					$cnt = 0;
				} else {
					$login = sprintf( '%s%d', $pref, $cnt );
					$cnt++;
				}
			} while ( username_exists( $login ) );
			$user_id = register_new_user( $login, $email );
		} else {
			kgr_social_login_error( 'new users can\'t register' );
		}
		wp_set_auth_cookie( $user_id, TRUE ); # TODO option remember
		header( 'location: ' . urldecode( $_GET['state'] ) );
		exit;
	} elseif ( array_key_exists( 'error', $_GET ) ) {
		kgr_social_login_error( sprintf( 'authentication %s', $_GET['error'] ) );
	} else {
		kgr_social_login_error( 'invalid function invocation' );
	}
}

add_action( 'wp_ajax_nopriv_kgr-social-login-google', function() {
	require_once( plugin_dir_path( __FILE__ ) . 'google/vendor/autoload.php' );
	$provider = new League\OAuth2\Client\Provider\Google( [
		'clientId'     => get_option( 'kgr-social-login-google-client-id' ),
		'clientSecret' => get_option( 'kgr-social-login-google-client-secret' ),
		'redirectUri'  => admin_url( 'admin-ajax.php?action=kgr-social-login-google' ),
	] );
	kgr_social_login_callback( $provider, ['email'] );
} );

add_action( 'wp_ajax_nopriv_kgr-social-login-microsoft', function() {
	require_once( plugin_dir_path( __FILE__ ) . 'microsoft/vendor/autoload.php' );
	$provider = new Stevenmaguire\OAuth2\Client\Provider\Microsoft( [
		'clientId'     => get_option( 'kgr-social-login-microsoft-client-id' ),
		'clientSecret' => get_option( 'kgr-social-login-microsoft-client-secret' ),
		'redirectUri'  => admin_url( 'admin-ajax.php?action=kgr-social-login-microsoft' ),
	] );
	kgr_social_login_callback( $provider, ['wl.emails'] );
} );

add_action( 'wp_ajax_nopriv_kgr-social-login-yahoo', function() {
	require_once( plugin_dir_path( __FILE__ ) . 'yahoo/vendor/autoload.php' );
	$provider = new Hayageek\OAuth2\Client\Provider\Yahoo( [
		'clientId'     => get_option( 'kgr-social-login-yahoo-client-id' ),
		'clientSecret' => get_option( 'kgr-social-login-yahoo-client-secret' ),
		'redirectUri'  => admin_url( 'admin-ajax.php?action=kgr-social-login-yahoo' ),
	] );
	kgr_social_login_callback( $provider, ['openid'] );
} );
