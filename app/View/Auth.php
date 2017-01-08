<?php
namespace Intraxia\Readsync\View;

use Intraxia\Jaxion\Contract\Core\HasActions;
use Intraxia\Readsync\Api\Pocket;
use PressForward\Core\Schema\Feeds;
use WP_Error;

/**
 * Class Auth
 *
 * @package    Intraxia\Readsync
 * @subpackage View
 */
class Auth implements HasActions {
	/**
	 * Pocket Api service.
	 *
	 * @var Pocket
	 */
	protected $api;

	/**
	 * Auth constructor.
	 *
	 * @param Pocket $api Pocket Api service.
	 */
	public function __construct( Pocket $api ) {
		$this->api = $api;
	}

	/**
	 * Routes the header of the login page to handle the auth process
	 */
	public function route() {
		if ( isset( $_GET['pocket_login'] ) && 'true' === $_GET['pocket_login'] ) {
			$this->do_pocket_login();
		}

		if ( isset( $_GET['pocket_authed'] ) && 'true' === $_GET['pocket_authed'] ) {
			$this->do_pocket_authentication();
		}
	}

	/**
	 * Handles the pre-redirect login flow.
	 */
	protected function do_pocket_login() {
		$code = $this->api->get_code();

		if ( is_wp_error( $code ) ) {
			return;
		}

		update_user_meta( get_current_user_id(), '_rs_pocket_code', $code );

		wp_redirect( $this->api->get_auth_uri( $code ) );
		exit;
	}

	/**
	 * After the user is logged in, retrieves an access token
	 *
	 * @return bool|WP_Error true if successful, WP_Error if fails
	 */
	protected function do_pocket_authentication() {
		$code = get_user_meta( get_current_user_id(), '_rs_pocket_code', true );

		if ( ! $code ) {
			return new WP_Error( 'no_code', 'No code found in user meta.' );
		}

		delete_user_meta( get_current_user_id(), '_rs_pocket_code' );
		$response = $this->api->authenticate( $code );

		if ( is_wp_error( $response ) ) {
			pf_log( 'Auth Failed' );

			return $response;
		}

		/**
		 * PF feeds service.
		 *
		 * @var Feeds $feeds PF Feeds service.
		 */
		$feeds  = pressforward( 'schema.feeds' );
		$result = $feeds->create( $this->api->get_base() . '/' . $response['username'], array(
			'title'        => $response['username'] . '\'s Pocket Feed',
			'type'         => 'pocket',
			'username'     => $response['username'],
			'access_token' => $response['access_token'],
			'module_added' => 'pocket',
			'since'        => null,
		) );

		if ( is_wp_error( $result ) ) {
			pf_log( 'Save Failed' );

			return new WP_Error;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 */
	public function action_hooks() {
		return array(
			array(
				'hook'   => 'load-pressforward_page_pf-feeder',
				'method' => 'route',
			),
		);
	}
}
