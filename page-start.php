<?php
/**
 * Template Name: Start Template
 * Description: A Page Template that adds a sidebar to pages
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>

		<!-- promo block -->
		
		<div class="promo">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php the_content(); ?>
				<?php endwhile; // end of the loop. ?>
		</div><!-- .promo -->
		
        <?php if(is_active_sidebar('top-start-sidebar')){ ?>
			<div class="boxes">
				<?php dynamic_sidebar( 'top-start-sidebar' ); ?>
			</div><!-- .boxes -->
		<?php } ?>
		<!-- news columns -->
		<div class="columns">
			<div class="columns-holder">
				<div class="columns-frame">
					<div class="columns-block">
						<div class="left-column">
							<?php dynamic_sidebar( 'left-start-sidebar' ); ?>
						</div><!-- .left-column -->
                       	<?php dynamic_sidebar( 'two-column-start-sidebar' ); ?>
<?php
// added by puggan

/*
		<div class="main-holder">
			<div id="twocolumns">
				<div role="main" id="content">
					<div class="content-holder">
						<div class="content-frame">
							<div class="content-block">
								<div class="page type-page status-publish hentry">
									<div class="entry-content">

*/

// 	$start2 = "<h2>test</h2>";
	$start2 = get_page_by_path("start2");
	if($start2)
	{
		echo <<<HTML_BLOCK
				<div role="main" id="content">
					<div class="content-holder">
						<div class="content-frame">
							<div class="content-block">
								<div class="page type-page status-publish hentry">
									<div class="entry-content">
HTML_BLOCK;
		echo str_replace( ']]>', ']]&gt;', apply_filters( 'the_content', $start2->post_content ) );
		echo <<<HTML_BLOCK
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
HTML_BLOCK;
	}
// 	var_dump($start2);

	$posts = get_posts ("cat=2&showposts=10");
	if($posts)
	{
		echo <<<HTML_BLOCK
						<aside class="box widget widget_hip_nyhetlist" id="hip_nyhetlist-2">
							<div class="column">
								<div class="column-holder">
									<div class="column-frame">
										<div class="column-block">
											<h2 class='widget-title'>Nyheter</h2>
HTML_BLOCK;
		
		foreach ($posts as $post)
		{
			setup_postdata($post);

			$permalink = get_the_permalink();
			$title = get_the_title();
//print_r($post);
			echo <<<HTML_BLOCK
								<h3><a href='{$permalink}'>{$title}</a></h3>
								<div>
									{$post->post_content}
								</div>
HTML_BLOCK;
		}
		
		echo <<<HTML_BLOCK
										</div>
									</div>
								</div>
							</div>
						</aside>
HTML_BLOCK;
	}
?>
					</div><!-- .columns-block -->
				</div><!-- .columns-frame -->
			</div><!-- .columns-holder -->
		</div><!-- .columns -->

<?php get_footer(); ?>
