<?php

//	die("Disabled");

	define('LOW_MATCH_COUNT_WEIGHT', 1);
	define('HIGH_MATCH_COUNT_WEIGHT', 10 * LOW_MATCH_COUNT_WEIGHT);
	define('HIGH_LAST_MATCH_WEIGHT', 100 * -HIGH_MATCH_COUNT_WEIGHT);
	define('LOW_LAST_MATCH_WEIGHT', 100 * HIGH_LAST_MATCH_WEIGHT);

	require_once __DIR__ . '/database.new.php';

	$database = new database('ibn19', 'ibn19', '4a6rMJYyGQ88fuep');

	$query = "SELECT * FROM game_fields";
	$fields = $database->read($query, 'field_id', 'field_name');

	$query = "SELECT * FROM game_breaks ORDER BY break_to";
	$breaks = $database->read($query);
	
	$next_match_time = $breaks[0]['break_to'];
	$next_match_time = "2015-02-21 22:20";

	$query = "SELECT * FROM game_matches LEFT JOIN game_match_time USING (match_id) WHERE match_type = 'GROUP'";
	
	$db_matches = $database->read($query, 'match_id');
	
	$teams = array_column($db_matches, 'home_team_id', 'home_team_id') + array_column($db_matches, 'away_team_id', 'away_team_id');
	
	$last_match = array_fill_keys($teams, -100);
	$matches_left = array_fill_keys($teams, 0);
	
	foreach($db_matches as $match)
	{
		$matches_left[$match['home_team_id']]++;
		$matches_left[$match['away_team_id']]++;
		
		if($match['match_time'])
		{
			$match_end_time = date("Y-m-d H:i:s", strtotime("{$match['match_time']} +10 minutes"));
			if($match['match_status'] AND $match['match_status'] != 'QUEUE')
			{
				$breaks[] = array('break_name' => "Match {$match['match_id']}", 'break_from' => $match['match_time'], 'break_to' => $match_end_time);
			}
			else
			{
				$next_match_time = max($match_end_time, $next_match_time);
			}
		}
	}
	
	$match_order = array();
	$todo_matches = $db_matches;
	
	while($todo_matches)
	{
		foreach($todo_matches as $row_id => $match)
		{
			$match['low_last_match'] =  min($last_match[$match['home_team_id']], $last_match[$match['away_team_id']]);
			$match['high_last_match'] = max($last_match[$match['home_team_id']], $last_match[$match['away_team_id']]);
			$match['low_match_count'] = min($matches_left[$match['home_team_id']], $matches_left[$match['away_team_id']]);
			$match['high_match_count'] = max($matches_left[$match['home_team_id']], $matches_left[$match['away_team_id']]);

			$match['points'] = 
				$match['low_last_match'] * LOW_LAST_MATCH_WEIGHT + 
				$match['high_last_match'] * HIGH_LAST_MATCH_WEIGHT + 
				$match['low_match_count'] * LOW_MATCH_COUNT_WEIGHT + 
				$match['high_match_count'] * HIGH_MATCH_COUNT_WEIGHT;
				
			$todo_matches[$row_id] = $match;
		}
		
		usort($todo_matches, function ($a, $b) {return $b['points'] - $a['points'];});
// 		echo "<pre>TODO: " . print_r($todo_matches, TRUE) . "</pre>";
		
		$next_match = array_shift($todo_matches);
// 		echo "<pre>Match: " . print_r($next_match, TRUE) . "</pre><hr />";
		
		if(!$match['match_status'] OR $match['match_status'] != 'QUEUE')
		{
			$match_order[] = $next_match;
		}
		
		$matches_left[$next_match['home_team_id']]--;
		$matches_left[$next_match['away_team_id']]--;
		$last_match[$next_match['home_team_id']] = count($match_order);
		$last_match[$next_match['away_team_id']] = count($match_order);
	}
	
	usort($breaks, function ($a, $b) {return strcmp($a['break_to'], $b['break_to']);});
	
// 	echo "<pre>" . print_r($match_order, TRUE) . "</pre>";
	
	while($match_order)
	{
		$next_match_end_time = date("Y-m-d H:i:s", strtotime("{$next_match_time} +10 minutes"));
		foreach($breaks as $break)
		{
			if($break['break_from'] < $next_match_end_time AND $next_match_time < $break['break_to'])
			{
				$next_match_time = $break['break_to'];
				$next_match_end_time = date("Y-m-d H:i:s", strtotime("{$next_match_time} +10 minutes"));
			}
		}
	
		foreach($fields as $field_id => $field)
		{
			$next_match = array_pop($match_order);

			if($next_match)
			{
				$set_parts = array();
				$set_parts['match_id'] = "match_id = " . (int) $next_match['match_id'];
				$set_parts['field_id'] = "field_id = " . (int) $field_id;
				$set_parts['match_time'] = "match_time = " . $database->quote($next_match_time);
				$set_parts['match_status'] = "match_status = 'QUEUE'";

				$query = "INSERT INTO game_match_time SET " . implode(", ", $set_parts);
// 				$query = "REPLACE INTO game_match_time SET " . implode(", ", $set_parts);
				$database->update($query);
			}
		}

		$next_match_time = $next_match_end_time;

		echo "<p>" . htmlentities($query) . "</p>";
	}
	
