<?php

if ( !defined( 'ABSPATH' ) )
	exit;

add_filter( 'plugin_action_links_kgr-social-login/kgr-social-login.php', function( array $links ): array {
	$links[] = sprintf( '<a href="%s">%s</a>', menu_page_url( 'kgr-social-login', FALSE ), 'Settings' );
	return $links;
} );

add_action( 'admin_menu', function() {
	if ( !current_user_can( 'administrator' ) )
		return;
	$page_title = 'KGR Social Login';
	$menu_title = 'KGR Social Login';
	$menu_slug = 'kgr-social-login';
	$function = 'kgr_social_login_settings_page';
	add_submenu_page( 'options-general.php', $page_title, $menu_title, 'administrator', $menu_slug, $function );
} );

add_action( 'admin_init', function() {
	if ( !current_user_can( 'administrator' ) )
		return;
	$group = 'kgr-social-login';
	// General settings
	$section = 'kgr-social-login';
	add_settings_section( $section, '', '__return_null', $group );
	// Remember
	$name = 'kgr-social-login-remember';
	register_setting( $group, $name );
	add_settings_field( $name, sprintf( '<label for="%s">Remember user</label>', $name ), function() {
		$name = 'kgr-social-login-remember';
		$value = get_option( $name, '' );
		$checked = checked( $value, 'on', FALSE );
		echo sprintf( '<input type="checkbox" name="%s" id="%s" value="on"%s />', $name, $name, $checked ) . "\n";
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
	// Google credentials
	$section = 'kgr-social-login-google-credentials';
	add_settings_section( $section, 'Google credentials', function() {
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
		echo '<a href="https://github.com/hayageek/oauth2-yahoo" target="_blank">github</a>' . "\n" .
			'<span>|</span>' . "\n" .
			'<a href="https://developer.yahoo.com/apps/" target="_blank">applications</a>' . "\n" .
			'<span>|</span>' . "\n" .
			'<a href="https://login.yahoo.com/account/activity" target="_blank">permissions</a>' . "\n";
		echo '<ol>' . "\n" .
			'<li>Create a <i>Web Application</i>.</li>' . "\n" .
			sprintf( '<li>Set <code>%s</code> as the <i>Callback Domain</i>.</li>', home_url() ) . "\n" .
			'</ol>' . "\n";
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
	echo sprintf( '<h1>%s</h1>', 'KGR Social Login' ) . "\n";
	kgr_social_login_notice( 'info', 'info', 'Leave credentials empty to disable a social login option.' );
	if ( intval( get_option( 'users_can_register' ) ) !== 1 )
		kgr_social_login_notice( 'warning', 'warning', sprintf( 'New users can\'t register. Set option <a href="%s">here</a>.', admin_url( 'options-general.php' ) ) );
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
	foreach ( ['google', 'microsoft', 'yahoo'] as $provider )
		foreach ( ['client-id', 'client-secret'] as $credential )
			delete_option( sprintf( 'kgr-social-login-%s-%s', $provider, $credential ) );
	exit;
} );
