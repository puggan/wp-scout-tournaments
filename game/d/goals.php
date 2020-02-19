<?php

	require_once __DIR__ . '/db.php';

	$fetch_query = <<<SQL_BLOCK
SELECT
	home_team.team_name AS home_team_name,
	away_team.team_name AS away_team_name,
	game_results.*,
	game_match_referees.*,
	game_match_time.*,
	game_groups.group_name,
	game_classes.class_name
FROM game_matches
	LEFT JOIN game_teams AS home_team ON (home_team.team_id = game_matches.home_team_id)
	LEFT JOIN game_classes USING (class_id)
	LEFT JOIN game_groups USING (group_id)
	LEFT JOIN game_teams AS away_team ON (away_team.team_id = game_matches.away_team_id)
	LEFT JOIN game_match_time USING (match_id)
	LEFT JOIN game_match_referees USING (match_id)
	LEFT JOIN game_results USING (match_id)
WHERE game_match_time.match_status IN ('STARTED', 'QUEUE')
ORDER BY
	game_match_time.match_status DESC,
	game_match_time.match_time,
	game_match_time.field_id
LIMIT 8
SQL_BLOCK;

	$matches = $database->read($fetch_query);

	echo <<<HTML_BLOCK
<html>
	<head>
		<title>IBN19 - Mål</title>
		<link href="/d/js/tables.css" type="text/css" rel="stylesheet" />
		<meta http-equiv="refresh" content="5" />
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

	foreach($matches as $index => $match)
	{
		if($match['match_status'] == 'STARTED')
		{
			echo <<<HTML_BLOCK
			<h1>
				<span class='field'>P{$match['field_id']}: </span>
				<span class='team'>{$match['home_team_name']}</span>
				<span class='goals'>{$match['home_goals']}</span>
				<span class='goals_sep'> - </span>
				<span class='goals'>{$match['away_goals']}</span>
				<span class='team'>{$match['away_team_name']}</span>
			</h1>
HTML_BLOCK;

			unset($matches[$index]);
		}
	}
	
	$now = date("H:i:s");
	echo <<<HTML_BLOCK
			<h2>Kommande matcher <span style='font-size: smaller;'>(uppdaterad {$now})</span></h2>
			<table>
				<thead>
					<tr>
						<th>Plan</th>
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
		$short_time = substr($match['match_time'], 11, 5);
		$table_class_row = (($odd = !$odd) ? 'odd' : 'even');
	echo <<<HTML_BLOCK
					<tr class='{$table_class_row}'>
						<td>{$match['field_id']}</td>
						<td>{$short_time}</td>
						<td>{$match['home_team_name']} {$match['home_goals']}</td>
						<td>{$match['away_team_name']} {$match['away_goals']}</td>
						<td>{$match['class_name']}</td>
						<td>{$match['group_name']}</td>
					</tr>
HTML_BLOCK;
	
	}
	echo <<<HTML_BLOCK
				</tbody>
			</table>
HTML_BLOCK;

	echo <<<HTML_BLOCK
<!-- pre>
Alla lag går till slutspel:

Rover Kvartsfinal:
P1 01:10 = Rover plats 4 - Rover plats 5

Mix Kvartsfinal:
P2 01:10 = Mix plats 4 - Mix plats 5

Utmanare kvartsfinal
P1 01:20 = Utmanare plats 1 - Utmanare plats 8
P2 01:20 = Utmanare plats 4 - Utmanare plats 5
P1 01:30 = Utmanare plats 3 - Utmanare plats 6
P2 01:30 = Utmanare plats 2 - Utmanare plats 7

Rover Semifinal
P1 01:40 Rover plats 1 - Vinnarna i "P1 01:10"
P2 01:40 Rover plats 2 - Rover plats 3

Mix Semifinal
P1 01:50 Mix plats 1 - Vinnarna i "P2 01:10"
P2 01:50 Mix plats 2 - Mix plats 3

Utmanare Semifinal
P1 02:10 Vinnarna i "P1 01:20" - Vinnarna i "P2 01:20"
P2 02:10 Vinnarna i "P1 01:30" - Vinnarna i "P2 01:30"

Rover Final
P1 02:20 Vinnarna i "P1 01:40" - vinnar i "P2 01:40"

Mix Final
P2 02:20 Vinnarna i "P1 01:50" - vinnar i "P2 01:50"

Utmanar Final
P1 02:30 Vinnarna i "P1 02:10" - Vinnarna i "P2 02:10"

Super Semifinal
P2 02:30 Vinnarna i Rover Finalen - Vinnarna i Mix Finalen

Super Final
Vinnarna i Utmanar Finalen - Vinnarna i Super Semifinalen
</pre -->
</div>
	</body>
</html>
HTML_BLOCK;
