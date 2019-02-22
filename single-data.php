<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

if (!defined('ABSPATH')) die("don't load this page directly");

get_header(); ?>
<div class="main-holder">
	<div id="twocolumns">
		<div id="content" role="main">
			<div class="content-holder">
				<div class="content-frame">
					<div class="content-block">
						<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<header class="entry-header">
								<h1 class="entry-title"><?php the_title(); ?></h1>
							</header><!-- .entry-header -->

							<div class="entry-content">
						<?php 
if($post->post_name == 'gruppdiagram')
{
	$teams_data = parse_gruppdiagram($post->post_content);
	echo teams_table($teams_data);
	echo "<hr style='clear: both;' />";
}
else if(substr($post->post_name, -11) == 'spelsschema')
{
	$schema = parse_schema($post->post_content);
	echo schema_table($schema);
	echo "<hr style='clear: both;' />";
}
else if(function_exists('parse_' . $post->post_name))
{
	$function_name = ('parse_' . $post->post_name);
	echo "<pre>";
	print_r($function_name($post->post_content));
	echo "</pre>";
}

echo "<pre>";
echo $post->post_content;
echo "</pre>";
						?>
							</div><!-- #post-<ID> -->
						</div><!-- .entry-content -->
					</div><!-- .content-block -->
				</div><!-- .content-frame -->
			</div><!-- .content-holder -->
		</div><!-- #content -->

		<?php get_template_part('sidebar', 'placeholder-ads'); ?>

	</div><!-- #two-columns -->

</div><!-- .main-holder -->
<?php get_footer(); ?>