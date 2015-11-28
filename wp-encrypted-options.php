<?php
/*
 * Plugin Name: Encrypted Options
 * Plugin URI:  http://wordpress.org/plugins/wp-encrypted-options/
 * Description: Encrypt Options in the WordPress database.
 * Version:     0.1
 * Author:      Josh Betz
 * Author URI:  https://joshbetz.com
 * Text Domain: wp-encrypted-options
 */

if ( ! defined( 'WPEO_KEY' ) ) {
	define( 'WPEO_KEY', '' );
}

class WP_Encrypted_Options {

	const METHOD = 'aes256';
	const IV_LENGTH = 16;

	static function encrypt( $value ) {
		$iv = substr( bin2hex( openssl_random_pseudo_bytes( self::IV_LENGTH ) ), 0, self::IV_LENGTH );
		$value = openssl_encrypt( $value, self::METHOD, md5( WPEO_KEY ), false, $iv );
		return array( $iv, $value );
	}

	static function decrypt( $encrypted ) {
		list( $iv, $value ) = $encrypted;

		if ( empty( $value ) ) {
			return false;
		}

		return openssl_decrypt( $value, self::METHOD, md5( WPEO_KEY ), false, $iv );
	}
}

function wpeo_add_option( $option, $value ) {
	$value = maybe_serialize( $value );
	$value = WP_Encrypted_Options::encrypt( $value );
	return add_option( $option, $value );
}

function wpeo_get_option( $option, $default = null ) {
	$value = get_option( $option );

	if ( empty( $value ) ) {
		return $default;
	}

	$value = WP_Encrypted_Options::decrypt( $value );
	$value = maybe_unserialize( $value );

	return $value;
}

function wpeo_get_user_option( $option, $user = null ) {
	$value = get_user_option( $option, $user );
	$value = WP_Encrypted_Options::decrypt( $value );
	$value = maybe_unserialize( $value );
	return $value;
}

function wpeo_update_option( $option, $value ) {
	$value = WP_Encrypted_Options::encrypt( $value );
	return update_option( $option, $value );
}

function wpeo_update_user_option( $user_id, $option, $value, $global = null ) {
	$value = WP_Encrypted_Options::encrypt( $value );
	return update_user_option( $user_id, $option, $value, $global );
}


if ( is_multisite() ) {
	function wpeo_add_blog_option( $id, $option, $value ) {
		$value = maybe_serialize( $value );
		$value = WP_Encrypted_Options::encrypt( $value );
		return add_blog_option( $id, $option, $value );
	}

	function wpeo_add_site_option( $option, $value ) {
		$value = maybe_serialize( $value );
		$value = WP_Encrypted_Options::encrypt( $value );
		return add_site_option( $option, $value );
	}

	function wpeo_get_blog_option( $id, $option, $default = null ) {
		$value = get_blog_option( $id, $option );

		if ( empty( $value ) ) {
			return $default;
		}

		$value = WP_Encrypted_Options::decrypt( $value );
		$value = maybe_unserialize( $value );
		return $value;
	}

	function wpeo_get_site_option( $option, $default = null ) {
		$value = get_site_option( $option );

		if ( empty( $value ) ) {
			return $default;
		}

		$value = WP_Encrypted_Options::decrypt( $value );
		$value = maybe_unserialize( $value );
		return $value;
	}

	function wpeo_update_blog_option( $id, $option, $value ) {
		$value = WP_Encrypted_Options::encrypt( $value );
		return update_blog_option( $id, $option, $value );
	}

	function wpeo_update_site_option( $option, $value ) {
		$value = WP_Encrypted_Options::encrypt( $value );
		return update_site_option( $option, $value );
	}
}
