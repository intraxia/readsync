<?php
namespace ReadSync\Service;

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
abstract class ModuleInterface extends \PF_Module {

	/**
	 * Hooks in the module's custom and PressForward functionality
	 *
	 * Adds the auth routing hooks for logging in to Pocket,
	 * then kicks off the PressForward module's boot method.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->id = static::$slug;
		$this->feed_type = static::$slug;

		$this->register_routes();

		$this->start();
	}

	abstract protected function register_routes();
}
