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

//    get_header();
    while (have_posts()) {
        the_post();
    }

    $breakQuery = <<<SQL_BLOCK
SELECT
    game_breaks.break_id,
    game_breaks.break_from,
    game_breaks.break_to,
    game_breaks.break_name,
    COALESCE(game_field_breaks.field_id, '*') as field_id
FROM game_breaks
    LEFT JOIN game_field_breaks USING (break_id)
WHERE
    NOT game_breaks.break_hidden
    AND game_breaks.break_to >= NOW()
ORDER BY
    game_breaks.break_to
SQL_BLOCK;
    /** @var \PHPDoc\DbResults2022\Breaks[] $breaks */
    $breaks = $wpdb->get_results($breakQuery, OBJECT_K);

    $fetch_query = <<<SQL_BLOCK
SELECT
	game_matches.match_id,
	game_matches.match_type,
	COALESCE(home_team.team_name, game_matches.home_team_description) AS home_team_name,
	COALESCE(away_team.team_name, game_matches.away_team_description) AS away_team_name,
	game_results.away_goals,
	game_results.done,
	game_results.home_goals,
	game_results.referee_id,
	game_match_time.field_id,
	game_match_time.match_status,
	game_match_time.match_time,
	game_groups.group_name,
	game_classes.class_name
FROM game_matches
	LEFT JOIN game_teams AS home_team ON (home_team.team_id = game_matches.home_team_id)
	LEFT JOIN game_classes USING (class_id)
	LEFT JOIN game_groups USING (group_id)
	LEFT JOIN game_teams AS away_team ON (away_team.team_id = game_matches.away_team_id)
	INNER JOIN game_match_time USING (match_id)
	LEFT JOIN game_results USING (match_id)
WHERE 
    game_matches.match_type <> 'HIDDEN'
    AND (
        game_match_time.match_status IN ('STARTED', 'QUEUE')
        OR game_match_time.match_time > NOW() - INTERVAL 15 MINUTE
    )
ORDER BY
	game_match_time.match_status DESC,
	game_match_time.match_time,
	game_match_time.field_id
SQL_BLOCK;

    /** @var \PHPDoc\DbResults2022\NuPage[] $matches */
    $matches = $wpdb->get_results($fetch_query, OBJECT_K);

    echo <<<HTML_BLOCK
<html lang="sv">
	<head>
		<title>IBN15 - Mål</title>
		<meta http-equiv="refresh" content="5">
		<link href="/d/js/tables.css" type="text/css" rel="stylesheet" />
		<style>
		    #page {
    		    display: flex;
                flex-direction: column;
                align-items: center;
		    }

			h1 span.time
			{
			    display: block;
			    font-size: 12px;
			    text-align: center;
		    }

			h1 span.goals
			{
				font-size: larger;
				display: inline-block;
				padding: 0 10px;
			}
			
			tr.break td {
			    background: yellow;
			}
			
			tr.break td[colspan] {
			    text-align: center;
			    font-weight: bold;
			}
		</style>
	</head>
	<body>
		<div id="page">
HTML_BLOCK;

    $played = [];
    foreach ($matches as $index => $match) {
        if ($match->match_status === 'STARTED') {
            echo <<<HTML_BLOCK
			<h1>
				<span class='time'>{$match->match_time}: </span>
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
        if ($match->match_status === 'PLAYED') {
            $played[] = $match;
            unset($matches[$index]);
        }
    }

    if ($played) {
        echo <<<HTML_BLOCK
			<h2>Spelade matcher, IBN22.se</h1>
			<table>
				<thead>
					<tr>
						<th>Plan</th>
						<th>Tid</th>
						<th>Lag 1</th>
						<th>Mål</th>
						<th>Lag 2</th>
						<!-- th>Klass</th -->
						<th>Grupp</th>
					</tr>
				</thead>
				<tbody>
HTML_BLOCK;

        $odd = false;
        foreach ($played as $match) {
            $short_time = substr($match->match_time, 11, 5);
            $table_class_row = (($odd = !$odd) ? 'odd' : 'even');
            $groupName = $match->match_type === 'GROYP' ? $match->group_name : '';
            echo <<<HTML_BLOCK
					<tr class='{$table_class_row}'>
						<td>{$match->field_id}</td>
						<td>{$short_time}</td>
						<td>{$match->home_team_name}</td>
						<td>{$match->home_goals} - {$match->away_goals}</td>
						<td>{$match->away_team_name}</td>
						<!-- td>{$match->class_name}</td -->
						<td>{$groupName}</td>
					</tr>
HTML_BLOCK;
        }
        echo <<<HTML_BLOCK
				</tbody>
			</table>
HTML_BLOCK;
    }

    echo <<<HTML_BLOCK
			<h2>Kommande matcher, IBN22.se</h1>
			<table>
				<thead>
					<tr>
						<th>Plan</th>
						<th>Tid</th>
						<th>Lag 1</th>
						<th>Lag 2</th>
						<!-- th>Klass</th -->
						<th>Grupp</th>
					</tr>
				</thead>
				<tbody>
HTML_BLOCK;

    $nextBreak = $breaks ? array_shift($breaks) : null;
    $odd = false;
    foreach ($matches as $match) {
        while ($nextBreak && $nextBreak->break_to <= $match->match_time) {
            $short_time = substr($nextBreak->break_from, 11, 5);
            $breakClass = $nextBreak->field_id === '*' ? 'break' : 'field_brak';
            echo <<<HTML_BLOCK
                            <tr class='{$breakClass}'>
                                <td>{$nextBreak->field_id}</td>
                                <td>{$short_time}</td>
                                <td colspan="3">{$nextBreak->break_name}</td>
                            </tr>
            HTML_BLOCK;
            $nextBreak = $breaks ? array_shift($breaks) : null;
        }
        $short_time = substr($match->match_time, 11, 5);
        $table_class_row = (($odd = !$odd) ? 'odd' : 'even');
        $groupName = $match->match_type === 'GROYP' ? $match->group_name : '';
        echo <<<HTML_BLOCK
					<tr class='{$table_class_row}'>
						<td>{$match->field_id}</td>
						<td>{$short_time}</td>
						<td>{$match->home_team_name} {$match->home_goals}</td>
						<td>{$match->away_team_name} {$match->away_goals}</td>
						<!-- td>{$match->class_name}</td -->
						<td>{$groupName}</td>
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
    //get_footer();
})();