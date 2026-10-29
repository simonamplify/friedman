<?php
/**
 * Synergy Software Download & Version Shortcodes
 * 
 * Provides shortcodes for the Synergy Editor Software:
 * 1. [syn_es_d os="windows/mac"]   - Outputs an <a> link with security attributes targeting a new blank tab.
 * 2. [syn_es_v os="windows/mac"]   - Outputs the latest version number prefixed with "Synergy Editor Software" (or just the prefix if fetch fails).
 * 3. [syn_es_osv os="windows/mac"] - Outputs OS compatibility requirement text.
 *
 * -------------------------------------------------------------------------------------
 * CLEARING THE TRANSIENT (VERSION CACHE) AFTER AN UPDATE:
 * -------------------------------------------------------------------------------------
 * The version number fetched by [syn_es_v] is cached in WordPress for 4 hours using
 * the Transients API to keep page loading speeds fast and avoid repeated API calls.
 *
 * To force an immediate refresh when a new version is published:
 * 1. VIA WP-CLI (Command Line):
 *    Run: wp transient delete synergy_software_versions
 *
 * 2. VIA A WORDPRESS PLUGIN:
 *    Use a free plugin like "Transients Manager" (by WP-Engine/Pippin Williamson).
 *    Go to Tools > Transients in the WP dashboard, search for "synergy_software_versions",
 *    and click "Delete".
 *
 * 3. AUTOMATIC EXPIRATION:
 *    The cache will automatically refresh itself after 4 hours.
 * -------------------------------------------------------------------------------------
 */

/**
 * Fetch and cache the latest software version numbers.
 *
 * Endpoint: https://download.synergyamps.com/editor/latest/version
 * Expected response: {"windows":"8.9.3","macos":"8.9.3"}
 *
 * @return array Associative array of versions keyed by OS ('windows', 'macos'), or empty array on failure.
 */
function get_synergy_latest_software_versions() {
    // Unique key used to identify our cached version data in WordPress
    $transient_key = 'synergy_software_versions';

    // Step 1: Check if the version numbers are already cached in WordPress
    $versions = get_transient( $transient_key );

    // Step 2: If no cache exists (expired or deleted), fetch from the version endpoint
    if ( false === $versions ) {
        $endpoint = 'https://download.synergyamps.com/editor/latest/version';

        // Make an HTTP request with a 5-second timeout so page loads never hang
        $response = wp_remote_get( $endpoint, array(
            'timeout'    => 5,
            'user-agent' => 'FriedmanAmplification-VersionChecker/1.0',
        ) );

        // Step 3: Verify the HTTP response code is 200 OK without errors
        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );

            // Validate that we received non-empty JSON data
            if ( is_array( $data ) && ! empty( $data ) ) {
                $versions = array();

                // Loop through all OS keys provided in the JSON (handles 'windows', 'macos', or single-OS)
                foreach ( $data as $os_key => $version_num ) {
                    if ( ! empty( $version_num ) && ( is_string( $version_num ) || is_numeric( $version_num ) ) ) {
                        $versions[ strtolower( sanitize_key( $os_key ) ) ] = sanitize_text_field( (string) $version_num );
                    }
                }

                // Step 4: Save valid version data to transient cache for 4 hours
                if ( ! empty( $versions ) ) {
                    set_transient( $transient_key, $versions, 4 * HOUR_IN_SECONDS );
                }
            }
        }

        // Step 5: If the fetch failed or response was invalid, do NOT use hardcoded version numbers.
        // Cache an empty array for 5 minutes so it retries soon without spamming the remote server.
        if ( false === $versions ) {
            $versions = array();
            set_transient( $transient_key, $versions, 5 * MINUTE_IN_SECONDS );
        }
    }

    // Return the version array from cache or fresh fetch (empty array if unavailable)
    return is_array( $versions ) ? $versions : array();
}

// Backwards-compatible alias for previous function name
if ( ! function_exists( 'get_latest_software_versions' ) ) {
    function get_latest_software_versions() {
        return get_synergy_latest_software_versions();
    }
}

