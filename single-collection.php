<?php
/**
 * Template per visualizzare una singola collection.
 */

get_header(); ?>

<div class="single-collection">
    <?php
    // Richiama lo shortcode per visualizzare le recensioni
    echo do_shortcode('[collection id="' . get_the_ID() . '"]');
    ?>
</div>

<?php get_footer(); ?>
