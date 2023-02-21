<?php
get_header();
?>

<main id="primary" class="site-main">
	<div class="container">
		<?php
        $args = array(
            'post_type' => 'project',
            'posts_per_page' => 3
        );
        $the_query = new WP_Query( $args ); ?>
        
        <?php if ( $the_query->have_posts() ) : ?>
        
            <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
            <div class="project_container">
                <h2><?php the_title(); ?></h2>
                <?php the_content();  ?>
            </div>
            <?php endwhile; ?>
        
            <?php wp_reset_postdata(); ?>
        
        <?php endif; ?>
	</div>
</main><!-- #main -->

<?php
get_footer();
