<?php

if ( !defined( 'ABSPATH' ) )
	exit;

class KGR_Social_Login_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = [
			'classname' => 'kgr-social-login-widget',
			'description' => esc_html( 'Display social login options.' ),
		];
		parent::__construct( FALSE, esc_html( 'KGR Social Login' ), $widget_ops );
	}

	function settings(): array {
		$settings = [];
		$settings['title'] = $this->settings_text( 'title' );
		return $settings;
	}

	function settings_text( string $label ): array {
		return [
			'default' => '',
			'sanitize' => 'strval',
			'label' => $label,
			'field' => function( string $id, string $name, string $value, string $label ) {
				echo '<p>' . "\n";
				echo sprintf( '<label for="%s">%s</label>', $id, esc_html( $label ) ) . "\n";
				echo sprintf( '<input class="widefat" id="%s" name="%s" type="text" value="%s" />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value )
				) . "\n";
				echo '</p>' . "\n";
			},
		];
	}

	function instance( $instance = NULL ): array {
		$settings = $this->settings();
		if ( is_null( $instance ) || !is_array( $instance ) )
			$instance = [];
		foreach ( $settings as $key => $value )
			if ( !array_key_exists( $key, $instance ) )
				$instance[ $key ] = $value['default'];
		return $instance;
	}

	function title( array $args, array $instance ) {
		if ( $instance['title'] === '' )
			return;
		echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'] . "\n";
	}

	function content( array $instance ) {
		$redirect = kgr_social_login_default_redirect();
		if ( !is_user_logged_in() ) {
			echo sprintf( '<p><a href="%s">%s</a></p>', esc_url( wp_login_url( $redirect ) ), esc_html__( 'Log in' ) ) . "\n";
			echo kgr_social_login_p();
		} else {
			echo sprintf( '<p><a href="%s">%s</a></p>', esc_url( wp_logout_url( $redirect ) ), esc_html__( 'Log out' ) ) . "\n";
		}
	}

	function form( $instance ) {
		$instance = $this->instance( $instance );
		foreach ( $this->settings() as $key => $value ) {
			$id = $this->get_field_id( $key );
			$name = $this->get_field_name( $key );
			$value['field']( $id, $name, $instance[ $key ], $value['label'] );
		}
	}

	function update( $new_instance, $old_instance ): array {
		$instance = [];
		foreach ( $this->settings() as $key => $value )
			if ( array_key_exists( $key, $new_instance ) )
				$instance[ $key ] = $value['sanitize']( $new_instance[ $key ] );
			else
				$instance[ $key ] = $value['default'];
		return $instance;
	}

	function widget( $args, $instance ) {
		$instance = $this->instance( $instance );
		echo $args['before_widget'];
		$this->title( $args, $instance );
		$this->content( $instance );
		echo $args['after_widget'];
	}

}

add_action( 'widgets_init', function() {
	register_widget( 'KGR_Social_Login_Widget' );
} );
