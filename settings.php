<?php

if ( !defined( 'ABSPATH' ) )
	exit;

add_action( 'admin_menu', function() {
	if ( !current_user_can( 'administrator' ) )
		return;
	$page_title = esc_html( 'KGR Social Login' );
	$menu_title = esc_html( 'KGR Social Login' );
	$menu_slug = KGR_SOCIAL_LOGIN_KEY;
	$function = 'kgr_social_login_settings_page';
	add_submenu_page( 'options-general.php', $page_title, $menu_title, 'administrator', $menu_slug, $function );
} );

add_action( 'admin_init', function() {
	if ( !current_user_can( 'administrator' ) )
		return;
	$group = KGR_SOCIAL_LOGIN_KEY;
	// General settings
	$section = KGR_SOCIAL_LOGIN_KEY;
	add_settings_section( $section, '', '__return_null', $group );
	// Remember
	$name = 'kgr-social-login-remember';
	register_setting( $group, $name );
	add_settings_field( $name, sprintf( '<label for="%s">%s</label>', esc_attr( $name ), esc_html( 'Remember user' ) ), function() {
		$name = 'kgr-social-login-remember';
		$value = get_option( $name, '' );
		$checked = checked( $value, 'on', FALSE );
		echo sprintf( '<input type="checkbox" name="%s" id="%s" value="on"%s />', esc_attr( $name ), esc_attr( $name ), $checked ) . "\n";
?>
<p class="description">
	When this option is set, the user is logged in for 14 days.
	<br />
	Otherwise, this period is limited to 2 days and only for the current session.
	<br />
	For more details, see <a href="https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/" target="_blank">WordPress Function Reference</a>.
</p>
<?php
	}, $group, $section );
	global $kgr_social_login_providers;
	global $kgr_social_login_credentials;
	foreach ( $kgr_social_login_providers as $provider => $provider_value ) {
		$section = sprintf( 'kgr-social-login-%s-credentials', $provider );
		add_settings_section( $section, $provider_value['label'], $provider_value['section'], $group );
		$name = sprintf( '%s-%s-composer', KGR_SOCIAL_LOGIN_KEY, $provider );
		register_setting( $group, $name );
		add_settings_field( $name, sprintf( '<label for="%s">%s</label>', esc_attr( $name ), esc_html( 'Composer directory' ) ), function( array $args ) {
			$name = sprintf( '%s-%s-composer', KGR_SOCIAL_LOGIN_KEY, $args['provider'] );
			$value = get_option( $name, '' );
			echo sprintf( '<input type="text" name="%s" id="%s" class="regular-text" placeholder="%s" autocomplete="off" value="%s" />',
				esc_attr( $name ),
				esc_attr( $name ),
				esc_html( 'Composer directory' ),
				esc_attr( $value )
			) . "\n";
		}, $group, $section, [ 'provider' => $provider ] );
		$value = get_option( $name, '' );
		if ( $value === '' || !file_exists( $value ) )
			continue;
		foreach ( $kgr_social_login_credentials as $credential => $credential_value ) {
			$name = sprintf( 'kgr-social-login-%s-%s', $provider, $credential );
			register_setting( $group, $name );
			add_settings_field(
				$name,
				sprintf( '<label for="%s">%s</label>', esc_attr( $name ), esc_html( $credential_value ) ),
				function( array $args ) {
					global $kgr_social_login_credentials;
					$name = sprintf( 'kgr-social-login-%s-%s', $args['provider'], $args['credential'] );
					$value = get_option( $name, '' );
					echo sprintf( '<input type="text" name="%s" id="%s" class="regular-text" placeholder="%s" autocomplete="off" value="%s" />',
						esc_attr( $name ),
						esc_attr( $name ),
						esc_attr( $kgr_social_login_credentials[ $args['credential'] ] ),
						esc_attr( $value )
					) . "\n";
				},
				$group,
				$section,
				[ 'provider' => $provider, 'credential' => $credential ]
			);
		}
	}
} );

function kgr_social_login_notice( string $class, string $dashicon, string $message ) {
?>
<div class="notice notice-<?= $class ?>">
	<p class="dashicons-before dashicons-<?= $dashicon ?>"><?= $message ?></p>
</div>
<?php
}

function kgr_social_login_settings_page() {
	if ( !current_user_can( 'administrator' ) )
		return;
	echo '<div class="wrap">' . "\n";
	echo sprintf( '<h1>%s</h1>', esc_html( 'KGR Social Login' ) ) . "\n";
		kgr_social_login_notice( 'info', 'info', esc_html( 'Leave credentials empty to disable a social login option.' ) );
		if ( intval( get_option( 'users_can_register' ) ) !== 1 )
			kgr_social_login_notice( 'warning', 'warning', sprintf( 'New users can\'t register. Set option <a href="%s">here</a>.', admin_url( 'options-general.php' ) ) );
	// Form
	echo '<form method="post" action="options.php">' . "\n";
	settings_fields( KGR_SOCIAL_LOGIN_KEY );
	do_settings_sections( KGR_SOCIAL_LOGIN_KEY );
	submit_button();
	echo '</form>' . "\n";
	// Uninstall
	echo '<div>' . "\n";
	echo sprintf( '<h2>%s</h2>', esc_html( 'Uninstall' ) ) . "\n";
	$name = 'kgr-social-login-uninstall';
	$nonce = wp_create_nonce( $name );
	$url = admin_url( sprintf( 'admin-ajax.php?action=%s&nonce=%s', $name, $nonce ) );
	echo sprintf( '<a href="%s" class="button button-secondary kgr-social-login-button">%s</a>', esc_url( $url ), esc_html( 'Uninstall' ) ) . "\n";
	echo '<span class="spinner" style="float: none;"></span>' . "\n";
	echo sprintf( '<p class="description">%s</p>', esc_html( 'Delete all plugin options.' ) ) . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
}

add_action( 'admin_enqueue_scripts', function( string $hook ) {
	if ( !current_user_can( 'administrator' ) )
		return;
	if ( $hook !== 'settings_page_kgr-social-login' )
		return;
	wp_enqueue_script( 'kgr-social-login-settings', KGR_SOCIAL_LOGIN_URL . 'settings.js', [ 'jquery' ] );
} );

add_action( 'wp_ajax_kgr-social-login-uninstall', function() {
	if ( !current_user_can( 'administrator' ) )
		exit( 'role' );
	$action = $_GET['action'];
	$nonce = $_GET['nonce'];
	if ( !wp_verify_nonce( $nonce, $action ) )
		exit( 'nonce' );
	delete_option( sprintf( '%s-%s', KGR_SOCIAL_LOGIN_KEY, 'remember' ) );
	global $kgr_social_login_providers;
	global $kgr_social_login_credentials;
	foreach ( array_keys( $kgr_social_login_providers ) as $provider ) {
		delete_option( sprintf( '%s-%s-%s', KGR_SOCIAL_LOGIN_KEY, $provider, 'composer' ) );
		foreach ( array_keys( $kgr_social_login_credentials ) as $credential )
			delete_option( sprintf( '%s-%s-%s', KGR_SOCIAL_LOGIN_KEY, $provider, $credential ) );
	}
	exit;
} );
