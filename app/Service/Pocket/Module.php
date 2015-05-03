<?php
namespace ReadSync\Service\Pocket;

use ReadSync\Service\ModuleInterface;

/**
 * The Pocket service class
 *
 * This class gets registered to add support for Pocket, including:
 *
 * * Logging in
 * * Consuming that user's reading list
 * * (Eventually) add share to Pocket support
 *
 * @package    ReadSync
 * @author     James DiGioia <jamesorodig@gmail.com>
 * @link       http://jamesdigioia.com
 * @since      1.0.0
 */
class Module extends ModuleInterface {

	protected $auth;

	/**
	 * PressForward ID for the ReadSync module
	 * @var string
	 */
	public static $slug = 'pocket';

	/**
	 * Register the authentication routing
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected function register_routes() {
		$auth = new Auth;

		add_action( 'load-pressforward_page_pf-feeder', array( $auth, 'route' ) );
	}

	/**
	 * Adds the admin menus to PressForward's module system
	 *
	 * @param  array $admin_menus
	 * @return array              Defined admin menus
	 * @since  1.0.0
	 */
	public function setup_admin_menus( $admin_menus ) {
		if ( ! is_array( $admin_menus ) ) {
			$admin_menus = array();
		}

		$admin_menus[] = array(
			'page_title' => __( 'ReadSync Settings', \ReadSync::$plugin_slug ),
			'menu_title' => __( 'ReadSync', \ReadSync::$plugin_slug ),
			'cap'        => 'edit_posts',
			'slug'       => \ReadSync::$plugin_slug,
			'callback'   => array( $this, 'display_plugin_admin_page' ),
		);

		parent::setup_admin_menus( $admin_menus );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( READSYNC_DIR . 'partials/settings.php' );
	}

	/**
	 * Adds the admin menus to PressForward's module system
	 *
	 * @param  array $links
	 * @return array              Defined action links
	 * @since  1.0.0
	 */
	public function add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . \ReadSync::$plugin_slug ) . '">' . __( 'Settings', \ReadSync::$plugin_slug ) . '</a>'
			),
			$links
		);
	}

	/**
	 * Sets up the module's meta info for registration into PressForward
	 *
	 * Overrides parent
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function module_setup() {
		$mod_settings = array(
			'name' => $this->id . ' Module',
			'slug' => \ReadSync::$plugin_slug,
			'description' => 'Sync your Pocket reading list with PressForward.',
			'thumbnail' => '',
			'options' => '',
		);

		$enabled = get_option( PF_SLUG .'_' . $this->id . '_enable' );
		if ( ! in_array( $enabled, array( 'yes', 'no' ) ) ) {
			$enabled = 'yes';
			update_option( PF_SLUG .'_' . $this->id . '_enable', $enabled );
		}

		update_option( PF_SLUG . '_' . $this->id . '_settings', $mod_settings );
	}

	/**
	 * Render the Login to Pocket box on the Add Content page
	 */
	public function add_to_feeder() {
		include_once( READSYNC_DIR . 'partials/add-user.php' );
	}

	/**
	 * Pulls from and transforms the Pocket feed into a
	 * PressForward-compatible array of articles
	 *
	 * @param  WP_Post $feed_obj Pocket feed object with cached post_meta
	 * @return array             Urls for most recent api call
	 * @todo   abstract this method out into an import object
	 * @todo   make this more robust for error handling
	 */
	public function get_data_object( $feed_obj ) {
		$api = new Api;

		$response = $api->get_archived_urls( $feed_obj->access_token, $feed_obj->since ? $feed_obj->since : false );

		if ( is_wp_error( $response ) ) {
			pf_log( 'Error retrieving archived urls for ' . $feed_obj->username );
			return $response;
		}

		$imported_urls = array();
		$i = 0;

		foreach ( array_reverse( get_object_vars( $response->list ) ) as $item_id => $item ) {
			$item_obj = array(
				'item_title'      => '', // obv
				'source_title'    => '', // name or baseurl of publication, or Pocket
				'item_date'       => '', // date of publication
				'item_author'     => '', // original author or "aggregation"
				'item_content'    => '', // body content, needs dummy content
				                         // readability: user opens feed, or user nominates item
				'item_link'       => '', // url
				'item_id'         => '', // needs to be added ourselves: md5 hash of url + title
				'item_wp_date'    => '', // entered the system
				'item_tags'       => '', // @todo get from pocket?
				'item_added_date' => '', // date added to feed (i.e. date archived)
				'source_repeat'   => '', // dupe detected, then increment
			);

			if ( $item->given_title ) {
				$item_obj['item_title'] = $item->given_title;
			} else if ( $item->resolved_title ) {
				$item_obj['item_title'] = $item->resolved_title;
			} else {
				$item_obj['item_title'] = $item->resolved_url;
			}

			$item_obj['item_link'] = $item->resolved_url;
			$item_obj['source_title'] = 'Pocket'; // @todo change this to the name of the publication?
			$item_obj['item_date'] = date( 'r', $item->time_read );
			$item_obj['item_author'] = 'aggregation';
			$item_obj['item_content'] = $item_obj['item_link'] . $item_obj['item_title'];
			$item_obj['item_id'] = md5( $item_obj['item_content'] );
			$item_obj['item_wp_date'] = $item_obj['item_date'];
			$item_obj['item_added_date'] = date_i18n( get_option( 'date_format' ), $item->time_read ); // @todo convert this to GMT?

			$imported_urls[ $item_id ] = $item_obj;

			$i++;

			if ( 300 === $i ) {
				break;
			}
		}

		// @todo save $since to post_meta

		return $imported_urls;
	}

}
