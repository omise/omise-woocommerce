<?php

use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Page_Card_From_Customization_Test extends TestCase
{
    public function setUp(): void
    {
        // mocking WP built-in functions
        if (!function_exists('get_option')) {
            function get_option() {}
        }

        Mockery::mock('alias:Omise_Admin_Page');
        require_once __DIR__ . '/../../../../includes/admin/class-omise-page-card-form-customization.php';
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function testGetLightTheme()
    {
        $expected = [
			'font' => [
				'name' => 'Poppins',
				'size' => 16,
				'custom_name' => ''
			],
			'input' => [
				'height' => '44px',
				'border_radius' => '4px',
				'border_color' => '#ced3de',
				'active_border_color' => '#1451cc',
				'background_color' => '#ffffff',
				'label_color' => '#212121',
				'text_color' => '#212121',
				'placeholder_color' => '#98a1b2',
			],
			'checkbox' => [
				'text_color' => '#1c2433',
				'theme_color' => '#1451cc',
			]
		];

        $obj = Omise_Page_Card_From_Customization::get_instance();

        // calling private method
        $themeValues = $this->invokeMethod($obj, 'get_light_theme', []);

        $this->assertEqualsCanonicalizing($expected, $themeValues);
    }

    /**
     * @test
     */
    public function testGetDarkTheme()
    {
        $expected = [
			'font' => [
				'name' => 'Poppins',
				'size' => 16,
				'custom_name' => ''
			],
			'input' => [
				'height' => '44px',
				'border_radius' => '4px',
				'border_color' => '#475266',
				'active_border_color' => '#475266',
				'background_color' => '#131926',
				'label_color' => '#E6EAF2',
				'text_color' => '#ffffff',
				'placeholder_color' => '#DBDBDB',
			],
			'checkbox' => [
				'text_color' => '#E6EAF2',
				'theme_color' => '#1451CC',
			]
		];

        $obj = Omise_Page_Card_From_Customization::get_instance();

        // calling private method
        $themeValues = $this->invokeMethod($obj, 'get_dark_theme', []);

        $this->assertEqualsCanonicalizing($expected, $themeValues);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
