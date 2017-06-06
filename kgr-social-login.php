<?php

/*
 * Plugin Name: KGR Social Login
 * Plugin URI: https://github.com/constracti/wp-social-login
 * Description: Users can register or login with their google, microsoft or yahoo account.
 * Author: constracti
 * Version: 1.4.4
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// http://oauth2-client.thephpleague.com/

if ( !defined( 'ABSPATH' ) )
	exit;

# TODO .htaccess hide files and directories

define( 'KGR_SOCIAL_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KGR_SOCIAL_LOGIN_URL', plugin_dir_url( __FILE__ ) );

$kgr_social_login_providers = [
	'google' => [
		'composer' => 'league/oauth2-google',
		'label' => 'Google',
		'section' => function() {
			$redirect_url = admin_url( 'admin-ajax.php?action=kgr-social-login-google' );
			echo '<a href="https://github.com/thephpleague/oauth2-google" target="_blank">github</a>' . "\n" .
				'<span>|</span>' . "\n" .
				'<a href="https://console.developers.google.com/" target="_blank">applications</a>' . "\n" .
				'<span>|</span>' . "\n" .
				'<a href="https://myaccount.google.com/permissions" target="_blank">permissions</a>' . "\n";
			echo '<ol>' . "\n" .
				'<li>Enable <i>Google+ API</i>.</li>' . "\n" .
				'<li>Create <i>OAuth client ID</i> and choose type <i>Web application</i>.</li>' . "\n" .
				sprintf( '<li>Add <code>%s</code> to <i>Authorized JavaScript origins</i>.</li>', home_url() ) . "\n" .
				sprintf( '<li>Add <code>%s</code> to <i>Authorized redirect URIs</i>.</li>', $redirect_url ) . "\n" .
				'</ol>' . "\n";
		},
	],
	'microsoft' => [
		'composer' => 'stevenmaguire/oauth2-microsoft',
		'label' => 'Microsoft',
		'section' => function() {
			$redirect_url = admin_url( 'admin-ajax.php' );
			echo '<a href="https://github.com/stevenmaguire/oauth2-microsoft" target="_blank">github</a>' . "\n" .
				'<span>|</span>' . "\n" .
				'<a href="https://apps.dev.microsoft.com/" target="_blank">applications</a>' . "\n" .
				'<span>|</span>' . "\n" .
				'<a href="https://account.live.com/consent/Manage" target="_blank">permissions</a>' . "\n";
			echo '<ol>' . "\n" .
				'<li>Add a <i>Web</i> platform.</li>' . "\n" .
				sprintf( '<li>Add <code>%s</code> to <i>Redirect URIs</i>.</li>', $redirect_url ) . "\n" .
				'</ol>' . "\n";
		},
	],
	'yahoo' => [
		'composer' => 'hayageek/oauth2-yahoo',
		'label' => 'Yahoo',
		'section' => function() {
			echo '<a href="https://github.com/hayageek/oauth2-yahoo" target="_blank">github</a>' . "\n" .
				'<span>|</span>' . "\n" .
				'<a href="https://developer.yahoo.com/apps/" target="_blank">applications</a>' . "\n" .
				'<span>|</span>' . "\n" .
				'<a href="https://login.yahoo.com/account/activity" target="_blank">permissions</a>' . "\n";
			echo '<ol>' . "\n" .
				'<li>Create a <i>Web Application</i>.</li>' . "\n" .
				sprintf( '<li>Set <code>%s</code> as the <i>Callback Domain</i>.</li>', home_url() ) . "\n" .
				'</ol>' . "\n";
		},
	],
];

$kgr_social_login_credentials = [
	'client-id' => 'Client ID',
	'client-secret' => 'Client secret',
];

require_once( KGR_SOCIAL_LOGIN_DIR . 'settings.php' );
require_once( KGR_SOCIAL_LOGIN_DIR . 'widget.php' );

function kgr_social_login_p( string $redirect_to = '' ): string {
	if ( $redirect_to === '' )
		$redirect_to = sprintf( '%s://%s%s',
			$_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
			$_SERVER['SERVER_NAME'],
			$_SERVER['REQUEST_URI']
		);
	$html = '';
	$html .= '<p class="kgr-social-login-p">' . "\n";
	global $kgr_social_login_providers;
	foreach ( array_keys( $kgr_social_login_providers ) as $provider ) {
		$flag = TRUE;
		foreach ( [ 'client-id', 'client-secret' ] as $credential )
			$flag = $flag && get_option( sprintf( 'kgr-social-login-%s-%s', $provider, $credential ), '' ) !== '';
		if ( !$flag )
			continue;
		$href = admin_url( sprintf( 'admin-ajax.php?action=kgr-social-login-%s&redirect_to=%s&login',
			$provider,
			urlencode( $redirect_to )
		) );
		$src = sprintf( '%s/%s.png', KGR_SOCIAL_LOGIN_URL . 'images', $provider );
		$html .= sprintf( '<a href="%s" title="%s">', esc_url( $href ), esc_attr( ucfirst( $provider ) ) ) . "\n";
		$html .= sprintf( '<img src="%s" alt="%s" />', esc_url( $src ), esc_attr( $provider ) ) . "\n";
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
		$html .= sprintf( '<p>%s</p>', esc_html( $atts['prompt'] ) ) . "\n";
	$html .= kgr_social_login_p( get_permalink() );
	return $html;
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'kgr-social-login-buttons', KGR_SOCIAL_LOGIN_URL . 'buttons.css' );
} );

add_action( 'login_enqueue_scripts', function() {
	wp_enqueue_style( 'kgr-social-login-buttons', KGR_SOCIAL_LOGIN_URL . 'buttons.css' );
	wp_enqueue_script( 'kgr-social-login-form', KGR_SOCIAL_LOGIN_URL . 'form.js', [ 'jquery' ] );
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
		$remember = get_option( 'kgr-social-login-remember', '' ) === 'on';
		wp_set_auth_cookie( $user_id, $remember );
		header( 'location: ' . urldecode( $_GET['state'] ) );
		exit;
	} elseif ( array_key_exists( 'error', $_GET ) ) {
		kgr_social_login_error( sprintf( 'authentication %s', $_GET['error'] ) );
	} else {
		kgr_social_login_error( 'invalid function invocation' );
	}
}

add_action( 'wp_ajax_nopriv_kgr-social-login-google', function() {
	require_once( KGR_SOCIAL_LOGIN_DIR . 'google/vendor/autoload.php' );
	$provider = new League\OAuth2\Client\Provider\Google( [
		'clientId'     => get_option( 'kgr-social-login-google-client-id' ),
		'clientSecret' => get_option( 'kgr-social-login-google-client-secret' ),
		'redirectUri'  => admin_url( 'admin-ajax.php?action=kgr-social-login-google' ),
	] );
	kgr_social_login_callback( $provider, ['email'] );
} );

add_action( 'wp_ajax_nopriv_kgr-social-login-microsoft', function() {
	require_once( KGR_SOCIAL_LOGIN_DIR . 'microsoft/vendor/autoload.php' );
	$provider = new Stevenmaguire\OAuth2\Client\Provider\Microsoft( [
		'clientId'     => get_option( 'kgr-social-login-microsoft-client-id' ),
		'clientSecret' => get_option( 'kgr-social-login-microsoft-client-secret' ),
		'redirectUri'  => admin_url( 'admin-ajax.php?action=kgr-social-login-microsoft' ),
	] );
	kgr_social_login_callback( $provider, ['wl.emails'] );
} );

add_action( 'wp_ajax_nopriv_kgr-social-login-yahoo', function() {
	require_once( KGR_SOCIAL_LOGIN_DIR . 'yahoo/vendor/autoload.php' );
	$provider = new Hayageek\OAuth2\Client\Provider\Yahoo( [
		'clientId'     => get_option( 'kgr-social-login-yahoo-client-id' ),
		'clientSecret' => get_option( 'kgr-social-login-yahoo-client-secret' ),
		'redirectUri'  => admin_url( 'admin-ajax.php?action=kgr-social-login-yahoo' ),
	] );
	kgr_social_login_callback( $provider, ['openid'] );
} );
