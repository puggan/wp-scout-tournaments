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

get_header();
while ( have_posts() ) the_post();

	$fetch_query = <<<SQL_BLOCK
SELECT
	match_id,
	home_team.team_name AS home_team_name,
	away_team.team_name AS away_team_name,
	game_results.*,
	game_match_referees.*,
	game_match_time.*,
	game_groups.group_name,
	game_classes.class_name
FROM game_matches
	INNER JOIN game_teams AS home_team ON (home_team.team_id = game_matches.home_team_id)
	INNER JOIN game_classes USING (class_id)
	INNER JOIN game_groups USING (group_id)
	INNER JOIN game_teams AS away_team ON (away_team.team_id = game_matches.away_team_id)
	INNER JOIN game_match_time USING (match_id)
	LEFT JOIN game_match_referees USING (match_id)
	LEFT JOIN game_results USING (match_id)
WHERE game_match_time.match_status IN ('STARTED', 'QUEUE')
ORDER BY
	game_match_time.match_status DESC,
	game_match_time.match_time
LIMIT 8
SQL_BLOCK;

	$matches = $wpdb->get_results($fetch_query, OBJECT_K);

	echo <<<HTML_BLOCK
<html>
	<head>
		<title>IBN15 - Mål</title>
		<link href="/d/js/tables.css" type="text/css" rel="stylesheet" />
		<style>
			H1 SPAN.goals
			{
				font-size: larger;
				display: inline-block;
				padding: 10px;
			}
		</style>
	</head>
	<body>
		<div style='width: 800px; margin: auto;'>
HTML_BLOCK;

	$index = 0;
	foreach($matches as $index => $match)
	{
		if($matches[$index]->match_status == 'STARTED')
		{
			echo <<<HTML_BLOCK
			<h1>
				<span class='side'>{$match->field_id}: </span>
				<span class='team'>{$match->home_team_name}</span>
				<span class='goals'>{$match->home_goals}</span>
				<span class='goals_sep'> - </span>
				<span class='goals'>{$match->away_goals}</span>
				<span class='team'>{$match->away_team_name}</span>
			</h1>
HTML_BLOCK;
			unset($matches[$index]);
		}
	}

	echo <<<HTML_BLOCK
			<h2>Kommande matcher</h1>
			<table>
				<thead>
					<tr>
						<th>Tid</th>
						<th>Lag 1</th>
						<th>Lag 2</th>
						<th>Klass</th>
						<th>Grupp</th>
					</tr>
				</thead>
				<tbody>
HTML_BLOCK;

	$odd = FALSE;
	foreach($matches as $match)
	{
		$short_time = substr($match->match_time, 11, 5);
		$table_class_row = (($odd = !$odd) ? 'odd' : 'even');
	echo <<<HTML_BLOCK
					<tr class='{$table_class_row}'>
						<td>{$short_time}</td>
						<td>{$match->home_team_name} {$match->home_goals}</td>
						<td>{$match->away_team_name} {$match->away_goals}</td>
						<td>{$match->class_name}</td>
						<td>{$match->group_name}</td>
					</tr>
HTML_BLOCK;

	}
	echo <<<HTML_BLOCK
				</tbody>
			</table>
HTML_BLOCK;

	echo <<<HTML_BLOCK
		</div>
	</body>
</html>
HTML_BLOCK;

get_footer();
