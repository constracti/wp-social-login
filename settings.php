<?php

if ( !defined( 'ABSPATH' ) )
	exit;

add_filter( 'plugin_action_links_kgr-social-login/kgr-social-login.php', function( array $links ): array {
	$links[] = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=kgr-social-login' ), 'Options' );
	return $links;
} );

add_action( 'admin_menu', function() {
	if ( !current_user_can( 'administrator' ) )
		return;
	$page_title = 'KGR Social Login';
	$menu_title = 'KGR Social Login';
	$menu_slug = 'kgr-social-login';
	$function = 'kgr_social_login_options_page';
	add_submenu_page( 'options-general.php', $page_title, $menu_title, 'administrator', $menu_slug, $function );
} );

add_action( 'admin_init', function() {
	if ( !current_user_can( 'administrator' ) )
		return;
	$group = 'kgr-social-login';
	// Google credentials
	$section = 'kgr-social-login-google-credentials';
	add_settings_section( $section, 'Google credentials', function() {
		echo '<a href="https://github.com/thephpleague/oauth2-google">github</a>' . "\n" .
			'<span>|</span>' . "\n" .
			'<a href="https://console.developers.google.com/">applications</a>' . "\n" .
			'<span>|</span>' . "\n" .
			'<a href="https://myaccount.google.com/permissions">permissions</a>' . "\n";
	}, $group );
	// Google Client ID
	$name = 'kgr-social-login-google-client-id';
	register_setting( $group, $name );
	add_settings_field( $name, sprintf( '<label for="%s">Client ID</label>', $name ), function() {
		$name = 'kgr-social-login-google-client-id';
		$value = get_option( $name, '' );
		echo sprintf( '<input type="text" name="%s" id="%s" class="regular-text" placeholder="Client ID" autocomplete="off" value="%s" />', $name, $name, $value ) . "\n";
	}, $group, $section );
	// Google Client secret
	$name = 'kgr-social-login-google-client-secret';
	register_setting( $group, $name );
	add_settings_field( $name, sprintf( '<label for="%s">Client secret</label>', $name ), function() {
		$name = 'kgr-social-login-google-client-secret';
		$value = get_option( $name, '' );
		echo sprintf( '<input type="text" name="%s" id="%s" class="regular-text" placeholder="Client secret" autocomplete="off" value="%s" />', $name, $name, $value ) . "\n";
	}, $group, $section );
	// Microsoft credentials
	$section = 'kgr-social-login-microsoft-credentials';
	add_settings_section( $section, 'Microsoft credentials', function() {
		echo '<a href="https://github.com/stevenmaguire/oauth2-microsoft">github</a>' . "\n" .
			'<span>|</span>' . "\n" .
			'<a href="https://apps.dev.microsoft.com/">applications</a>' . "\n" .
			'<span>|</span>' . "\n" .
			'<a href="https://account.live.com/consent/Manage">permissions</a>' . "\n";
	}, $group );
	// Microsoft Client ID
	$name = 'kgr-social-login-microsoft-client-id';
	register_setting( $group, $name );
	add_settings_field( $name, sprintf( '<label for="%s">Client ID</label>', $name ), function() {
		$name = 'kgr-social-login-microsoft-client-id';
		$value = get_option( $name, '' );
		echo sprintf( '<input type="text" name="%s" id="%s" class="regular-text" placeholder="Client ID" autocomplete="off" value="%s" />', $name, $name, $value ) . "\n";
	}, $group, $section );
	// Microsoft Client secret
	$name = 'kgr-social-login-microsoft-client-secret';
	register_setting( $group, $name );
	add_settings_field( $name, sprintf( '<label for="%s">Client secret</label>', $name ), function() {
		$name = 'kgr-social-login-microsoft-client-secret';
		$value = get_option( $name, '' );
		echo sprintf( '<input type="text" name="%s" id="%s" class="regular-text" placeholder="Client secret" autocomplete="off" value="%s" />', $name, $name, $value ) . "\n";
	}, $group, $section );
	// Yahoo credentials
	$section = 'kgr-social-login-yahoo-credentials';
	add_settings_section( $section, 'Yahoo credentials', function() {
		echo '<a href="https://github.com/hayageek/oauth2-yahoo">github</a>' . "\n" .
			'<span>|</span>' . "\n" .
			'<a href="https://developer.yahoo.com/apps/">applications</a>' . "\n" .
			'<span>|</span>' . "\n" .
			'<a href="https://login.yahoo.com/account/activity">permissions</a>' . "\n";
	}, $group );
	// Yahoo Client ID
	$name = 'kgr-social-login-yahoo-client-id';
	register_setting( $group, $name );
	add_settings_field( $name, sprintf( '<label for="%s">Client ID</label>', $name ), function() {
		$name = 'kgr-social-login-yahoo-client-id';
		$value = get_option( $name, '' );
		echo sprintf( '<input type="text" name="%s" id="%s" class="regular-text" placeholder="Client ID" autocomplete="off" value="%s" />', $name, $name, $value ) . "\n";
	}, $group, $section );
	// Yahoo Client secret
	$name = 'kgr-social-login-yahoo-client-secret';
	register_setting( $group, $name );
	add_settings_field( $name, sprintf( '<label for="%s">Client secret</label>', $name ), function() {
		$name = 'kgr-social-login-yahoo-client-secret';
		$value = get_option( $name, '' );
		echo sprintf( '<input type="text" name="%s" id="%s" class="regular-text" placeholder="Client secret" autocomplete="off" value="%s" />', $name, $name, $value ) . "\n";
	}, $group, $section );
} );

function kgr_social_login_options_page() {
	if ( !current_user_can( 'administrator' ) )
		return;
	echo '<div class="wrap">' . "\n";
	echo sprintf( '<h1>%s</h1>', 'KGR Social Login' ) . "\n";
	if ( intval( get_option( 'users_can_register' ) ) !== 1 )
		echo '<div class="notice notice-warning">' . "\n" .
		sprintf( '<p class="dashicons-before dashicons-warning">New users can\'t register. Set option <a href="%s">here</a>.</p>',
			admin_url( 'options-general.php' )
		) . "\n" .
		'</div>' . "\n";
	echo '<form method="post" action="options.php">' . "\n";
	settings_fields( 'kgr-social-login' );
	do_settings_sections( 'kgr-social-login' );
	submit_button();
	$url = admin_url( 'admin-ajax.php?action=kgr-social-login-clear' );
	echo sprintf( '<p><a href="%s" class="button button-secondary" id="kgr-social-login-clear">%s</a></p>', $url, 'Clear' ) . "\n";
	echo '</form>' . "\n";
	echo '</div>' . "\n";
}

add_action( 'admin_enqueue_scripts', function( string $hook ) {
	if ( !current_user_can( 'administrator' ) )
		return;
	if ( $hook !== 'settings_page_kgr-social-login' )
		return;
	wp_enqueue_script( 'kgr-social-login-settings', plugins_url( 'settings.js', __FILE__ ), ['jquery'] );
} );

add_action( 'wp_ajax_kgr-social-login-clear', function() {
	if ( !current_user_can( 'administrator' ) )
		exit;
	exit( 'ok' );
	foreach ( ['google', 'microsoft', 'yahoo'] as $provider )
		foreach ( ['client-id', 'client-secret'] as $credential )
			delete_option( sprintf( 'kgr-social-login-%s-%s', $provider, $credential ) );
	exit;
} );