/**
 * Shortcode 1: [syn_es_d]
 * Outputs an <a> anchor link targeting a new blank tab with security attributes (target="_blank" rel="noopener noreferrer").
 *
 * Link text priority:
 * 1. Enclosed content (e.g. [syn_es_d]My Image/Text[/syn_es_d])
 * 2. 'text' attribute (e.g. [syn_es_d text="Download Now"])
 * 3. Default fallback: 'Download'
 *
 * Attributes:
 *   - os:     'windows' (or 'win') or 'mac' (or 'macos') - default: 'windows'
 *   - text:   Optional link text - default: 'Download' (or enclosed content)
 *   - class:  Optional additional CSS classes for the <a> tag (e.g. 'et_pb_button')
 *   - output: 'link' (default, outputs <a> tag) or 'url' (outputs just the raw URL string)
 *
 * Usage Examples:
 *   [syn_es_d os="windows"]                                          -> <a href="..." class="syn-es-download-link" target="_blank" rel="noopener noreferrer">Download</a>
 *   [syn_es_d os="windows" text="Download for Windows"]              -> <a href="..." class="syn-es-download-link" target="_blank" rel="noopener noreferrer">Download for Windows</a>
 *   [syn_es_d os="windows" class="et_pb_button"]Download[/syn_es_d] -> <a href="..." class="syn-es-download-link et_pb_button" target="_blank" rel="noopener noreferrer">Download</a>
 *   [syn_es_d os="windows"]<img src="banner.jpg" />[/syn_es_d]       -> Wraps the image in the secure new-tab download link
 */
function syn_es_download_url_shortcode( $atts, $content = null ) {
    // Block 1: Parse shortcode attributes
    $atts = shortcode_atts( array(
        'os'     => 'windows',  // 'windows', 'win', 'mac', or 'macos'
        'text'   => '',         // Optional custom link text (defaults to 'Download' if omitted)
        'class'  => '',         // Optional additional CSS class(es)
        'output' => 'link',     // 'link' (default HTML <a> tag) or 'url' (raw URL string)
    ), $atts, 'syn_es_d' );

    // Block 2: Normalize the OS string to lowercase
    $os = strtolower( trim( $atts['os'] ) );

    // Block 3: Determine the download URL based on operating system
    if ( in_array( $os, array( 'mac', 'macos', 'osx' ), true ) ) {
        $download_url = 'https://download.synergyamps.com/editor/latest/macos';
    } elseif ( in_array( $os, array( 'windows', 'win' ), true ) ) {
        $download_url = 'https://download.synergyamps.com/editor/latest/windows';
    } else {
        return '';
    }

    // Block 4: If raw URL output was requested, return just the escaped URL string
    if ( 'url' === $atts['output'] ) {
        return esc_url( $download_url );
    }

    // Block 5: Determine link content (enclosed content, custom text attribute, or fallback to 'Download')
    if ( ! empty( $content ) ) {
        $link_content = do_shortcode( $content );
    } elseif ( ! empty( $atts['text'] ) ) {
        $link_content = esc_html( $atts['text'] );
    } else {
        $link_content = esc_html( 'Download' );
    }

    // Block 6: Build class attribute (includes default 'syn-es-download-link' plus any custom classes)
    $classes = array( 'syn-es-download-link' );
    if ( ! empty( $atts['class'] ) ) {
        $classes[] = $atts['class'];
    }
    $class_attr = esc_attr( trim( implode( ' ', $classes ) ) );

    // Block 7: Return the secure <a> element targeting a new tab
    // - target="_blank": Opens link in a new browser tab.
    // - rel="noopener noreferrer": Prevents reverse tabnabbing (window.opener exploits) and protects referrer privacy.
    return sprintf(
        '<a href="%s" class="%s" target="_blank" rel="noopener noreferrer">%s</a>',
        esc_url( $download_url ),
        $class_attr,
        $link_content
    );
}
add_shortcode( 'syn_es_d', 'syn_es_download_url_shortcode' );

/**
 * Shortcode 2: [syn_es_v]
 * Outputs "Synergy Editor Software" followed by the latest version number for the requested OS,
 * wrapped in a styled <span> element.
 *
 * Attributes:
 *   - os:     'windows' (or 'win') or 'mac' (or 'macos') - default: 'windows'
 *   - prefix: Text before the version number - default: 'Synergy Editor Software '
 *   - class:  Optional additional CSS classes for the <span> wrapper
 *
 * Usage Examples:
 *   [syn_es_v os="windows"]           -> <span class="syn-es-version">Synergy Editor Software 8.9.3</span>
 *   [syn_es_v os="mac"]               -> <span class="syn-es-version">Synergy Editor Software 8.9.3</span>
 *   [syn_es_v os="windows" prefix=""] -> <span class="syn-es-version">8.9.3</span>
 */
