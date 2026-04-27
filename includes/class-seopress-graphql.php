<?php
/**
 * Main plugin class.
 *
 * Registers all SEOPress data as a `seo` field on every post type and taxonomy
 * that is registered with WPGraphQL.
 */

defined( 'ABSPATH' ) || exit;

class SEOPress_GraphQL {

    /**
     * Boot the plugin.
     */
    public static function init(): void {
        $instance = new self();
        add_action( 'graphql_register_types', [ $instance, 'register_types' ] );
    }

    /**
     * Register all custom GraphQL types and fields.
     */
    public function register_types(): void {
        $this->register_seo_type();
        $this->register_og_type();
        $this->register_twitter_type();
        $this->register_robots_type();
        $this->register_schema_type();
        $this->register_breadcrumb_types();
        $this->register_redirect_type();

        // Attach `seo` field to every WPGraphQL post type.
        $this->attach_to_post_types();

        // Attach `seo` field to every WPGraphQL taxonomy term.
        $this->attach_to_taxonomies();
    }

    // -------------------------------------------------------------------------
    // Type registrations
    // -------------------------------------------------------------------------

    private function register_og_type(): void {
        register_graphql_object_type( 'SEOPressOpenGraph', [
            'description' => __( 'SEOPress Open Graph / Facebook metadata', 'seopress-graphql' ),
            'fields'      => [
                'title'       => [ 'type' => 'String', 'description' => __( 'OG title', 'seopress-graphql' ) ],
                'description' => [ 'type' => 'String', 'description' => __( 'OG description', 'seopress-graphql' ) ],
                'image'       => [ 'type' => 'String', 'description' => __( 'OG image URL', 'seopress-graphql' ) ],
                'imageWidth'  => [ 'type' => 'String', 'description' => __( 'OG image width', 'seopress-graphql' ) ],
                'imageHeight' => [ 'type' => 'String', 'description' => __( 'OG image height', 'seopress-graphql' ) ],
                'type'        => [ 'type' => 'String', 'description' => __( 'OG type (article, website, etc.)', 'seopress-graphql' ) ],
                'url'         => [ 'type' => 'String', 'description' => __( 'OG url override', 'seopress-graphql' ) ],
                'siteName'    => [ 'type' => 'String', 'description' => __( 'OG site name override', 'seopress-graphql' ) ],
                'videoUrl'    => [ 'type' => 'String', 'description' => __( 'OG video URL', 'seopress-graphql' ) ],
                'locale'      => [ 'type' => 'String', 'description' => __( 'OG locale override', 'seopress-graphql' ) ],
                'facebookApp' => [ 'type' => 'String', 'description' => __( 'Facebook App ID', 'seopress-graphql' ) ],
            ],
        ] );
    }

    private function register_twitter_type(): void {
        register_graphql_object_type( 'SEOPressTwitterCard', [
            'description' => __( 'SEOPress Twitter Card metadata', 'seopress-graphql' ),
            'fields'      => [
                'title'       => [ 'type' => 'String', 'description' => __( 'Twitter card title', 'seopress-graphql' ) ],
                'description' => [ 'type' => 'String', 'description' => __( 'Twitter card description', 'seopress-graphql' ) ],
                'image'       => [ 'type' => 'String', 'description' => __( 'Twitter card image URL', 'seopress-graphql' ) ],
                'cardType'    => [ 'type' => 'String', 'description' => __( 'Twitter card type (summary, summary_large_image, etc.)', 'seopress-graphql' ) ],
                'creator'     => [ 'type' => 'String', 'description' => __( 'Twitter creator @handle', 'seopress-graphql' ) ],
                'site'        => [ 'type' => 'String', 'description' => __( 'Twitter @site handle', 'seopress-graphql' ) ],
            ],
        ] );
    }

