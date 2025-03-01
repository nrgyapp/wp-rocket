<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Cache\FilesystemCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Common\Cache\FilesystemCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Cache\FilesystemCache::delete
 */
class TestDelete extends TestCase {
	protected $root_folder;
	protected $filesystem;
	protected $filesystemcache;

	public function set_up() {
		parent::set_up();
		$this->root_folder = '/background-css/';
		$this->filesystem = Mockery::mock( WP_Filesystem_Direct::class );

		$this->filesystemcache = new FilesystemCache( $this->root_folder, $this->filesystem );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Functions\when('rocket_get_constant')->justReturn($config['root']);
		Functions\expect('get_rocket_parse_url')->with($config['key'])->andReturn($config['parsed_url']);
		Functions\when('home_url')->justReturn($config['home_url']);
		$this->filesystem->shouldReceive('exists')->with($expected['path'])->andReturn($config['exists']);
		$this->configureIsDir($config, $expected);
		$this->configureDirDelete($config, $expected);
		$this->configureFileDelete($config, $expected);
		$this->assertSame($expected['output'], $this->filesystemcache->delete($config['key']));
	}

	protected function configureIsDir( $config, $expected ) {
		if(! $config['exists']) {
			return;
		}
		$this->filesystem->shouldReceive('is_dir')->with($expected['path'])->andReturn($config['is_dir']);
	}

	protected function configureFileDelete( $config, $expected ) {
		if(! $config['exists'] || $config['is_dir']) {
			return;
		}
		$this->filesystem->shouldReceive('delete')->with($expected['path'])->andReturn(true);
	}

	protected function configureDirDelete( $config, $expected ) {
		if(! $config['exists'] || ! $config['is_dir'] ) {
			return;
		}
		Functions\expect('rocket_rrmdir')->andReturn($expected['path'], [], $this->filesystem);
	}
}