function syn_es_version_number_shortcode( $atts ) {
    // Block 1: Parse shortcode attributes (defaults to windows with "Synergy Editor Software " prefix)
    $atts = shortcode_atts( array(
        'os'     => 'windows',                  // 'windows', 'win', 'mac', or 'macos'
        'prefix' => 'Synergy Editor Software ', // Text prepended before the version number
        'class'  => '',                         // Optional additional CSS class(es)
    ), $atts, 'syn_es_v' );

    // Block 2: Normalize OS input to match our JSON keys ('windows' or 'macos')
    $os = strtolower( trim( $atts['os'] ) );
    if ( in_array( $os, array( 'mac', 'macos', 'osx' ), true ) ) {
        $os = 'macos';
    } elseif ( in_array( $os, array( 'windows', 'win' ), true ) ) {
        $os = 'windows';
    }

    // Block 3: Retrieve cached version numbers (returns empty array if endpoint failed)
    $versions = get_synergy_latest_software_versions();

    // Block 4: If version for requested OS exists, format prefix + version number
    if ( ! empty( $versions[ $os ] ) ) {
        $text = $atts['prefix'] . $versions[ $os ];
    } else {
        // Block 5: If the GET request failed or version is unavailable, output just the prefix
        $text = rtrim( $atts['prefix'] );
    }

    // Block 6: Build class attribute (includes default 'syn-es-version' plus any custom classes)
    $classes = array( 'syn-es-version' );
    if ( ! empty( $atts['class'] ) ) {
        $classes[] = $atts['class'];
    }
    $class_attr = esc_attr( trim( implode( ' ', $classes ) ) );

    // Block 7: Return wrapped in a span with class
    return sprintf( '<span class="%s">%s</span>', $class_attr, esc_html( $text ) );
}
add_shortcode( 'syn_es_v', 'syn_es_version_number_shortcode' );

/**
 * Shortcode 3: [syn_es_osv]
 * Outputs the operating system compatibility requirement text, wrapped in a styled <p> paragraph tag.
 *
 * Attributes:
 *   - os:    'windows' (or 'win') or 'mac' (or 'macos') - default: 'windows'
 *   - class: Optional additional CSS classes for the <p> wrapper
 *
 * Usage Examples:
 *   [syn_es_osv os="windows"] -> <p class="syn-es-os-compat">Windows 7 64-Bit or higher</p>
 *   [syn_es_osv os="mac"]     -> <p class="syn-es-os-compat">Apple MacOS 10.15 or higher</p>
 *   [syn_es_osv os="macos"]   -> <p class="syn-es-os-compat">Apple MacOS 10.15 or higher</p>
 */
function syn_es_os_compatibility_shortcode( $atts ) {
    // Block 1: Parse shortcode attributes (defaults to windows)
    $atts = shortcode_atts( array(
        'os'    => 'windows', // 'windows', 'win', 'mac', or 'macos'
        'class' => '',        // Optional additional CSS class(es)
    ), $atts, 'syn_es_osv' );

    // Block 2: Normalize the OS string to lowercase
    $os = strtolower( trim( $atts['os'] ) );

    // Block 3: Return the matching compatibility requirement text
    if ( in_array( $os, array( 'mac', 'macos', 'osx' ), true ) ) {
        $text = 'Apple MacOS 10.15 or higher';
    } elseif ( in_array( $os, array( 'windows', 'win' ), true ) ) {
        $text = 'Windows 7 64-Bit or higher';
    } else {
        return '';
    }

    // Block 4: Build class attribute (includes default 'syn-es-os-compat' plus any custom classes)
    $classes = array( 'syn-es-os-compat' );
    if ( ! empty( $atts['class'] ) ) {
        $classes[] = $atts['class'];
    }
    $class_attr = esc_attr( trim( implode( ' ', $classes ) ) );

    // Block 5: Return wrapped in a paragraph <p> with class
    return sprintf( '<p class="%s">%s</p>', $class_attr, esc_html( $text ) );
}
add_shortcode( 'syn_es_osv', 'syn_es_os_compatibility_shortcode' );