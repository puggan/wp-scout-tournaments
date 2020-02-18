<?php

	use PHPDoc\DbResults\MatchResult;
	use PHPDoc\Models\Referee;

	require_once __DIR__ . '/database.new.php';

	function e($s) {return htmlentities($s, ENT_QUOTES | ENT_HTML5);}

	$database = new database('ibn19', 'ibn19', '4a6rMJYyGQ88fuep');

	$user = [];
	if(!empty($_GET['u']))
	{
		$query = 'SELECT * FROM game_referees WHERE referee_code = ' . $database->quote($_GET['u']);

		/** @var Referee $user */
		$user = $database->object($query);
	}

	// Not logged in?
	if(!$user)
	{
		echo <<<HTML_BLOCK
			<html>
				<head>
					<title>IBN19 - Domare</title>
				</head>
				<body>
					<div style='width: 800px; margin: auto;'>
						<h1>IBN19 - Domare</h1>
						<form>
							<fieldset>
								<label>
									<span>Dommarkod:</span>
									<input name='u' />
								</label>
								<input type='submit' value='Login' />
							</fieldset>
						</form>
					</div>
				</body>
			</html>
		HTML_BLOCK;
		exit();
	}

	$messages = [];

	if(isset($_GET['book']))
	{
		$set_parts = [];
		$set_parts[] = 'referee_id = ' . $user->referee_id;
		$set_parts[] = 'match_id = ' . (int) $_GET['book'];
		$query = 'INSERT INTO game_match_referees SET ' . implode(', ', $set_parts);

		if($database->update($query))
		{
			$messages[] = e("Match {$_GET['book']} bokad");
		}
		else
		{
			$messages[] = e("Misslyckades boka match {$_GET['book']}");
			$messages[] = e($query);
		}
	}

	if(isset($_GET['play']))
	{
		$set_parts = [];
		$set_parts[] = 'referee_id = ' . $user->referee_id;
		$set_parts[] = 'match_id = ' . (int) $_GET['play'];
		$query = 'INSERT INTO game_match_referees SET ' . implode(', ', $set_parts);

		if($database->update($query))
		{
			header("Location: ?u={$user->referee_code}&m=" . (int) $_GET['play']);
			exit();
		}

		$messages[] = e("Misslyckades boka match {$_GET['book']}");
		$messages[] = e($query);
	}

	$messages_html = $messages ? '<ul><li>' . implode('</li><li>', $messages) . '</li></ul>' : '';

	$referee_name = e($user->referee_name);
	echo <<<HTML_BLOCK
<html>
	<head>
		<title>IBN19 - Domare - {$referee_name}</title>

		<script src="/d/js/onload_manager.js"></script>
		<script src="/d/js/misc.js"></script>
		<script src="/d/js/filter_table.js"></script>
		<script src="/d/js/sort_table2.js"></script>
		<script src="/d/js/set_checkboxes_in_form.js"></script>
		<link href="/d/js/tables.css" type="text/css" rel="stylesheet" />
		<style>

			TABLE
			{
				width: 100%;
			}

			THEAD TH.match
			{
				font-size: 40px;
			}

			INPUT.goals
			{
				font-size: 150px;
				height: 200px;
				width: 250px;
				max-width: 250px;
				text-align: center;
			}

			INPUT[type=submit].inc, INPUT[type=submit].dec
			{
				height: 70px;
				width: 70px;
				min-width: 70px;
				font-size: 60px;
				text-align: center;
				valign: center;
			}

			INPUT[type=submit], INPUT[type=submit]
			{
				font-size: 20px;
			}

		</style>
	</head>
	<body>
		<div style='width: 800px; margin: auto;'>
			{$messages_html}
			<h1>IBN19 - Domare - {$user->referee_name}</h1>
