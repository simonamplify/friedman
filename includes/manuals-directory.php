<?php
/**
 * Manuals directory shortcode
 * Usage: [manuals_directory]
 */

if (!defined('ABSPATH')) {
    exit;
}

// Return manual URL plus file type details regardless of ACF return format.
function fam_get_manual_file_data($product_id) {
    $manual = get_field('eng_manual_url', $product_id);

    if (empty($manual)) {
        return false;
    }

    $url = '';
    $ext = '';
    $mime = '';

    // ACF file field may return URL, array, or attachment ID.
    if (is_array($manual)) {
        $url = !empty($manual['url']) ? $manual['url'] : '';
        $mime = !empty($manual['mime_type']) ? $manual['mime_type'] : '';

        if (!empty($manual['filename'])) {
            $ext = strtolower(pathinfo($manual['filename'], PATHINFO_EXTENSION));
        }
    }
    elseif (is_numeric($manual)) {
        $attachment_id = (int) $manual;
        $url = wp_get_attachment_url($attachment_id);
        $mime = get_post_mime_type($attachment_id);
    }
    elseif (is_string($manual)) {
        $url = $manual;
    }

    if (empty($url)) {
        return false;
    }

    // Fallback extension detection from URL path.
    if (empty($ext)) {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        }
    }

    // Fallback mime detection.
    if (empty($mime)) {
        $filetype = wp_check_filetype($url);
        $mime = !empty($filetype['type']) ? $filetype['type'] : '';
    }

    // Display label shown to users.
    $label = !empty($ext) ? strtoupper($ext) : 'FILE';

    return array(
        'url' => $url,
        'ext' => $ext,
        'mime' => $mime,
        'label' => $label,
    );
}

// Woo has no native "primary" category, so use a stable fallback.
function fam_get_primary_woo_category($product_id) {
    $terms = get_the_terms($product_id, 'product_cat');

    if (empty($terms) || is_wp_error($terms)) {
        return false;
    }

    // Keep grouping predictable by sorting term names.
    usort($terms, function ($a, $b) {
        return strcasecmp($a->name, $b->name);
    });

    return $terms[0];
}

function fam_manuals_directory_shortcode() {
    // Pull all published products as IDs for a lighter query.
    $product_ids = get_posts(array(
        'post_type'              => 'product',
        'post_status'            => 'publish',
        'posts_per_page'         => -1,
        'fields'                 => 'ids',
        'orderby'                => 'title',
        'order'                  => 'ASC',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => true,
    ));

    if (empty($product_ids)) {
        return '<p>No manuals found.</p>';
    }

    $groups = array();

    foreach ($product_ids as $product_id) {
        // Skip products that do not have a manual.
        $manual_file = fam_get_manual_file_data($product_id);
        if (!$manual_file) {
            continue;
        }

        // Group each product under a single category bucket.
        $cat = fam_get_primary_woo_category($product_id);
        $group_key = $cat ? (string) $cat->term_id : 'uncategorized';
        $group_name = $cat ? $cat->name : 'Uncategorized';

        if (!isset($groups[$group_key])) {
            $groups[$group_key] = array(
                'name'  => $group_name,
                'items' => array(),
            );
        }

        $groups[$group_key]['items'][] = array(
            'title' => get_the_title($product_id),
            'url'   => get_permalink($product_id),
            'manual' => $manual_file,
        );
    }

    if (empty($groups)) {
        return '<p>No manuals found.</p>';
    }

    // Sort category groups alphabetically for display.
    uasort($groups, function ($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    ob_start();
    ?>
    <div class="manuals-directory">
        <?php foreach ($groups as $group) : ?>
            <section class="manuals-group">
                <h2><?php echo esc_html($group['name']); ?></h2>
                <ul class="manuals-list">
                    <?php foreach ($group['items'] as $item) : ?>
                        <li class="manuals-item">
                            <article class="manual-card">
                                <header class="manual-header">
                                    <h3>
                                        <a href="<?php echo esc_url($item['url']); ?>">
                                            <?php echo esc_html($item['title']); ?>
                                        </a>
                                    </h3>
                                </header>
                                <footer class="manual-footer">
                                    <div class="manual-meta">
                                        <span class="manual-label">Manual</span>
                                        <span>·</span>
                                        <span class="manual-type"><?php echo esc_html($item['manual']['label']); ?></span>
                                    </div>
                                    <a
                                        href="<?php echo esc_url($item['manual']['url']); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        download
                                        class="manual-link manual-type-<?php echo esc_attr($item['manual']['ext'] ? $item['manual']['ext'] : 'file'); ?>"
                                        aria-label="<?php echo esc_attr('Download ' . $item['manual']['label'] . ' manual for ' . $item['title']); ?>"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-to-line-icon lucide-arrow-down-to-line" aria-hidden="true"><path d="M12 17V3"></path><path d="m6 11 6 6 6-6"></path><path d="M19 21H5"></path></svg>
                                        <span>Download</span>
                                    </a>
                                </footer>
                            </article>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('manuals_directory', 'fam_manuals_directory_shortcode');