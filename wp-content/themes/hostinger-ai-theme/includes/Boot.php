<?php
namespace Hostinger\AiTheme;

use Hostinger\AiTheme\Admin\Assets as AdminAssets;
use Hostinger\AiTheme\Admin\Menu as AdminMenu;
use Hostinger\AiTheme\Admin\Hooks as AdminHooks;
use Hostinger\AiTheme\Builder\AffiliateBuilder;
use Hostinger\AiTheme\Builder\HostingerReachBuilder;
use Hostinger\AiTheme\Builder\ImageManager;
use Hostinger\AiTheme\Builder\RequestClient;
use Hostinger\AiTheme\Builder\Seo;
use Hostinger\AiTheme\Builder\WooBuilder;
use Hostinger\AiTheme\Builder\WebsiteBuilder;
use Hostinger\AiTheme\Rest\BuilderRoutes;
use Hostinger\AiTheme\Rest\Routes;
use Hostinger\AiTheme\Rest\BlockTypeRoutes;
use Hostinger\Surveys\Rest as SurveysRest;
use Hostinger\Surveys\SurveyManager;
use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Constants;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Utils as Helper;
use Hostinger\WpMenuManager\Manager;
use Hostinger\Amplitude\AmplitudeLoader;
use Hostinger\AiTheme\Settings\Theme as ThemeSettings;
use Hostinger\AiTheme\Shortcodes\ShortcodesManager;

defined( 'ABSPATH' ) || exit;

class Boot {
	public function run(): void {
        $this->load_hostinger_packages();
		$this->load_dependencies();
		$this->set_locale();
	}

    /**
     * @return void
     */
    public function hostinger_load_menus(): void {
        $manager = Manager::getInstance();
        $manager->boot();
    }

    /**
     * @return void
     */
    public function hostinger_load_amplitude(): void {
        $amplitude = AmplitudeLoader::getInstance();
        $amplitude->boot();
    }

    /**
     * @return void
     */
    private function load_hostinger_packages(): void {
        if ( ! has_action( 'plugins_loaded', 'hostinger_load_menus' ) ) {
            add_action( 'after_setup_theme', array( $this, 'hostinger_load_menus' ) );
        }

        if ( ! has_action( 'plugins_loaded', 'hostinger_load_amplitude' ) ) {
            add_action( 'after_setup_theme', array( $this, 'hostinger_load_amplitude' ) );
        }
    }

	private function load_dependencies(): void {
        $assets          = new Assets();
        $theme_settings  = new ThemeSettings();
        $updates         = new Updates();
        $updates->updates();

        $gutenbergBlocks = new GutenbergBlocks();
        $shortcodes      = new ShortcodesManager();

        $helper         = new Helper();
        $config_handler = new Config();
        $client         = new Client( $config_handler->getConfigValue( 'base_rest_uri', HOSTINGER_AI_WEBSITES_REST_URI ), [
            Config::TOKEN_HEADER  => $helper::getApiToken(),
            Config::DOMAIN_HEADER => $helper->getHostInfo(),
            'Content-Type' => 'application/json'
        ] );

        $request_client = new RequestClient( $client );
        $image_manager = new ImageManager();
        $affiliate_builder = new AffiliateBuilder();
        $woo_builder = new WooBuilder( $image_manager );
        $hostinger_reach_builder = new HostingerReachBuilder();

        $website_builder = new WebsiteBuilder( $request_client, $image_manager, $affiliate_builder, $woo_builder, $hostinger_reach_builder );
        $website_builder->init();

        $builder_routes = new BuilderRoutes( $website_builder );

        $block_type_routes = new BlockTypeRoutes();
        $routes = new Routes( $builder_routes, $block_type_routes );
        $routes->init();

        $utils = new Helper();
        $redirects = new Redirects( $utils );
        $seo = new Seo();
        $hooks = new Hooks( $image_manager );

		if ( is_admin() ) {
			$this->load_admin_dependencies();
		}
	}

	private function set_locale() {
		$plugin_i18n = new I18n();
	}


	private function load_admin_dependencies(): void
    {
        $image_manager = new ImageManager();
        $helper        = new Helper();

        new AdminAssets( $helper );
        new AdminMenu();
        new AdminHooks( $image_manager );

        $config = new Config();
        $client = new Client(
            $config->getConfigValue( 'base_rest_uri', Constants::HOSTINGER_REST_URI ),
            [
                Config::TOKEN_HEADER  => $helper->getApiToken(),
                Config::DOMAIN_HEADER => $helper->getHostInfo(),
            ]
        );

        if ( class_exists( SurveyManager::class ) ) {
            $surveysRest   = new SurveysRest( $client );
            $surveyManager = new SurveyManager( $helper, $config, $surveysRest );
            $surveys       = new \Hostinger\AiTheme\Admin\Surveys( $surveyManager );
            $surveys->init();
        }
    }
}
