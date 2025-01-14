<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {
    wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/**
 * Funzioni personalizzate per gestire le recensioni con ACF e CPT.
 */

// Registrazione del Custom Post Type "Recensioni".
function custom_cpt_recensioni() {
    $labels = array(
        'name' => 'Recensioni',
        'singular_name' => 'Recensione',
        'menu_name' => 'Recensioni',
        'add_new' => 'Aggiungi Nuova',
        'all_items' => 'Tutte le Recensioni',
        'add_new_item' => 'Aggiungi Nuova Recensione',
        'edit_item' => 'Modifica Recensione',
        'new_item' => 'Nuova Recensione',
        'view_item' => 'Visualizza Recensione',
        'search_items' => 'Cerca Recensioni',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'has_archive' => false,
        'rewrite' => array('slug' => 'recensioni'),
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
    );

    register_post_type('recensioni', $args);
}
add_action('init', 'custom_cpt_recensioni');

// Funzione per generare le stelle
function get_star_svg($filled = true) {
    if ($filled) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="20px" viewBox="0 0 32 32" style="margin-right: 3px;"><path fill="#FFD700" d="M20.388,10.918L32,12.118l-8.735,7.749L25.914,31.4l-9.893-6.088L6.127,31.4l2.695-11.533L0,12.118 l11.547-1.2L16.026,0.6L20.388,10.918z"></path></svg>';
    } else {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="20px" viewBox="0 0 32 32" style="margin-right: 3px;"><path fill="#ddd" d="M20.388,10.918L32,12.118l-8.735,7.749L25.914,31.4l-9.893-6.088L6.127,31.4l2.695-11.533L0,12.118 l11.547-1.2L16.026,0.6L20.388,10.918z"></path></svg>';
    }
}

// Shortcode per visualizzare una recensione tramite ID.
function recensioni_shortcode($atts) {
    ob_start();
    
    $atts = shortcode_atts(
        array('id' => ''),
        $atts,
        'recensioni'
    );

    if (empty($atts['id'])) {
        return 'ID non specificato';
    }

    $post_id = intval($atts['id']);

    // Controllo se l'ID è valido e se il post è del tipo "recensioni".
    if (get_post_type($post_id) !== 'recensioni') {
        return 'Recensione non trovata.';
    }

    // Recupero dei campi ACF.
    $reviewer_image = get_field('reviewer_image', $post_id);
    $reviewer_name = get_field('reviewer_name', $post_id);
    $rating = get_field('rating', $post_id) ?: 0;
    $review_date = get_field('review_date', $post_id);
    $review_text = get_field('review_text', $post_id);
    $review_link = get_field('review_link', $post_id);

    // HTML della card della recensione.
    ?>
    <div class="recensione-card" style="max-width: 1000px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <div style="display: flex; gap: 20px; align-items: flex-start;">
            <!-- Immagine recensore -->
            <div style="flex-shrink: 0;">
                <?php if ($reviewer_image): ?>
                    <img src="<?php echo esc_url($reviewer_image); ?>" alt="<?php echo esc_attr($reviewer_name); ?>" style="width: 56px; height: 56px; border-radius: 50%;">
                <?php endif; ?>
            </div>

            <!-- Informazioni recensione -->
            <div>
                <h3 style="margin: 0;">
                    <?php if ($review_link): ?>
                        <a href="<?php echo esc_url($review_link); ?>" target="_blank">
                            <?php echo esc_html(get_the_title($post_id)); ?>
                        </a>
                    <?php else: ?>
                        <?php echo esc_html(get_the_title($post_id)); ?>
                    <?php endif; ?>
                </h3>
                <!-- Stelle e data in colonna -->
                <div style="margin-top: 5px; display: flex; flex-direction: column;">
                    <div class="recensione-rating" style="display: flex; gap: 3px;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php echo $i <= $rating ? get_star_svg(true) : get_star_svg(false); ?>
                        <?php endfor; ?>
                    </div>
                    <div style="margin-top: 5px; font-size: 14px; color: #666;">
                        <?php echo esc_html(date_i18n('j F Y', strtotime($review_date))); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="recensione-body-container" style="max-height: 203px; overflow-y: auto; margin-top: 20px; padding-right: 10px;">
            <div class="recensione-body" style="line-height: 1.5;">
                <p style="margin: 0;">
                    <?php echo nl2br(esc_html($review_text)); ?>
                </p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('recensioni', 'recensioni_shortcode');

// Aggiunta colonna "Shortcode" nella lista admin delle recensioni.
function recensioni_add_shortcode_column($columns) {
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}
add_filter('manage_recensioni_posts_columns', 'recensioni_add_shortcode_column');

function recensioni_display_shortcode_column($column, $post_id) {
    if ($column === 'shortcode') {
        echo '[recensioni id="' . esc_attr($post_id) . '"]';
    }
}
add_action('manage_recensioni_posts_custom_column', 'recensioni_display_shortcode_column', 10, 2);

// Template personalizzato per la visualizzazione di una recensione singola.
function recensioni_single_template($template) {
    if (is_singular('recensioni')) {
        $custom_template = locate_template('single-recensione.php');
        if ($custom_template) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('single_template', 'recensioni_single_template');

