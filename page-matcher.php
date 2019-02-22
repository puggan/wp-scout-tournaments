<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
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
<?php while ( have_posts() ) : the_post(); ?>
								<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
									<header class="entry-header">
										<h1 class="entry-title"><?php the_title(); ?></h1>
									</header><!-- .entry-header -->
									<?php if( has_post_thumbnail() ) the_post_thumbnail( 'main428' ); ?>
<?php
	$next_break = null;
	$breaks = $wpdb->get_results("SELECT * FROM game_breaks ORDER BY break_from");
	if($breaks) $next_break = array_shift($breaks);
	$query = <<<SQL_QUERY
	SELECT
	game_matches.*,
	game_match_time.field_id,
	game_match_time.match_time,
	game_results.done,
	game_results.home_goals,
	game_results.away_goals
	FROM game_matches
	LEFT JOIN game_match_time USING (match_id)
	LEFT JOIN game_results USING (match_id)
	ORDER BY
	game_match_time.match_time IS NULL,
	game_match_time.match_time,
	game_match_time.field_id
SQL_QUERY;
	$matcher = $wpdb->get_results( $query, OBJECT_K );
	$query = 'SELECT game_teams.team_id, game_teams.team_name, game_classes.class_name, game_groups.group_name FROM game_teams LEFT JOIN game_classes USING (class_id) LEFT JOIN game_groups USING (group_id) ORDER BY IF(game_teams.group_id IS NULL, 0, 1), game_teams.class_id, game_teams.group_id, game_teams.team_name';
	$teams = $wpdb->get_results( $query, OBJECT_K );
	if($matcher)
	{
		echo "<style>TABLE.puggan_table TD, TABLE.puggan_table TH {padding: 3px 8px; border: solid black 1px;} TABLE.puggan_table {border: solid gray 2px;}</style>";

		echo "<h2>Matcher</h2>";
		echo "<table class='puggan_table'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Plan</th>";
		echo "<th>Tid</th>";
		echo "<th colspan='2'>Lag 1</th>";
		echo "<th colspan='2'>Lag 2</th>";
		echo "<th>Grupp</th>";
// 		echo "<th>Klass</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";

		$odd = FALSE;
		foreach($matcher as $match)
		{
			while($next_break AND $next_break->break_from < $match->match_time)
			{
					echo '<tr><td colspan="7" style="text-align: center;">' . htmlentities($next_break->break_name) . '</td></tr>';

				$next_break = null;
				if($breaks) $next_break = array_shift($breaks);
			}
			$table_row_class = (($odd = !$odd) ? 'odd' : 'even');
			echo "<tr class='{$table_row_class}'>";
			echo "<td title='{$match->match_id}'>{$match->field_id}</td>";
			echo "<td>" . substr($match->match_time, 11, 5) . "</td>";
			if($match->home_team_id)
            {
                echo "<td title='{$match->home_team_id}'>{$teams[$match->home_team_id]->team_name}</td>";
            }
			else
            {
                echo "<td>{$match->home_team_description}</td>";
            }
			if($match->done)
			{
				echo "<td><b>{$match->home_goals}</b></td>";
			}
			else if($match->done === 0)
			{
				echo "<td><i>{$match->home_goals}</i></td>";
			}
			else
			{
				echo "<td></td>";
			}
			if($match->away_team_id)
            {
                echo "<td title='{$match->away_team_id}'>{$teams[$match->away_team_id]->team_name}</td>";
            }
			else
            {
                echo "<td>{$match->away_team_description}</td>";
            }
			if($match->done)
			{
				echo "<td><b>{$match->away_goals}</b></td>";
			}
			else if($match->done === 0)
			{
				echo "<td><i>{$match->away_goals}</i></td>";
			}
			else
			{
				echo "<td></td>";
			}
			echo "<td>{$teams[$match->home_team_id]->group_name}</td>";
// 			echo "<td>{$teams[$match->home_team_id]->class_name}</td>";
			echo "</tr>";
		}
		
		echo "</tbody>";
		echo "</table>";
	}
?>
									<div class="entry-content">
										<?php the_content(); ?>
										<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', 'twentyeleven' ) . '</span>', 'after' => '</div>' ) ); ?>
									</div><!-- .entry-content -->
									<footer class="entry-meta">		
										<?php edit_post_link( __( 'Edit', 'scout' ), '<span class="edit-link">', '</span>' ); ?>
									</footer><!-- .entry-meta -->
								</div><!-- #post-<?php the_ID(); ?> -->
								<?php comments_template( '', true ); ?>
<?php endwhile; // end of the loop. ?>
							</div><!-- .content-block -->
						</div><!-- .content-frame -->
					</div><!-- .content-holder -->
				</div><!-- #content -->
				
				<?php get_template_part('sidebar', 'placeholder-ads'); ?>
				
			</div><!-- #two-columns -->
		
	</div><!-- .main-holder -->

<?php get_footer(); ?>
