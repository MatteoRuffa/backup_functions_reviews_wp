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
        return '<svg xmlns="http://www.w3.org/2000/svg" width="20px" viewBox="0 0 32 32" style="margin-right: 3px;"><path fill="#000" d="M20.388,10.918L32,12.118l-8.735,7.749L25.914,31.4l-9.893-6.088L6.127,31.4l2.695-11.533L0,12.118 l11.547-1.2L16.026,0.6L20.388,10.918z"></path></svg>';
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
<div class="recensione-card">
    <div class="recensione-header">
        <!-- Immagine recensore -->
        <div class="recensione-img-container">
            <?php if ($reviewer_image): ?>
                <img src="<?php echo esc_url($reviewer_image); ?>" alt="<?php echo esc_attr($reviewer_name); ?>" class="recensione-img">
            <?php endif; ?>
        </div>

        <!-- Informazioni recensione -->
        <div class="recensione-info-container">
            <h3 class="recensione-title">
                <?php if ($review_link): ?>
                    <a href="<?php echo esc_url($review_link); ?>" target="_blank">
                        <?php echo esc_html(get_the_title($post_id)); ?>
                    </a>
                <?php else: ?>
                    <?php echo esc_html(get_the_title($post_id)); ?>
                <?php endif; ?>
            </h3>
            <!-- Stelle e data in colonna -->
            <div class="recensione-meta">
                <div class="recensione-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php echo $i <= $rating ? get_star_svg(true) : get_star_svg(false); ?>
                    <?php endfor; ?>
                </div>
                <div class="recensione-date">
                    <?php echo esc_html(date_i18n('j F Y', strtotime($review_date))); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="recensione-body-container">
        <div class="recensione-body">
            <p class="recensione-text">
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



// Hook per registrare il Custom Post Type
add_action('init', 'register_collections_cpt');

function register_collections_cpt() {
    // Registrazione del CPT "Collections"
    $labels = array(
        'name'               => 'Collections',
        'singular_name'      => 'Collection',
        'menu_name'          => 'Collections',
        'name_admin_bar'     => 'Collection',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Collection',
        'edit_item'          => 'Edit Collection',
        'new_item'           => 'New Collection',
        'view_item'          => 'View Collection',
        'search_items'       => 'Search Collections',
        'not_found'          => 'No collections found',
        'not_found_in_trash' => 'No collections found in Trash',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=recensioni',
        'capability_type'    => 'post',
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'collections'), 
        'hierarchical'       => false,
        'supports'           => array('title', 'editor'), 
    );
    register_post_type('collections', $args);
    
}

// Aggiunta di una colonna per gli shortcode nella tabella del CPT "Collections"
add_filter('manage_collections_posts_columns', 'add_collections_shortcode_column');
add_action('manage_collections_posts_custom_column', 'populate_collections_shortcode_column', 10, 2);

function add_collections_shortcode_column($columns) {
    $columns['shortcode'] = 'Shortcode';
    $columns['recensioni'] = 'Recensioni'; 
    return $columns;
}

function populate_collections_shortcode_column($column, $post_id) {
    if ($column === 'shortcode') {
        echo '[collection id="' . $post_id . '"]';
    }

    if ($column === 'recensioni') {
        // Recupera i dati del campo "recensioni" tramite ACF
        $recensioni = get_field('recensioni', $post_id);

        if (!empty($recensioni) && is_array($recensioni)) {
            // Mostra i titoli delle recensioni selezionate
            $titoli = array_map(function($recensione) {
                return get_the_title($recensione->ID);
            }, $recensioni);
            echo implode(', ', $titoli);
        } else {
            echo 'Nessuna recensione selezionata';
        }
    }
}

add_filter('single_template', function ($template) {
    if (get_post_type() === 'collections') {
        $custom_template = locate_template('single-collection.php');
        if ($custom_template) {
            return $custom_template;
        }
    }
    return $template;
});

// Shortcode per visualizzare una collection
add_shortcode('collection', 'render_collection_shortcode');

function render_collection_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => null,
    ), $atts, 'collection');

    $collection_id = $atts['id'];
    if (!$collection_id) {
        return 'Nessuna collection specificata.';
    }

    // Recupera le recensioni associate alla collection
    $recensioni = get_field('recensioni', $collection_id);
    if (empty($recensioni) || !is_array($recensioni)) {
        return '<p>Nessuna recensione trovata per questa collection.</p>';
    }

    // Genera l'output HTML delle recensioni
    ob_start();
    foreach ($recensioni as $recensione_id) {
        // Recupera il post della recensione
        $recensione = get_post($recensione_id);
        if ($recensione instanceof WP_Post) {
            // Usa lo stesso HTML della recensione
            echo do_shortcode('[recensioni id="' . $recensione->ID . '"]');
        }
    }
    return ob_get_clean();
}