    private function register_robots_type(): void {
        register_graphql_object_type( 'SEOPressRobots', [
            'description' => __( 'SEOPress robots/indexing directives', 'seopress-graphql' ),
            'fields'      => [
                'noIndex'   => [ 'type' => 'Boolean', 'description' => __( 'noindex', 'seopress-graphql' ) ],
                'noFollow'  => [ 'type' => 'Boolean', 'description' => __( 'nofollow', 'seopress-graphql' ) ],
                'noOdp'     => [ 'type' => 'Boolean', 'description' => __( 'noodp', 'seopress-graphql' ) ],
                'noImageIndex' => [ 'type' => 'Boolean', 'description' => __( 'noimageindex', 'seopress-graphql' ) ],
                'noArchive' => [ 'type' => 'Boolean', 'description' => __( 'noarchive', 'seopress-graphql' ) ],
                'noSnippet' => [ 'type' => 'Boolean', 'description' => __( 'nosnippet', 'seopress-graphql' ) ],
                'primary'   => [ 'type' => 'String',  'description' => __( 'Primary robots meta string rendered by SEOPress', 'seopress-graphql' ) ],
            ],
        ] );
    }

    private function register_schema_type(): void {
        register_graphql_object_type( 'SEOPressSchema', [
            'description' => __( 'SEOPress structured data / JSON-LD', 'seopress-graphql' ),
            'fields'      => [
                'type'         => [ 'type' => 'String', 'description' => __( 'Schema type override', 'seopress-graphql' ) ],
                'pageType'     => [ 'type' => 'String', 'description' => __( 'Page type for schema', 'seopress-graphql' ) ],
                'articleType'  => [ 'type' => 'String', 'description' => __( 'Article type for schema', 'seopress-graphql' ) ],
                'jsonLd'       => [ 'type' => 'String', 'description' => __( 'Raw JSON-LD output as string (use carefully)', 'seopress-graphql' ) ],
            ],
        ] );
    }

    private function register_breadcrumb_types(): void {
        register_graphql_object_type( 'SEOPressBreadcrumbItem', [
            'description' => __( 'A single breadcrumb item', 'seopress-graphql' ),
            'fields'      => [
                'text' => [ 'type' => 'String', 'description' => __( 'Breadcrumb label', 'seopress-graphql' ) ],
                'url'  => [ 'type' => 'String', 'description' => __( 'Breadcrumb URL', 'seopress-graphql' ) ],
            ],
        ] );
    }

    private function register_redirect_type(): void {
        register_graphql_object_type( 'SEOPressRedirect', [
            'description' => __( 'SEOPress redirect settings for this post', 'seopress-graphql' ),
            'fields'      => [
                'enabled'     => [ 'type' => 'Boolean', 'description' => __( 'Whether a redirect is enabled', 'seopress-graphql' ) ],
                'type'        => [ 'type' => 'String',  'description' => __( 'HTTP redirect type (301, 302, etc.)', 'seopress-graphql' ) ],
                'url'         => [ 'type' => 'String',  'description' => __( 'Redirect destination URL', 'seopress-graphql' ) ],
            ],
        ] );
    }

    private function register_seo_type(): void {
        register_graphql_object_type( 'SEOPressSEO', [
            'description' => __( 'SEOPress SEO fields for a post or term', 'seopress-graphql' ),
            'fields'      => [
                // ── Core ──────────────────────────────────────────────────────
                'title'           => [
                    'type'        => 'String',
                    'description' => __( 'SEO title (rendered, variables replaced)', 'seopress-graphql' ),
                ],
                'metaDescription' => [
                    'type'        => 'String',
                    'description' => __( 'Meta description (rendered)', 'seopress-graphql' ),
                ],
                'canonicalUrl'    => [
                    'type'        => 'String',
                    'description' => __( 'Canonical URL', 'seopress-graphql' ),
                ],
                'targetKeywords'  => [
                    'type'        => [ 'list_of' => 'String' ],
                    'description' => __( 'Target keywords', 'seopress-graphql' ),
                ],

                // ── Robots ────────────────────────────────────────────────────
                'robots'          => [
                    'type'        => 'SEOPressRobots',
                    'description' => __( 'Robots / indexing settings', 'seopress-graphql' ),
                ],

                // ── Open Graph ────────────────────────────────────────────────
                'openGraph'       => [
                    'type'        => 'SEOPressOpenGraph',
                    'description' => __( 'Open Graph / Facebook metadata', 'seopress-graphql' ),
                ],

                // ── Twitter ───────────────────────────────────────────────────
                'twitterCard'     => [
                    'type'        => 'SEOPressTwitterCard',
                    'description' => __( 'Twitter Card metadata', 'seopress-graphql' ),
                ],

                // ── Schema ────────────────────────────────────────────────────
                'schema'          => [
                    'type'        => 'SEOPressSchema',
                    'description' => __( 'Structured data / JSON-LD settings', 'seopress-graphql' ),
                ],

                // ── Breadcrumbs ───────────────────────────────────────────────
                'breadcrumbs'     => [
                    'type'        => [ 'list_of' => 'SEOPressBreadcrumbItem' ],
                    'description' => __( 'Breadcrumb trail for the current page', 'seopress-graphql' ),
                ],

                // ── Redirect ──────────────────────────────────────────────────
                'redirect'        => [
                    'type'        => 'SEOPressRedirect',
                    'description' => __( 'Redirect settings', 'seopress-graphql' ),
                ],

                // ── Sitemap ───────────────────────────────────────────────────
                'sitemapExclude'  => [
                    'type'        => 'Boolean',
                    'description' => __( 'Whether this post/term is excluded from the sitemap', 'seopress-graphql' ),
                ],
                'sitemapPriority' => [
                    'type'        => 'String',
                    'description' => __( 'Sitemap priority override (0.0 – 1.0)', 'seopress-graphql' ),
                ],
                'sitemapFrequency' => [
                    'type'        => 'String',
                    'description' => __( 'Sitemap change frequency override', 'seopress-graphql' ),
                ],
            ],
        ] );
    }

