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

(static function () {
    global $wpdb;
    $e = static function ($s) {
        return htmlentities($s, ENT_QUOTES | ENT_HTML5);
    };

    get_header(); ?>

    <div class="main-holder">
        <div id="twocolumns">
            <div id="content" role="main">
                <div class="content-holder">
                    <div class="content-frame">
                        <div class="content-block">
                            <?php while (have_posts()) : the_post(); ?>
                                <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                                    <header class="entry-header">
                                        <h1 class="entry-title"><?= the_title('', '', false) ?></h1>
                                    </header><!-- .entry-header -->
                                    <?php if (has_post_thumbnail()) {
                                        the_post_thumbnail('main428');
                                    } ?>
                                    <?php
                                    $teams = $wpdb->get_results(
                                        'SELECT game_teams.team_id, game_teams.class_id, game_teams.group_id, game_teams.team_name, game_classes.class_name, game_groups.group_name FROM game_teams LEFT JOIN game_classes USING (class_id) LEFT JOIN game_groups USING (group_id) ORDER BY game_teams.class_id, game_teams.group_id, game_teams.team_name',
                                        OBJECT_K
                                    );

                                    $team_name_to_id = [];

                                    $points_tables = [];
                                    foreach ($teams as $team) {
                                        $points_tables[$team->class_name][$team->group_name][$team->team_name] = [
                                            'point' => 0,
                                            'good_goals' => 0,
                                            'bad_goals' => 0,
                                            'wins' => 0,
                                            'draw' => 0,
                                            'lose' => 0,
                                        ];
                                        $team_name_to_id[$team->team_name] = $team->team_id;
                                    }

                                    $played_matches = $wpdb->get_results(
                                        "SELECT game_results.*, game_matches.* FROM game_results INNER JOIN game_matches USING (match_id) WHERE game_results.done = 1 AND match_type = 'GROUP'",
                                        OBJECT_K
                                    );

                                    foreach ($played_matches as $match) {
                                        $home_team = $teams[$match->home_team_id];
                                        $away_team = $teams[$match->away_team_id];

                                        if ($match->home_goals > $match->away_goals) {
                                            $points_tables[$home_team->class_name][$home_team->group_name][$home_team->team_name]['point'] += 3;
                                            $points_tables[$home_team->class_name][$home_team->group_name][$home_team->team_name]['wins']++;
                                            $points_tables[$away_team->class_name][$away_team->group_name][$away_team->team_name]['lose']++;
                                        } elseif ($match->home_goals < $match->away_goals) {
                                            $points_tables[$away_team->class_name][$away_team->group_name][$away_team->team_name]['point'] += 3;
                                            $points_tables[$away_team->class_name][$away_team->group_name][$away_team->team_name]['wins']++;
                                            $points_tables[$home_team->class_name][$home_team->group_name][$home_team->team_name]['lose']++;
                                        } else {
                                            $points_tables[$home_team->class_name][$home_team->group_name][$home_team->team_name]['point'] += 1;
                                            $points_tables[$away_team->class_name][$away_team->group_name][$away_team->team_name]['point'] += 1;
                                            $points_tables[$away_team->class_name][$away_team->group_name][$away_team->team_name]['draw']++;
                                            $points_tables[$home_team->class_name][$home_team->group_name][$home_team->team_name]['draw']++;
                                        }

                                        $points_tables[$home_team->class_name][$home_team->group_name][$home_team->team_name]['good_goals'] += $match->home_goals;
                                        $points_tables[$home_team->class_name][$home_team->group_name][$home_team->team_name]['bad_goals'] += $match->away_goals;
                                        $points_tables[$away_team->class_name][$away_team->group_name][$away_team->team_name]['good_goals'] += $match->away_goals;
                                        $points_tables[$away_team->class_name][$away_team->group_name][$away_team->team_name]['bad_goals'] += $match->home_goals;
                                    }

                                    foreach ($points_tables as $class_name => $class_points_tables) {
                                        foreach ($class_points_tables as $group_name => $group_points_tables) {
                                            echo <<<HTML_BLOCK
									<div>
										<h3>{$class_name} - {$group_name}</h3>
										<table class='sort_yes filter_yes'>
											<thead>
												<tr>
													<th>Lag</th>
													<th>Poäng</th>
													<th>Matcher</th>
													<th title="Mål skillnad">Mål S</thtitle>
													<th>Vinster</th>
													<th>Lika</th>
													<th>Förluster</th>
												</tr>
											</thead>
											<tbody>
HTML_BLOCK;

                                            uasort($group_points_tables, static function ($a, $b) {
                                                return ($b['point'] <=> $a['point']) ?: (($b['good_goals'] - $b['bad_goals']) <=> ($a['good_goals'] - $a['bad_goals']));
                                            });

                                            $odd = true;
                                            foreach ($group_points_tables as $team_name => $team_data) {
                                                // array('point' => 0, 'good_goals' => 0, 'bad_goals' => 0, 'wins' => 0, 'draw' => 0, 'lose' => 0);
                                                $table_row_class = (($odd = !$odd) ? 'odd' : 'even');
                                                echo "											";
                                                echo "<tr class='{$table_row_class}'>";
                                                echo "<td title='{$team_name_to_id[$team_name]}'>" . $e($team_name) . "</td>";

                                                echo "<td>" . $e($team_data['point']) . "</td>";
                                                echo "<td>" . $e($team_data['wins'] + $team_data['draw'] + $team_data['lose']) . "</td>";
                                                echo '<td title="' . $e($team_data['good_goals']) . ' - ' . $e($team_data['bad_goals']) . '">' . $e(
                                                        $team_data['good_goals'] - $team_data['bad_goals']
                                                    ) . "</td>";
                                                echo "<td>" . $e($team_data['wins']) . "</td>";
                                                echo "<td>" . $e($team_data['draw']) . "</td>";
                                                echo "<td>" . $e($team_data['lose']) . "</td>";
                                                echo "</tr>\n";
                                            }

                                            echo <<<HTML_BLOCK
											</tbody>
										</table>
									</div>
HTML_BLOCK;
                                        }
                                    }

                                    ?>
                                    <div class="entry-content">
                                        <?php the_content(); ?>
                                        <?= wp_link_pages(['echo' => false, 'before' => '<div class="page-link"><span>' . __('Pages:', 'twentyeleven') . '</span>', 'after' => '</div>']) ?>
                                    </div><!-- .entry-content -->
                                    <footer class="entry-meta">
                                        <?php edit_post_link(__('Edit', 'scout'), '<span class="edit-link">', '</span>'); ?>
                                    </footer><!-- .entry-meta -->
                                </div><!-- #post-<?php the_ID(); ?> -->
                                <?php comments_template('', true); ?>
                            <?php endwhile; // end of the loop. ?>
                        </div><!-- .content-block -->
                    </div><!-- .content-frame -->
                </div><!-- .content-holder -->
            </div><!-- #content -->

            <?php get_template_part('sidebar', 'placeholder-ads'); ?>

        </div><!-- #two-columns -->

    </div><!-- .main-holder -->

    <?php get_footer();
})();
