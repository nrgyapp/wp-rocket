<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::create_lazy_inline_css
 */
class TestCreateLazyInlineCss extends TestCase {
	use SubscriberTrait;

	public function set_up() {
		$this->init_subscriber();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Functions\when('wp_generate_uuid4')->justReturn('hash');

		foreach ($config['extract'] as $content => $conf) {
			$this->extractor->expects()->extract($content, $conf['css_file'])->andReturn($conf['results']);
		}

		foreach ($config['rule_format'] as $url_tag) {
			$this->rule_formatter->expects()->format($url_tag['content'], $url_tag['tag'])->andReturn($url_tag['new_content']);
			$this->json_formatter->expects()->format($url_tag['tag'])->andReturn($url_tag['formatted_urls']);
		}

		$this->assertSame($expected, $this->subscriber->create_lazy_inline_css($config['data']));
	}
}