    // -------------------------------------------------------------------------
    // Attachment helpers
    // -------------------------------------------------------------------------

    /**
     * Add `seo` field to every WPGraphQL-enabled post type.
     */
    private function attach_to_post_types(): void {
        $post_types = \WPGraphQL::get_allowed_post_types();
        foreach ( $post_types as $post_type ) {
            $type_name = get_post_type_object( $post_type )->graphql_single_name ?? null;
            if ( ! $type_name ) {
                continue;
            }
            register_graphql_field( $type_name, 'seo', [
                'type'        => 'SEOPressSEO',
                'description' => __( 'SEOPress SEO data', 'seopress-graphql' ),
                'resolve'     => function ( \WPGraphQL\Model\Post $post ) {
                    return $this->resolve_post_seo( $post->databaseId );
                },
            ] );
        }
    }

    /**
     * Add `seo` field to every WPGraphQL-enabled taxonomy term.
     */
    private function attach_to_taxonomies(): void {
        $taxonomies = \WPGraphQL::get_allowed_taxonomies();
        foreach ( $taxonomies as $taxonomy ) {
            $tax_object = get_taxonomy( $taxonomy );
            $type_name  = $tax_object->graphql_single_name ?? null;
            if ( ! $type_name ) {
                continue;
            }
            register_graphql_field( $type_name, 'seo', [
                'type'        => 'SEOPressSEO',
                'description' => __( 'SEOPress SEO data', 'seopress-graphql' ),
                'resolve'     => function ( \WPGraphQL\Model\Term $term ) {
                    return $this->resolve_term_seo( $term->databaseId );
                },
            ] );
        }
    }

    // -------------------------------------------------------------------------
    // Resolvers
    // -------------------------------------------------------------------------

