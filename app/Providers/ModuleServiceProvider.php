<?php
namespace Intraxia\Readsync\Providers;

use Intraxia\Jaxion\Contract\Core\Container;
use Intraxia\Jaxion\Contract\Core\ServiceProvider;
use Intraxia\Readsync\Module\Pocket;

/**
 * Class ModuleServiceProvider
 *
 * @package    Intraxia\Readsync
 * @subpackage Providers
 */
class ModuleServiceProvider implements ServiceProvider {
	/**
	 * {@inheritdoc}
	 *
	 * @param Container $container
	 */
	public function register( Container $container ) {
		$container->share( 'module.pocket', function ( Container $container ) {
			return new Pocket( $container->fetch( 'api.pocket' ), $container->fetch( 'path' ) );
		} );
	}
}
