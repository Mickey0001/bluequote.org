<!DOCTYPE html>
<html <?php language_attributes(); ?>>

    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        
        <!-- wp_header -->
        <?php wp_head(); ?>
    </head>

    <body class="single single-template-builder-editor themify_builder_active builder-breakpoint-desktop">

        <div class="single-template-builder-container">

            <?php if (have_posts()) while (have_posts()) : the_post(); ?>
                    <h2 class="builder_title"><?php the_title() ?></h2>
                    <?php the_content(); ?>
                <?php endwhile; ?>

        </div>
        <!-- /.single-template-builder-container -->

        <!-- wp_footer -->
        <?php wp_footer(); ?>

    </body>

</html>