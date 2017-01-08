<?php
namespace Intraxia\Readsync\View;

use Intraxia\Jaxion\Contract\Core\HasFilters;

/**
 * Class Settings
 *
 * @package    Intraxia\Readsync
 * @subpackage View
 */
class Settings implements HasFilters {
	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	protected $basename;

	/**
	 * Settings constructor.
	 *
	 * @param string $slug     Plugin slug.
	 * @param string $basename Plugin basename.
	 */
	public function __construct( $slug, $basename ) {
		$this->slug     = $slug;
		$this->basename = $basename;
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @param array $links Action links.
	 *
	 * @return array Action links.
	 */
	public function add_action_links( array $links ) {
		return array_merge(
			array(
				'settings' => sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'admin.php?page=' . $this->slug ),
					__( 'Settings', 'readsync' )
				),
			),
			$links
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 */
	public function filter_hooks() {
		return array(
			array(
				'hook'   => 'plugin_action_links_' . $this->basename,
				'method' => 'add_action_links',
			),
		);
	}
}
