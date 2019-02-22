<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>

<div class="main-holder">
	<div id="twocolumns">
		<div id="content" role="main">
			<div class="content-holder">
				<div class="content-frame">
					<div class="content-block">
						
						<?php if ( have_posts() ) : ?>

							<header class="page-header">
								<h1 class="page-title">
									<?php if (is_category()) : ?>
										<?php printf( __( 'Category Archives: %s', 'scout' ), '<span>' . single_cat_title( '', false ) . '</span>' ); ?>
									<?php elseif(is_tag()) : ?>
										<?php printf( __( 'Tag Archives: %s', 'scout' ), '<span>' . single_tag_title( '', false ) . '</span>' ); ?>
									<?php elseif(is_tax()) : ?>
										<?php
											/* Get plural name if it exists, otherwise get single term title */
											$term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
											$term = get_option("tax_meta_$term->term_id");
											echo !empty($term['tax_meta_plural_name']) ? $term['tax_meta_plural_name'] : single_term_title();
										?>
									<?php elseif( function_exists( 'is_post_type_archive' ) && is_post_type_archive() ) : ?>
										<?php post_type_archive_title(); ?>
									<?php elseif(is_author()) : ?>
										<?php
											/* Queue the first post, that way we know
											 * what author we're dealing with (if that is the case).
											 *
											 * We reset this later so we can run the loop
											 * properly with a call to rewind_posts().
											 */
											the_post();											
											printf( __( 'Author Archives: %s', 'scout' ), get_the_author() );
											rewind_posts();
										?>
									<?php elseif ( is_day() ) : ?>
										<?php _e( 'Blog Archives', 'scout' ); ?> <?php echo get_the_date(); ?>
									<?php elseif ( is_month() ) : ?>
										<?php _e( 'Blog Archives', 'scout' ); ?> <?php echo get_the_date('F Y'); ?>
									<?php elseif ( is_year() ) : ?>
										<?php _e( 'Blog Archives', 'scout' ); ?> <?php echo get_the_date('Y'); ?>
									<?php else : ?>
										<?php _e( 'Blog Archives', 'scout' ); ?>
									<?php endif; ?>
								</h1>
								<?php
									$category_description = category_description();
									if(!empty($category_description)) echo apply_filters( 'category_archive_meta', '<div class="category-archive-meta content-preamble">' . $category_description . '</div>' );
								?>
							</header>

							<?php /* Start the Loop */ ?>
							<?php while ( have_posts() ) : the_post(); ?>

								<?php
										get_template_part('content', 'blog-archives');
									
								?>

							<?php endwhile; ?>

							<?php twentyeleven_content_nav( 'nav-below' ); ?>

						<?php else : ?>

							<article id="post-0" class="post no-results not-found">
								<header class="entry-header">
									<h1 class="entry-title"><?php _e( 'Nothing Found', 'scout' ); ?></h1>
								</header><!-- .entry-header -->

								<div class="entry-content">
									<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'scout' ); ?></p>
									<?php get_search_form(); ?>
								</div><!-- .entry-content -->
							</article><!-- #post-0 -->

						<?php endif; ?>
					</div><!-- .content-block -->
				</div><!-- .content-frame -->
			</div><!-- .content-holder -->
		</div><!-- #content -->

		<?php get_template_part('sidebar', 'placeholder-ads'); ?>

	</div><!-- #twocolumns -->

</div><!-- .main-holder -->

<?php get_footer(); ?>