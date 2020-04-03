<?php
/**
 * Plugin Name: Paid Memberships Pro - REST API Demo
 * Plugin URI: https://www.paidmembershipspro.com/documentation/rest-api/
 * Description: Sample code to demonstration use of the PMPro REST API endpoints.
 * Author: Paid Memberships Pro
 * Author URI: https://www.paidmembershipspro.com
 * Version: .1
 * Plugin URI:
 * License: GNU GPLv2+
 * Text Domain: pmpro-rest-api-demo
 */

/// TESTING
function init_test() {
	if ( !empty( $_REQUEST['test'] ) ) {
		///$remote_user = pmprorad_get_remote_user_by_email( 'jason+api@strangerstudios.com' );
		///var_dump( $remote_user );
		
		///$has_access = pmprorad_has_membership_access( 1, 1 );		
		///var_dump( $has_access );
		
		$membership_level = pmprorad_get_membership_level_for_user( 'jason+api@strangerstudios.com' );
		var_dump( $membership_level );
		
		exit;
	}
}
add_action( 'init', 'init_test' );

/**
 * Get array of settings needed to make API calls.
 *
 * Update the remotesite, APIUSER, and APIPASS settings
 * as required for the remote site you will be testing against. 
 */
function pmprorad_get_options() {
    return array(
        'localsite' => site_url(),
        'remotesite' => 'https://dev.paidmembershipspro.com',
        'APIUSER' => 'strangerstudios',
        'APIPASS' => 'gEkU PlKj bnkC 3rdM aPn2 7NT9',        
    );
}
 
/**
 * Make a call to the remote site's WP REST API.
 *
 * @param $endpoint string Endpoint to make a request to.
 * @param $method   string HTTP method for request.
 * @param $body     string Optional body to send in request.
 */
function pmprorad_request( $endpoint, $method = 'get', $params = array() ) {
    $options = pmprorad_get_options();
    $params = array_map( 'urlencode', $params );    
	$url = $options['remotesite'] . $endpoint;
    $url = add_query_arg( $params, $url );		
	$args = array(
        'method' => $method,        
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $options['APIUSER'] . ':' . $options['APIPASS'] ),
        )
    );
    $response = wp_remote_request( $url, $args );
    
	d( $response );
	
	if ( $response['response']['code'] !== 200 ) {        
        return false;
    } else {
        return json_decode( $response['body'] );
    }
}

/**
 * Find a remote user by email address.
 * Uses a Core WP endpoint.
 * /wp-json/wp/v2/users/?search=email@domain.com
 */
function pmprorad_get_remote_user_by_email( $email ) {
	$endpoint = '/wp-json/wp/v2/users/';
	$params = array( 'search' => $email );
	
	$remote_users = pmprorad_request( $endpoint, 'get', $params );
		
	if ( ! empty( $remote_users ) ) {
		return $remote_users[0];
	} else {
		return false;
	}
}

/**
 * Check if a user has access to a specific post. 
 * /wp-json/pmpro/v1/has_membership_access
 * 
 * You must pass both a user_id AND post_id param to the endpoint.
 */
function pmprorad_has_membership_access( $user_id, $post_id ) {
	
	$params = array( 'user_id' => $user_id, 'post_id' => $post_id );
	
	$has_access = pmprorad_request( $endpoint, 'get', $params );
		
	return $has_access;
}

/**
 * Get a membership level for a user.
 * /wp-json/pmpro/v1/get_membership_level_for_user
 *
 * You must pass either a user_id OR email param to the endpoint.
 */
function pmprorad_get_membership_level_for_user( $user_id ) {
	$endpoint = '/wp-json/pmpro/v1/get_membership_level_for_user';
	
	if ( is_numeric( $user_id ) ) {
		$params = array( 'user_id' => $user_id );
	} else {
		$params = array( 'email' => $user_id );
	}
	
	$membership_level = pmprorad_request( $endpoint, 'get', $params );
	
	return $membership_level;
}

