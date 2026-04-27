<?php
/**
 * Plugin Name: SEOPress WPGraphQL
 * Plugin URI:  https://github.com/your-repo/seopress-graphql
 * Description: Expose SEOPress SEO fields in the WPGraphQL API. Supports titles, meta descriptions, Open Graph, Twitter Card, robots, canonical, schema, breadcrumbs, and more.
 * Version:     1.0.0
 * Author:      Alex Beck
 * License:     GPL-2.0-or-later
 * Text Domain: seopress-graphql
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'SEOPRESS_GRAPHQL_VERSION', '1.0.0' );
define( 'SEOPRESS_GRAPHQL_DIR', plugin_dir_path( __FILE__ ) );
define( 'SEOPRESS_GRAPHQL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check that both SEOPress and WPGraphQL are active before loading.
 */
add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WPGraphQL' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>'
                . esc_html__( 'SEOPress WPGraphQL requires the WPGraphQL plugin to be installed and active.', 'seopress-graphql' )
                . '</p></div>';
        } );
        return;
    }

    if ( ! defined( 'SEOPRESS_VERSION' ) && ! class_exists( 'SeoPress\Core\Kernel' ) && ! function_exists( 'seopress_get_service' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>'
                . esc_html__( 'SEOPress WPGraphQL requires the SEOPress plugin to be installed and active.', 'seopress-graphql' )
                . '</p></div>';
        } );
        return;
    }

    require_once SEOPRESS_GRAPHQL_DIR . 'includes/class-seopress-graphql.php';
    SEOPress_GraphQL::init();
} );
