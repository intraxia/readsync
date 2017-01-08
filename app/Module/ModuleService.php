<?php
namespace Intraxia\Readsync\Module;

use Intraxia\Jaxion\Contract\Core\HasActions;
use PF_Module;

/**
 * Class ModuleService
 *
 * Base Module class for new feed sources.
 *
 * @package    Intraxia\Readsync\Module
 * @subpackage Module
 */
abstract class ModuleService extends PF_Module implements HasActions {
	/**
	 * Register the module with Pressforward after setup.
	 */
	public function post_setup_module_info() {
		pressforward( 'modules' )->modules[ $this->feed_type ] = $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 */
	public function action_hooks() {
		return array(
			array(
				'hook'   => 'pf_setup_modules',
				'method' => 'setup_module_info',
			),
			array(
				'hook'   => 'admin_init',
				'method' => 'module_setup',
			),
			array(
				'hook'   => 'pf_admin_op_page',
				'method' => 'admin_op_page',
			),
			array(
				'hook'   => 'pf_admin_op_page_save',
				'method' => 'admin_op_page_save',
			),
		);
	}
}
