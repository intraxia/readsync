<?php
namespace ReadSync\Service\Pocket;

/**
 * Interacts with the Pocket api.
 *
 * This class defines all code necessary to authenticate
 * and interact with the Pocket api.
 *
 * @package    ReadSync
 * @author     James DiGioia <jamesorodig@gmail.com>
 * @link       http://jamesdigioia.com
 * @since      1.0.0
 */
class Api {

	/**
	 * API url base
	 * @var string
	 */
	protected $base = 'https://getpocket.com/v3';

	/**
	 * Pocket API consumer key
	 * @var string
	 */
	protected $consumer_key = '39554-2a020fdc19cee0d7a5620026';

	/**
	 * Uri Pocket redirects to
	 * @var string
	 */
	protected $redirect_uri;

	/**
	 * One-time code for authentication
	 * @var string
	 */
	protected $code;

	/**
	 * Authenticated username
	 * @var string
	 */
	protected $user;

	/**
	 * Access token for Pocket Api
	 * @var string
	 */
	protected $token;

	public function __construct() {
		$this->redirect_uri = admin_url( 'admin.php?page=pf-feeder&pocket_authed=true' );

		// set_props hook in somwhere instead?
		// user id needs to be accessible
	}

	/**
	 * Retrieves a one-time code for authentication
	 *
	 * @return string one-time code
	 * @since 1.0.0
	 */
	public function get_code() {
		$response = wp_remote_post( $this->base . '/oauth/request', array(
			'headers' => array(
				'Content-Type' => 'application/json; charset=UTF8',
				'X-Accept'     => 'application/json',
			),
			'body'    => json_encode( array(
				'consumer_key' => $this->consumer_key,
				'redirect_uri' => $this->redirect_uri,
			) )
		) );

		$status = wp_remote_retrieve_response_code( $response );

		if ( '4' === substr( $status, 0, 1 ) || '5' === substr( $status, 0, 1 ) ) {
			// @todo real error handling
			return new \WP_Error;
		}

		$this->code = json_decode( wp_remote_retrieve_body( $response ) )->code;
		update_user_meta( get_current_user_id(), '_rs_pocket_code', $this->code );

		return $this->code;
	}

	/**
	 * Gets the authentication uri for the user to login, using the one-time code
	 *
	 * @param  string $code retrieved one-time use code
	 * @return string       uri for Pocket login
	 */
	public function get_auth_uri( $code ) {
		// @todo is there a "WordPress Way" of doing this?
		$query = array(
			'request_token' => $code,
			'redirect_uri' => $this->redirect_uri,
		);

		return 'https://getpocket.com/auth/authorize?' . http_build_query( $query );
	}

	/**
	 * Authenticates with the one-time use code after the user
	 * has logged in to the Pocket auth uri
	 *
	 * @return array       username and access token for new user
	 * @since 1.0.0
	 */
	public function authenticate() {
		$this->code = get_user_meta( get_current_user_id(), '_rs_pocket_code', true );

		$response = wp_remote_post( $this->base . '/oauth/authorize', array(
			'headers' => array(
				'Content-Type' => 'application/json; charset=UTF8',
				'X-Accept' => 'application/json',
			),
			'body' => json_encode( array(
				'consumer_key' => $this->consumer_key,
				'code' => $this->code,
			) )
		) );

		// This always gets used only once
		delete_user_meta( get_current_user_id(), '_rs_pocket_code' );

		$status = wp_remote_retrieve_response_code( $response );

		if ( '4' === substr( $status, 0, 1 ) || '5' === substr( $status, 0, 1 ) ) {
			// @todo real error handling
			return new \WP_Error;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		return array(
			'username' => $body->username,
			'access_token' => $body->access_token,
		);
	}

	/**
	 * Retrieves all the urls archived since the given unix timestamp
	 * or all the urls if no timestamp provided
	 *
	 * @param  string         $token     access token
	 * @param  boolean|string $since     unix timestamp to get urls from
	 * @return stdObject                 api results
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

		$response = wp_remote_post( $this->base . '/get', array(
			'headers' => array(
				'Content-Type' => 'application/json; charset=UTF8',
				'X-Accept' => 'application/json',
			),
			'timeout' => 500,
			'body' => json_encode( $body )
		) );

		// @todo there's a check status code function we should use
		$status = wp_remote_retrieve_response_code( $response );

		if ( '4' === substr( $status, 0, 1 ) || '5' === substr( $status, 0, 1 ) ) {
			// @todo real error handling
			return new \WP_Error;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Returns the api url base
	 */
	public function get_base() {
		return $this->base;
	}
}