HTML_BLOCK;

	if(isset($_GET['m']))
	{
		$match_id = (int) $_GET['m'];
		$fetch_query = <<<SQL_BLOCK
SELECT
	COALESCE(home_team.team_name, home_team_description) AS home_team_name,
	COALESCE(away_team.team_name, away_team_description) AS away_team_name,
	game_results.*,
	game_match_referees.*,
	game_match_time.*
FROM game_matches
	LEFT JOIN game_teams AS home_team ON (home_team.team_id = game_matches.home_team_id)
	LEFT JOIN game_teams AS away_team ON (away_team.team_id = game_matches.away_team_id)
	LEFT JOIN game_match_time USING (match_id)
	LEFT JOIN game_match_referees USING (match_id)
	LEFT JOIN game_results USING (match_id)
WHERE game_matches.match_id = {$match_id}
SQL_BLOCK;

		/** @var MatchResult $match */
		$match = $database->object($fetch_query);

		$match->home_goals = (int) $match->home_goals;
		$match->away_goals = (int) $match->away_goals;

		$url = e("?u={$user->referee_code}&m={$_GET['m']}");

		if($_POST)
		{
			$update_set_parts = [];
			$set_parts = [];
			$set_parts['match_id'] = 'match_id = ' . $match_id;
			$set_parts['referee_id'] = 'referee_id = ' . $user->referee_id;
			$set_parts['done'] = 'done = 0';
			$set_parts['home_goals'] = 'home_goals = 0';
			$set_parts['away_goals'] = 'away_goals = 0';
			$set_parts_time = [];

			if(isset($_POST['update']))
			{
				$update_set_parts['home_goals'] = 'home_goals = ' . (int) $_POST['home_goals'];
				$update_set_parts['away_goals'] = 'away_goals = ' . (int) $_POST['away_goals'];
				$set_parts = $update_set_parts + $set_parts;
			}
			else if(isset($_POST['end']))
			{
				$update_set_parts['done'] = 'done = 1';
				$update_set_parts['home_goals'] = 'home_goals = ' . (int) $_POST['home_goals'];
				$update_set_parts['away_goals'] = 'away_goals = ' . (int) $_POST['away_goals'];
				$set_parts = $update_set_parts + $set_parts;
				$set_parts_time['match_status'] = "match_status = 'PLAYED'";
			}
			else if(isset($_POST['start']))
			{
				$update_set_parts = $set_parts;
				$set_parts_time['match_status'] = "match_status = 'STARTED'";
			}
			else if(isset($_POST['home_inc']))
			{
				$update_set_parts['home_goals'] = 'home_goals = home_goals + 1';
				$set_parts['home_goals'] = 'home_goals = 1';
			}
			else if(isset($_POST['away_inc']))
			{
				$update_set_parts['away_goals'] = 'away_goals = away_goals + 1';
				$set_parts['away_goals'] = 'away_goals = 1';
			}
			else if(isset($_POST['home_dec']))
			{
				$update_set_parts['home_goals'] = 'home_goals = GREATEST(0, home_goals - 1)';
			}
			else if(isset($_POST['away_dec']))
			{
				$update_set_parts['home_goals'] = 'away_goals = GREATEST(0, away_goals - 1)';
			}

			if($set_parts_time)
			{
				$set_parts_sql = implode(', ', $set_parts_time);
				$update_query = "UPDATE game_match_time SET {$set_parts_sql} WHERE match_id = ". $match_id;
				$database->update($update_query);
			}
			
			if($update_set_parts)
			{
				$set_parts_sql = implode(', ', $set_parts);
				$update_set_parts_sql = implode(', ', $update_set_parts);
				$update_query = "INSERT INTO game_results SET {$set_parts_sql} ON DUPLICATE KEY UPDATE {$update_set_parts_sql}";

				$database->update($update_query);
				if(isset($_POST['end']))
				{
					header('Location: ?u=' . $user->referee_code);
					exit();
				}

				$match = $database->object($fetch_query);
			}
		}

		if(!empty($_GET['swap']))
		{
			$side_1 = 'home';
			$side_2 = 'away';
			$swap_url = $url;
			$form_url = $url . '&swap=1';
		}
		else
		{
			$side_1 = 'away';
			$side_2 = 'home';
			$form_url = $url;
			$swap_url = $url . '&swap=1';
		}
		
		switch($match->match_status)
		{
			case 'STARTED':
			{
				echo <<<HTML_BLOCK
			<h2>Match {$match->match_id} - {$match->{"{$side_1}_team_name"}} vs {$match->{"{$side_2}_team_name"}}</h2>
			<form action="{$form_url}" method='post'>
				<table>
					<thead class='match'>
						<tr>
							<th colspan='2' style='font-size: 2em;'>{$match->{"{$side_1}_team_name"}}</th>
							<th> vs </th>
							<th colspan='2' style='font-size: 2em;'>{$match->{"{$side_2}_team_name"}}</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td rowspan='2'>
								<input class='goals' id='{$side_1}_goals' name='{$side_1}_goals' value='{$match->{"{$side_1}_goals"}}' />
							</td>
							<td>
								<input type='submit' name='{$side_1}_inc' class='inc' value='+' />
							</td>
							<td rowspan='2'>
								vs
							</td>
							<td rowspan='2'>
								<input class='goals' id='{$side_2}_goals' name='{$side_2}_goals' value='{$match->{"{$side_2}_goals"}}' />
							</td>
							<td>
								<input type='submit' name='{$side_2}_inc' class='inc' value='+' />
							</td>
						</tr>
						<tr>
							<td>
								<input type='submit' name='{$side_1}_dec' class='dec' value='-' />
							</td>
							<td>
								<input type='submit' name='{$side_2}_dec' class='dec' value='-' />
							</td>
						</tr>
					</tbody>
				</table>
				<input type='submit' name='reload' value='Ladda' />
				<input type='submit' name='update' value='Spara' />
				<input type='submit' name='end' value='Avsluta' />
				<input type='button' value='Byt sida' onclick="location.href='{$swap_url}'" />
			</form>
HTML_BLOCK;
				break;
			}
			case 'PLAYED':
			{
				echo <<<HTML_BLOCK
			<h2>Match {$match->match_id} - {$match->{$side_1."_team_name"}} vs {$match->{"{$side_2}_team_name"}}</h2>
			<p>Matchen mellan {$match->{"{$side_1}_team_name"}} och {$match->{"{$side_2}_team_name"}} slutade {$match->{"{$side_1}_goals"}} - {$match->{"{$side_2}_goals"}}</p>
HTML_BLOCK;
				break;
			}
			case 'QUEUE':
			default:
			{
				echo <<<HTML_BLOCK
			<h2>Match {$match->match_id} - {$match->{$side_1."_team_name"}} vs {$match->{"{$side_2}_team_name"}}</h2>
			<p>Matchen mellan {$match->{"{$side_1}_team_name"}} och {$match->{"{$side_2}_team_name"}} ska börja {$match->match_time}</p>
			<form action="{$form_url}" method='post'>
				<input type='submit' name='start' value='Starta matchen' />
			</form>
HTML_BLOCK;
				break;
			}
		}
	}
	else
	{
		$query = <<<SQL_BLOCK
SELECT 
	COALESCE(home_team.team_name, game_matches.home_team_description) AS home_team_name, 
	COALESCE(away_team.team_name, game_matches.away_team_description) AS away_team_name, 
	game_match_time.*, 
	game_match_referees.* 
FROM game_match_referees 
	INNER JOIN game_matches USING (match_id) 
	LEFT JOIN game_teams AS home_team ON (home_team.team_id = game_matches.home_team_id) 
	LEFT JOIN game_teams AS away_team ON (away_team.team_id = game_matches.away_team_id) 
	LEFT JOIN game_match_time USING (match_id) 
	LEFT JOIN game_results USING (match_id) 
WHERE 
	(game_results.done IS NULL OR game_results.done = 0)
	AND
	game_match_referees.referee_id = {$user->referee_id}
ORDER BY game_match_time.match_time, game_match_time.field_id
SQL_BLOCK;

		$list = $database->objects($query, 'match_id');
		
		echo <<<HTML_BLOCK
			<h2>Mina matcher</h2>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th>Plan</th>
						<th>Tid</th>
						<th>Hemmalag</th>
						<th>Bortalag</th>
					</tr>
				</thead>
				<tbody>
HTML_BLOCK;

		$odd = FALSE;
		foreach($list as $match_id => $match)
		{
			$url = e("?u={$user->referee_code}&m={$match_id}");
			$row_class = (($odd = !$odd) ? 'odd' : 'even');

			echo <<<HTML_BLOCK
				<tr class='{$row_class}'>
					<td><a href='{$url}'>Match {$match->match_id}</a></td>
					<td>{$match->field_id}</td>
					<td>{$match->match_time}</td>
					<td>{$match->home_team_name}</td>
					<td>{$match->away_team_name}</td>
				</tr>
HTML_BLOCK;
		}

		echo <<<HTML_BLOCK
				<tbody>
			</table>
HTML_BLOCK;
// 		echo "<pre>"; print_r($list); echo "\n{$query}</pre>";

		$query =  <<<SQL_BLOCK
SELECT 
	COALESCE(home_team.team_name, game_matches.home_team_description) AS home_team_name, 
	COALESCE(away_team.team_name, game_matches.away_team_description) AS away_team_name, 
	game_match_time.*, 
	game_matches.* 
FROM game_matches 
	LEFT JOIN game_teams AS home_team ON (home_team.team_id = game_matches.home_team_id) 
	LEFT JOIN game_teams AS away_team ON (away_team.team_id = game_matches.away_team_id) 
	LEFT JOIN game_match_referees USING (match_id) 
	LEFT JOIN game_match_time USING (match_id) 
WHERE game_match_referees.referee_id IS NULL
ORDER BY game_match_time.match_time IS NULL, game_match_time.match_time, game_matches.match_id
SQL_BLOCK;

		$list = $database->read($query, 'match_id');
		
		if($list)
		{
			echo <<<HTML_BLOCK
			<h2>Obokade Matcher</h2>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th>Plan</th>
						<th>Tid</th>
						<th>Hemmalag</th>
						<th>Bortalag</th>
						<th>Funktioner</th>
					</tr>
				</thead>
				<tbody>
HTML_BLOCK;

			$odd = FALSE;
			foreach($list as $match_id => $match)
			{
				$match = (object) $match;

				$url = e("?u={$user->referee_code}&book={$match_id}");
				$url2 = e("?u={$user->referee_code}&play={$match_id}");
				$row_class = (($odd = !$odd) ? 'odd' : 'even');
				$short_time = substr($match->match_time, 11, 5);

				echo <<<HTML_BLOCK
				<tr class='{$row_class}'>
					<td>{$match->match_id}</td>
					<td>{$match->field_id}</td>
					<td title='{$match->match_time}'>{$short_time}</td>
					<td>{$match->home_team_name}</td>
					<td>{$match->away_team_name}</td>
					<td><a href='{$url2}'>Döm</a>, <a href='{$url}'>Boka</a></td>
				</tr>
HTML_BLOCK;
			}

			echo <<<HTML_BLOCK
				</tbody>
			</table>
HTML_BLOCK;
		}

		$query = <<<SQL_BLOCK
SELECT 
	COALESCE(home_team.team_name, game_matches.home_team_description) AS home_team_name, 
	COALESCE(away_team.team_name, game_matches.away_team_description) AS away_team_name, 
	game_match_time.*, 
	game_match_referees.*,
	game_results.*
FROM game_match_referees 
	INNER JOIN game_matches USING (match_id) 
	LEFT JOIN game_teams AS home_team ON (home_team.team_id = game_matches.home_team_id) 
	LEFT JOIN game_teams AS away_team ON (away_team.team_id = game_matches.away_team_id) 
	LEFT JOIN game_match_time USING (match_id) 
	INNER JOIN game_results USING (match_id) 
WHERE 
	game_results.done = 1
	AND
	game_match_referees.referee_id = {$user->referee_id}
SQL_BLOCK;

		/** @var MatchResult[] $list */
		$list = $database->objects($query, 'match_id');

		if($list)
		{
			echo <<<HTML_BLOCK
			<h2>Mina dömda Matcher</h2>
			<table>
				<thead>
					<tr>
						<th>#</th>
						<th>Tid</th>
						<th colspan='2'>Hemmalag</th>
						<th colspan='2'>Bortalag</th>
					</tr>
				</thead>
				<tbody>
HTML_BLOCK;

			$odd = FALSE;
			foreach($list as $match_id => $match)
			{
				$url = e("?u={$user->referee_code}&book={$match_id}");
				$url2 = e("?u={$user->referee_code}&play={$match_id}");
				$row_class = (($odd = !$odd) ? 'odd' : 'even');

				echo <<<HTML_BLOCK
				<tr class='{$row_class}'>
					<td>{$match->match_id}</td>
					<td>{$match->match_time}</td>
					<td>{$match->home_team_name}</td>
					<td>{$match->home_goals}</td>
					<td>{$match->away_team_name}</td>
					<td>{$match->away_goals}</td>
				</tr>
HTML_BLOCK;
			}

			echo <<<HTML_BLOCK
				</tbody>
			</table>
HTML_BLOCK;
		}

	}
	echo <<<HTML_BLOCK
		</div>
	</body>
</html>
HTML_BLOCK;