    /**
     * Build the SEO data array for a post.
     */
    private function resolve_post_seo( int $post_id ): array {
        $meta = get_post_meta( $post_id );

        // Helper: single meta value with fallback to empty string.
        $m = function ( string $key ) use ( $meta ): string {
            return isset( $meta[ $key ][0] ) ? (string) $meta[ $key ][0] : '';
        };

        // Title — try the rendered title via SEOPress if available.
        $title = $m( '_seopress_titles_title' );
        if ( function_exists( 'seopress_get_service' ) ) {
            try {
                // SEOPress 5.x+ service API
                $title_service = seopress_get_service( 'TitleService' );
                if ( $title_service ) {
                    $title_service->set_post( get_post( $post_id ) );
                    $rendered = $title_service->get_title();
                    if ( $rendered ) {
                        $title = $rendered;
                    }
                }
            } catch ( \Throwable $e ) {
                // Fall back to raw meta.
            }
        }

        // Meta description.
        $description = $m( '_seopress_titles_desc' );

        // Canonical.
        $canonical = $m( '_seopress_robots_canonical' );
        if ( ! $canonical ) {
            $canonical = get_permalink( $post_id );
        }

        // Keywords.
        $kw_raw  = $m( '_seopress_analysis_target_kw' );
        $keywords = $kw_raw ? array_filter( array_map( 'trim', explode( ',', $kw_raw ) ) ) : [];

        // Robots.
        $robots = [
            'noIndex'      => (bool) $m( '_seopress_robots_index' ),
            'noFollow'     => (bool) $m( '_seopress_robots_follow' ),
            'noOdp'        => (bool) $m( '_seopress_robots_odp' ),
            'noImageIndex' => (bool) $m( '_seopress_robots_imageindex' ),
            'noArchive'    => (bool) $m( '_seopress_robots_archive' ),
            'noSnippet'    => (bool) $m( '_seopress_robots_snippet' ),
            'primary'      => $m( '_seopress_robots_primary_cat' ),
        ];

        // Open Graph.
        $og = [
            'title'       => $m( '_seopress_social_fb_title' ),
            'description' => $m( '_seopress_social_fb_desc' ),
            'image'       => $m( '_seopress_social_fb_img' ),
            'imageWidth'  => $m( '_seopress_social_fb_img_width' ),
            'imageHeight' => $m( '_seopress_social_fb_img_height' ),
            'type'        => $m( '_seopress_social_fb_type' ) ?: 'article',
            'url'         => $m( '_seopress_social_fb_url' ),
            'siteName'    => $m( '_seopress_social_fb_site_name' ),
            'videoUrl'    => $m( '_seopress_social_fb_video_url' ),
            'locale'      => $m( '_seopress_social_fb_locale' ),
            'facebookApp' => get_option( 'seopress_social_accounts_option_name', [] )['seopress_social_accounts_facebook_app_id'] ?? '',
        ];

        // Twitter.
        $twitter = [
            'title'       => $m( '_seopress_social_twitter_title' ),
            'description' => $m( '_seopress_social_twitter_desc' ),
            'image'       => $m( '_seopress_social_twitter_img' ),
            'cardType'    => $m( '_seopress_social_twitter_card' ) ?: 'summary_large_image',
            'creator'     => $m( '_seopress_social_twitter_creator' ),
            'site'        => get_option( 'seopress_social_accounts_option_name', [] )['seopress_social_accounts_twitter'] ?? '',
        ];

        // Schema.
        $schema = [
            'type'        => $m( '_seopress_pro_schemas_manual_type' ),
            'pageType'    => $m( '_seopress_pro_page_type' ),
            'articleType' => $m( '_seopress_pro_article_type' ),
            'jsonLd'      => '', // populated below if possible
        ];

        // Breadcrumbs — use SEOPress breadcrumb data if available.
        $breadcrumbs = $this->get_post_breadcrumbs( $post_id );

        // Redirect (SEOPress PRO).
        $redirect_enabled = (bool) $m( '_seopress_redirections_enabled' );
        $redirect = [
            'enabled' => $redirect_enabled,
            'type'    => $m( '_seopress_redirections_type' ),
            'url'     => $m( '_seopress_redirections_value' ),
        ];

        // Sitemap.
        $sitemap_exclude   = (bool) $m( '_seopress_sitemap_exclude' );
        $sitemap_priority  = $m( '_seopress_sitemaps_prio' );
        $sitemap_frequency = $m( '_seopress_sitemaps_freq' );

        return [
            'title'            => $title,
            'metaDescription'  => $description,
            'canonicalUrl'     => $canonical,
            'targetKeywords'   => array_values( $keywords ),
            'robots'           => $robots,
            'openGraph'        => $og,
            'twitterCard'      => $twitter,
            'schema'           => $schema,
            'breadcrumbs'      => $breadcrumbs,
            'redirect'         => $redirect,
            'sitemapExclude'   => $sitemap_exclude,
            'sitemapPriority'  => $sitemap_priority,
            'sitemapFrequency' => $sitemap_frequency,
        ];
    }

