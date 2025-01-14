<?php get_header(); ?>

<main id="maincontent" role="main">
    <div class="section">
        <?php while ( have_posts() ) : the_post(); ?>
            <article>
                <?php echo do_shortcode( '[recensioni id="' . get_the_ID() . '"]' ); ?>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>
