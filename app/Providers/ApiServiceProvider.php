<?php
namespace Intraxia\Readsync\Providers;

use Intraxia\Jaxion\Contract\Core\Container;
use Intraxia\Jaxion\Contract\Core\ServiceProvider;
use Intraxia\Readsync\Api\Pocket;

/**
 * Class ApiServiceProvider
 *
 * @package    Intraxia\Readsync
 * @subpackage Providers
 */
class ApiServiceProvider implements ServiceProvider {
	/**
	 * {@inheritdoc}
	 *
	 * @param Container $container
	 */
	public function register( Container $container ) {
		$container->define( 'api.pocket', function () {
			return new Pocket;
		} );
	}
}
