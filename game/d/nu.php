<?php

	date_default_timezone_set("Europe/Stockholm");

	require_once __DIR__ . '/database.new.php';

	$database = new database('ibn19', 'ibn19', '4a6rMJYyGQ88fuep');

	$now = date("Y-m-d H:i:s");

	$next_break = null;
	$breaks = $database->objects("SELECT * FROM game_breaks WHERE break_to > NOW() ORDER BY break_from");
	if($breaks) $next_break = array_shift($breaks);

	$fetch_query = <<<SQL_BLOCK
SELECT
	match_id,
	COALESCE(home_team.team_name, home_team_description) AS home_team_name,
	COALESCE(away_team.team_name, away_team_description) AS away_team_name,
	game_results.*,
	game_match_referees.*,
	game_match_time.*,
	game_groups.group_name,
	game_classes.class_name
FROM game_matches
	INNER JOIN game_match_time USING (match_id)
	LEFT JOIN game_teams AS home_team ON (home_team.team_id = game_matches.home_team_id)
	LEFT JOIN game_classes USING (class_id)
	LEFT JOIN game_groups USING (group_id)
	LEFT JOIN game_teams AS away_team ON (away_team.team_id = game_matches.away_team_id)
	LEFT JOIN game_match_referees USING (match_id)
	LEFT JOIN game_results USING (match_id)
GROUP BY game_matches.match_id
ORDER BY
	IF(game_match_time.match_status = 'PLAYED', game_match_time.match_time, NULL) DESC,
	game_match_time.match_time,
    game_match_time.field_id
SQL_BLOCK;

	$matches = $database->read($fetch_query);

	echo <<<HTML_BLOCK
<html>
	<head>
		<title>IBN19 - Mål</title>
		<link href="http://www.fridabraxell.se/tables.css" type="text/css" rel="stylesheet" />
		<style>
			H1 SPAN.goals
			{
				font-size: larger;
				display: inline-block;
				padding: 10px;
			}
			TR.odd
			{
			    background: rgb(240, 240, 255);
			}
		</style>
		<meta http-equiv="refresh" content="5" />
	</head>
	<body>
		<div style='width: 800px; margin: auto;'>
HTML_BLOCK;

	$index = 0;
    $played = array();

	foreach($matches as $index => $match)
	{
	    $matches[$index] = (object) $match;
    }
	foreach($matches as $index => $match)
	{
		if($matches[$index]->match_status == 'STARTED')
		{
			echo <<<HTML_BLOCK
			<h1 style="margin: 0;">
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
	foreach($matches as $index => $match)
	{
		if($matches[$index]->match_status == 'PLAYED')
        {
            if(empty($played[$match->field_id]) AND $matches[$index]->match_time < $now)
            {
                $played[$match->field_id] = TRUE;
        		$short_time = substr($match->match_time, 11, 5);
    			echo <<<HTML_BLOCK
			<h3 style="margin: 0;">
				<span class='side'>{$match->field_id}: </span>
				<span class='team'>{$match->home_team_name}</span>
				<span class='goals'>{$match->home_goals}</span>
				<span class='goals_sep'> - </span>
				<span class='goals'>{$match->away_goals}</span>
				<span class='team'>{$match->away_team_name}</span>
				<span class='ttime'>({$short_time})</span>
			</h3>
HTML_BLOCK;
            }
			unset($matches[$index]);
        }
	}

	echo <<<HTML_BLOCK
			<h2>Kommande matcher</h1>
			<table>
				<thead>
					<tr>
						<th>Tid</th>
						<th>Plan</th>
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
		while($next_break AND $next_break->break_from < $match->match_time)
		{
			echo '<tr>';
			echo '<td>' . htmlentities(substr($next_break->break_from, 11, 5)) . '</td>';
			echo '<td colspan="6" style="text-align: center; padding: 5px; font-weight: bold;">' . htmlentities($next_break->break_name) . '</td>';
			echo '</tr>';

			$next_break = null;
			if($breaks) $next_break = array_shift($breaks);
		}
		$short_time = substr($match->match_time, 11, 5);
		$table_class_row = (($odd = !$odd) ? 'odd' : 'even');
        echo <<<HTML_BLOCK
					<tr class='{$table_class_row}'>
						<td>{$short_time}</td>
						<td>{$match->field_id}</td>
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
		<p style="text-align: center;">Se resultat och schema i mobilen på ibn19.se</p>
		<p style="text-align: center;">Wifi: Linksys</p>
	</body>
</html>
HTML_BLOCK;