    /**
     * Build the SEO data array for a term.
     */
    private function resolve_term_seo( int $term_id ): array {
        $m = function ( string $key ) use ( $term_id ) {
            return (string) ( get_term_meta( $term_id, $key, true ) ?: '' );
        };

        $canonical = $m( '_seopress_robots_canonical' );
        if ( ! $canonical ) {
            $term = get_term( $term_id );
            $canonical = $term instanceof \WP_Term ? get_term_link( $term ) : '';
            if ( is_wp_error( $canonical ) ) {
                $canonical = '';
            }
        }

        $robots = [
            'noIndex'      => (bool) $m( '_seopress_robots_index' ),
            'noFollow'     => (bool) $m( '_seopress_robots_follow' ),
            'noOdp'        => (bool) $m( '_seopress_robots_odp' ),
            'noImageIndex' => (bool) $m( '_seopress_robots_imageindex' ),
            'noArchive'    => (bool) $m( '_seopress_robots_archive' ),
            'noSnippet'    => (bool) $m( '_seopress_robots_snippet' ),
            'primary'      => '',
        ];

        $og = [
            'title'       => $m( '_seopress_social_fb_title' ),
            'description' => $m( '_seopress_social_fb_desc' ),
            'image'       => $m( '_seopress_social_fb_img' ),
            'imageWidth'  => '',
            'imageHeight' => '',
            'type'        => 'website',
            'url'         => '',
            'siteName'    => '',
            'videoUrl'    => '',
            'locale'      => '',
            'facebookApp' => '',
        ];

        $twitter = [
            'title'       => $m( '_seopress_social_twitter_title' ),
            'description' => $m( '_seopress_social_twitter_desc' ),
            'image'       => $m( '_seopress_social_twitter_img' ),
            'cardType'    => 'summary_large_image',
            'creator'     => '',
            'site'        => '',
        ];

        return [
            'title'            => $m( '_seopress_titles_title' ),
            'metaDescription'  => $m( '_seopress_titles_desc' ),
            'canonicalUrl'     => $canonical,
            'targetKeywords'   => [],
            'robots'           => $robots,
            'openGraph'        => $og,
            'twitterCard'      => $twitter,
            'schema'           => [ 'type' => '', 'pageType' => '', 'articleType' => '', 'jsonLd' => '' ],
            'breadcrumbs'      => [],
            'redirect'         => [ 'enabled' => false, 'type' => '', 'url' => '' ],
            'sitemapExclude'   => (bool) $m( '_seopress_sitemap_exclude' ),
            'sitemapPriority'  => $m( '_seopress_sitemaps_prio' ),
            'sitemapFrequency' => $m( '_seopress_sitemaps_freq' ),
        ];
    }

    // -------------------------------------------------------------------------
    // Breadcrumbs helper
    // -------------------------------------------------------------------------

    /**
     * Generate breadcrumbs for a post using SEOPress breadcrumb logic if available,
     * otherwise fall back to a simple home → post trail.
     */
    private function get_post_breadcrumbs( int $post_id ): array {
        $crumbs = [];

        // SEOPress 5.x breadcrumb service.
        if ( function_exists( 'seopress_get_service' ) ) {
            try {
                $service = seopress_get_service( 'breadcrumb' );
                if ( $service && method_exists( $service, 'get_breadcrumb' ) ) {
                    // We need a fake query context — temporarily fake the global post.
                    global $post;
                    $original = $post;
                    $post      = get_post( $post_id );
                    setup_postdata( $post );

                    $items = $service->get_breadcrumb();

                    wp_reset_postdata();
                    $post = $original;

                    if ( is_array( $items ) ) {
                        foreach ( $items as $item ) {
                            $crumbs[] = [
                                'text' => $item['text'] ?? '',
                                'url'  => $item['url']  ?? '',
                            ];
                        }
                        return $crumbs;
                    }
                }
            } catch ( \Throwable $e ) {
                // Fall through to manual breadcrumbs.
            }
        }

        // Fallback: home → ancestors → current post.
        $crumbs[] = [ 'text' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ];

        $ancestors = get_post_ancestors( $post_id );
        foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
            $crumbs[] = [ 'text' => get_the_title( $ancestor_id ), 'url' => get_permalink( $ancestor_id ) ];
        }

        $crumbs[] = [ 'text' => get_the_title( $post_id ), 'url' => get_permalink( $post_id ) ];

        return $crumbs;
    }
}
