<?php
namespace Intraxia\Readsync\Api;

use stdClass;
use WP_Error;

/**
 * Interacts with the Pocket api.
 *
 * This class defines all code necessary to authenticate
 * and interact with the Pocket api.
 *
 * @package    Intraxia\ReadSync
 * @subpackage Api
 */
class Pocket {
	/**
	 * API url base.
	 *
	 * @var string
	 */
	protected $base = 'https://getpocket.com/v3';

	/**
	 * Pocket API consumer key.
	 *
	 * @var string
	 */
	protected $consumer_key = '39554-2a020fdc19cee0d7a5620026';

	/**
	 * Uri Pocket redirects to.
	 *
	 * @var string
	 */
	protected $redirect_uri;

	/**
	 * Pocket constructor.
	 */
	public function __construct() {
		$this->redirect_uri = admin_url( 'admin.php?page=pf-feeder&pocket_authed=true' );
	}

	/**
	 * Retrieves a one-time code for authentication
	 *
	 * @return string One-time code.
	 */
	public function get_code() {
		$response = wp_remote_post( $this->base . '/oauth/request', array(
			'headers' => array(
				'Content-Type' => 'application/json; charset=UTF8',
				'X-Accept'     => 'application/json',
			),
			'body'    => wp_json_encode( array(
				'consumer_key' => $this->consumer_key,
				'redirect_uri' => $this->redirect_uri,
			) ),
		) );

		$status = wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ) );

		if ( '4' === $status[0] || '5' === $status[0] ) {
			return new WP_Error( $body );
		}

		return $body->code;
	}

	/**
	 * Gets the authentication uri for the user to login, using the one-time code.
	 *
	 * @param  string $code retrieved one-time use code.
	 *
	 * @return string       uri for Pocket login.
	 */
	public function get_auth_uri( $code ) {
		return 'https://getpocket.com/auth/authorize?' . http_build_query( array(
			'request_token' => $code,
			'redirect_uri'  => $this->redirect_uri,
		) );
	}

	/**
	 * Authenticates with the one-time use code after the user
	 * has logged in to the Pocket auth uri.
	 *
	 * @param  string $code retrieved one-time use code.
	 *
	 * @return array|WP_Error username and access token for new user
	 *                              or error on failure.
	 */
	public function authenticate( $code ) {
		$response = wp_remote_post( $this->base . '/oauth/authorize', array(
			'headers' => array(
				'Content-Type' => 'application/json; charset=UTF8',
				'X-Accept'     => 'application/json',
			),
			'body'    => wp_json_encode( array(
				'consumer_key' => $this->consumer_key,
				'code'         => $code,
			) ),
		) );

		$status = wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ) );

		if ( '4' === $status[0] || '5' === $status[0] ) {
			return new WP_Error( $body );
		}

		return array(
			'username'     => $body->username,
			'access_token' => $body->access_token,
		);
	}

	/**
	 * Retrieves all the urls archived since the given unix timestamp
	 * or all the urls if no timestamp provided.
	 *
	 * @param  string      $token access token.
	 * @param  bool|string $since unix timestamp to get urls from.
	 *
	 * @return WP_Error|stdClass                  api results.
	 */
	public function get_archived_urls( $token, $since = false ) {
		$body = array(
			'consumer_key' => $this->consumer_key,
			'access_token' => $token,
			'state'        => 'archive',
		);

		if ( $since ) {
			$body['since'] = $since;
		}

		pf_log( 'About to get urls' );

		$response = wp_remote_post( $this->base . '/get', array(
			'headers' => array(
				'Content-Type' => 'application/json; charset=UTF8',
				'X-Accept'     => 'application/json',
			),
			'timeout' => 500,
			'body'    => wp_json_encode( $body ),
		) );

		pf_log( 'Received urls' );

		$status = wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ) );

		if ( '4' === $status[0] || '5' === $status[0] ) {
			return new WP_Error( $body );
		}

		return $body;
	}

	/**
	 * Returns the api url base
	 */
	public function get_base() {
		return $this->base;
	}
}
