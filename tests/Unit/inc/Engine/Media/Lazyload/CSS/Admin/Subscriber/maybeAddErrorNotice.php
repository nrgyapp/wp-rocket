<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Admin\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Common\Cache\CacheInterface;
use WP_Rocket\Engine\Media\Lazyload\CSS\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Admin\Subscriber::maybe_add_error_notice
 */
class TestMaybeAddErrorNotice extends TestCase {
	protected $cache;
	protected $subscriber;

	public function set_up() {
		parent::set_up();

		$this->cache = Mockery::mock( CacheInterface::class );

		$this->subscriber = new Subscriber( $this->cache );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		Functions\expect( 'current_user_can' )
			->with( 'rocket_manage_options' )
			->andReturn( $config['can'] );

		$this->configureIsAccessible( $config, $expected );
		$this->configureNotice( $config, $expected );

		$this->subscriber->maybe_add_error_notice();

		$this->assertTrue( true );
	}

	protected function configureIsAccessible( $config, $expected ) {
		if ( ! $config['can'] ) {
			return;
		}

		$this->cache->shouldReceive( 'is_accessible' )
			->andReturn( $config['is_accessible'] );
	}

	protected function configureNotice( $config, $expected ) {
		if ( ! $config['can'] || $config['is_accessible'] ) {
			return;
		}

		Functions\expect( 'rocket_notice_writing_permissions' )
			->with( $config['root_path'] )
			->andReturn( $config['message'] );
		$this->cache->shouldReceive( 'get_root_path' )
			->andReturn( $config['root_path'] );
		Functions\expect( 'rocket_notice_html' )
			->with( $expected['notice'] );
	}
}
