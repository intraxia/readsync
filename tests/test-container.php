<?php
namespace ReadSync\Service;

use \Mockery as m;

class ServiceContainerTest extends \WP_UnitTestCase {

	static $functions;

	function setUp() {
		parent::setUp();
		self::$functions = m::mock();
		$this->container = new Container();
	}

	function test_registers_all_modules() {
		self::$functions->shouldReceive( 'pressforward_register_module' )->with(array(
			'slug'  => 'pocket',
			'class' => '\ReadSync\Service\Pocket\Module',
		))->once();

		$this->container->register();
	}

	public function tearDown() {
		m::close();
	}
}


function pressforward_register_module( $module ) {
	return ServiceContainerTest::$functions->pressforward_register_module( $module );
}
