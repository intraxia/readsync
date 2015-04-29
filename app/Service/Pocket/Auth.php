<?php
namespace ReadSync\Service\Pocket;

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, a settings page, and two examples hooks
 * for how to enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    ReadSync
 * @author     Your Name <email@example.com>
 * @link       http://example.com
 * @since      1.0.0
 */
class Auth {

	public function __construct() {
		// @todo inject this
		$this->api = new Api;
	}

	/**
	 * Routes the header of the login page to handle the auth process
	 *
	 * @since 1.0.0
	 */
	public function route() {
		if ( isset( $_GET['pocket_login'] ) && 'true' === $_GET['pocket_login'] ) {
			$this->do_pocket_login();
		}

		if ( isset( $_GET['pocket_authed'] ) && 'true' === $_GET['pocket_authed'] ) {
			$this->do_pocket_authentication();
		}

		// if ( isset( $_GET['pocket_import'] ) && 'true' === $_GET['pocket_import'] ) {
		// 	$this->do_pocket_import();
		// }

	}

	/**
	 * Handles the pre-redirect login flow
	 *
	 * @since 1.0.0
	 */
	protected function do_pocket_login() {
		$code = $this->api->get_code();

		if ( is_wp_error( $code ) ) {
			// @todo real error handling
			return;
		}

		wp_redirect( $this->api->get_auth_uri( $code ) );
		exit;
	}

	/**
	 * After the user is logged in, retrieves an access token
	 *
	 * @return bool|WP_Error true if successful, WP_Error if fails
	 * @since 1.0.0
	 */
	protected function do_pocket_authentication() {
		// @todo make sure this never fires more than once
		$response = $this->api->authenticate();

		if ( is_wp_error( $response ) ) {
			// @todo real error handling
			pf_log("Auth Failed");
			return;
		}

		$feed_obj = pressforward()->pf_feeds;
		$result = $feed_obj->create( $this->api->get_base() . '/' . $response['username'], array(
			'title'         => $response['username'] . '\'s Pocket Feed',
			'type'          => 'pocket',
			'username'      => $response['username'],
			'access_token'  => $response['access_token'],
			'module_added'  => 'pocket',
			'since'         => null,
		) );

		if ( is_wp_error( $response ) ) {
			// @todo real error handling
			pf_log("Save Failed");
			return new \WP_Error;
		}

		return true;
	}
}
