<?php
namespace Intraxia\Readsync\Module;

use Intraxia\Readsync\Api\Pocket as Api;
use WP_Post;

/**
 * The Pocket Module class
 *
 * This class gets registered to add support for Pocket, including:
 *
 * * Logging in.
 * * Consuming that user's reading list.
 *
 * @package    Intraxia\ReadSync
 * @subpackage Module
 */
class Pocket extends ModuleService {
	/**
	 * PressForward feed type.
	 *
	 * @var string
	 */
	public $feed_type = 'pocket';

	/**
	 * PressForward id.
	 *
	 * @var string
	 */
	public $id = 'pocket';

	/**
	 * Pocket Api service.
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Plugin directory.
	 *
	 * @var string
	 */
	protected $dir;

	/**
	 * Pocket constructor.
	 *
	 * @param Api    $api
	 * @param string $dir
	 */
	public function __construct( Api $api, $dir ) {
		$this->api = $api;
		$this->dir = $dir;
	}

	/**
	 * Adds the admin menus to PressForward's module system.
	 *
	 * @param array $admin_menus PF menus to register.
	 */
	public function setup_admin_menus( $admin_menus ) {
		if ( ! is_array( $admin_menus ) ) {
			$admin_menus = array();
		}

		$admin_menus[] = array(
			'page_title' => __( 'ReadSync Settings', 'readsync' ),
			'menu_title' => __( 'ReadSync', 'readsync' ),
			'cap'        => 'edit_posts',
			'slug'       => 'readsync', // @todo variable
			'callback'   => array( $this, 'display_plugin_admin_page' ),
		);

		parent::setup_admin_menus( $admin_menus );
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public function display_plugin_admin_page() {
		include_once( $this->dir . 'partials/settings.php' );
	}

	/**
	 * Sets up the module's meta info for registration into PressForward.
	 *
	 * Overrides parent.
	 */
	public function module_setup() {
		$mod_settings = array(
			'name'        => $this->id . ' Module',
			'slug'        => $this->id,
			'description' => 'Sync your Pocket reading list with PressForward.',
			'thumbnail'   => '',
			'options'     => '',
		);

		$enabled = get_option( PF_SLUG . '_' . $this->id . '_enable' );
		if ( ! in_array( $enabled, array( 'yes', 'no' ) ) ) {
			update_option( PF_SLUG . '_' . $this->id . '_enable', 'yes' );
		}

		update_option( PF_SLUG . '_' . $this->id . '_settings', $mod_settings );
	}

	/**
	 * Render the Login to Pocket box on the Add Content page
	 */
	public function add_to_feeder() {
		include_once( $this->dir . 'partials/add-user.php' );
	}

	/**
	 * Add a tab to the Add Feed page.
	 *
	 * @param array $permitted_tabs Tabs on the Add Feed page.
	 *
	 * @return array
	 */
	public function set_permitted_feeds_tabs( $permitted_tabs ) {
		$permitted_tabs['pocket'] = array(
			'title' => __( 'Subscribe to Pocket', 'readsync' ),
			'cap'   => get_option( 'pf_menu_feeder_access', pf_get_defining_capability_by_role( 'editor' ) ),
		);

		return $permitted_tabs;
	}

	/**
	 * Pulls from and transforms the Pocket feed into a
	 * PressForward-compatible array of articles
	 *
	 * @param  WP_Post $feed_obj Pocket feed object with cached post_meta.
	 *
	 * @return array             Urls for most recent api call
	 */
	public function get_data_object( $feed_obj ) {
		pf_log( 'Invoked: ReadSync\Service\Pocket\Module::get_data_object()' );

		$list = get_option( '_rs_pocket_items_remaining' );

		if ( ! $list ) {
			pf_log( 'No urls remaining. Retrieving latest.' );

			$response = $this->api->get_archived_urls( $feed_obj->access_token, $feed_obj->since ?: false );

			if ( is_wp_error( $response ) ) {
				pf_log( 'Error retrieving archived urls for ' . $feed_obj->username );

				return array();
			}

			$list = get_object_vars( $response->list );

			pf_log( 'Receieved ' . count( $list ) . ' urls' );
		}

		$items = $this->parse_list( $list );

		update_post_meta( $feed_obj->post_id, 'since', $response->since );

		return $items;
	}

	/**
	 * Parses response list into PF's items
	 *
	 * @param  array $list response list.
	 *
	 * @return array         PF's items array.
	 */
	protected function parse_list( $list ) {
		$urls = array();
		$i    = 0;

		pf_log( 'Looping through urls.' );

		while ( $list ) {
			$item   = array_pop( $list );
			$urls[] = $this->format_item( $item );

			$i ++;

			if ( 250 === $i ) {
				break;
			}
		}

		if ( $list ) {
			update_option( '_rs_pocket_items_remaining', $list );
		}

		return $urls;
	}

	/**
	 * Formats a single response item into a PF item.
	 *
	 * @param  \stdClass $item response item.
	 *
	 * @return array           PF formatted item
	 */
	protected function format_item( $item ) {
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
			'item_feat_img'   => '', // also obv
		);

		if ( $item->resolved_title ) {
			$item_obj['item_title'] = $item->resolved_title;
		} else if ( $item->given_title ) {
			$item_obj['item_title'] = $item->given_title;
		} else {
			$item_obj['item_title'] = $item->resolved_url;
		}

		$item_obj['item_link']       = explode( '?', $item->resolved_url )[0];
		$item_obj['source_title']    = 'Pocket'; // @todo change this to the name of the publication?
		$item_obj['item_date']       = date( 'r', $item->time_read );
		$item_obj['item_author']     = 'aggregation';
		$item_obj['item_content']    = $item->excerpt;
		$item_obj['item_id']         = md5( $item_obj['item_content'] );
		$item_obj['item_wp_date']    = $item_obj['item_date'];
		$item_obj['item_added_date'] = date_i18n(
			get_option( 'date_format' ),
			$item->time_read
		); // @todo convert this to GMT?

		return $item_obj;
	}
}
