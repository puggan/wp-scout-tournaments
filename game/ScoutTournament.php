<?php /** @noinspection SqlNoDataSourceInspection */

	define('GAME_FROM_ID', 1);
	define('GAME_CLASS_FIELD_ID', 14);
	define('GAME_TEAM_FIELD_ID', 16);

	add_action('admin_menu', 'ScoutTournament::init_add_game_menu');

	class ScoutTournament
	{
		public static function html_encode($s) : string
		{
			return htmlentities($s, ENT_QUOTES | ENT_XHTML);
		}

		public static function init_add_game_menu() : void
		{
			$icon_url = get_template_directory_uri() . '/game/ball.png';
			$menu_slug = 'game';
			$capability = 'edit_pages';

			add_menu_page('Administrera Spel', 'Spel', $capability, $menu_slug, 'ScoutTournament::game_page', $icon_url, 58);

			add_submenu_page($menu_slug, 'Deltagande Lag', 'Lag', $capability, $menu_slug . '_teams', 'ScoutTournament::game_team_page');
			add_submenu_page($menu_slug, 'Grupper', 'Grupp', $capability, $menu_slug . '_groups', 'ScoutTournament::game_group_page');
			add_submenu_page($menu_slug, 'Matcher', 'Match', $capability, $menu_slug . '_match', 'ScoutTournament::game_match_page');
			add_submenu_page($menu_slug, 'Edit Match', 'Edit Match', $capability, $menu_slug . '_edit_match', 'ScoutTournament::game_edit_match_page');

			add_submenu_page($menu_slug, 'Dommare', 'Dommare', $capability, $menu_slug . '_referees', 'ScoutTournament::game_referees_page');
		}

		public static function game_page() : void
		{
			global $wpdb;

			echo '<style>TABLE.puggan_table TD, TABLE.puggan_table TH {padding: 3px 8px; border: solid black 1px;} TABLE.puggan_table {border: solid gray 2px;}</style>';
			echo '<h2>Klasser</h2>';
			echo "<table class='puggan_table'>";
			echo '<thead>';
			echo '<tr><th>Klass</th><th>Antal Lag</th></tr>';
			echo '</thead>';
			echo '<tbody>';

			$classes = $wpdb->get_results('SELECT game_classes.*, COUNT(game_teams.team_id) AS teams FROM game_classes LEFT JOIN game_teams USING (class_id) GROUP BY class_id', OBJECT_K);
			if(!$classes)
			{
				echo "<tr><td colspan='2'>Inga klasser</td></tr>";
			}
			else
			{
				foreach($classes as $class)
				{
					echo '<tr>';
					echo "<td>{$class->class_name}</td>";
					echo "<td>{$class->teams}</td>";
					echo '</tr>';
				}
			}

			echo '</tbody>';
			echo '</table>';

			echo '<h2>Lag</h2>';
			echo "<table class='puggan_table'>";
			echo '<thead>';
			echo '<tr><th>Lagnamn</th><th>Klass</th><th>Grupp</th></tr>';
			echo '</thead>';
			echo '<tbody>';

			$teams = $wpdb->get_results(
				'SELECT game_teams.team_id, game_teams.team_name, game_classes.class_name, game_groups.group_name FROM game_teams LEFT JOIN game_classes USING (class_id) LEFT JOIN game_groups USING (group_id) ORDER BY game_teams.class_id, game_teams.group_id, game_teams.team_name',
				OBJECT_K
			);
			if(!$teams)
			{
				echo "<tr><td colspan='3'>Inga lag</td></tr>";
			}
			else
			{
				foreach($teams as $team)
				{
					echo '<tr>';
					echo "<td><a href='?page=game_teams&amp;team_id={$team->team_id}'>{$team->team_name}</a></td>";
					echo "<td>{$team->class_name}</td>";
					echo "<td>{$team->group_name}</td>";
					echo '</tr>';
				}
			}

			echo '</tbody>';
			echo '</table>';

		}

		public static function game_team_page() : void
		{
			global $wpdb;

			if(isset($_POST['bulk_button']))
			{
				foreach($_POST['bulk'] as $row)
				{
					if(isset($row['action']) && $row['action'] === 'add')
					{
						if(self::game_add_team($row))
						{
							echo "<p class='notice'>Lag " . self::html_encode($row['team_name']) . ' registrerad</p>';
						}
						else
						{
							echo "<p class='warning'>Misslyckades registrera Lag " . self::html_encode($row['team_name']) . '</p>';
						}
					}
				}
			}

			if(!empty($_POST['add']['action']))
			{
				if(self::game_add_team($_POST['add']))
				{
					echo "<p class='notice'>Lag " . self::html_encode($_POST['add']['team_name']) . ' registrerad</p>';
				}
				else
				{
					echo "<p class='warning'>Misslyckades registrera Lag " . self::html_encode($_POST['add']['team_name']) . '</p>';
				}
			}

			echo '<style>TABLE.puggan_table TD, TABLE.puggan_table TH {padding: 3px 8px; border: solid black 1px;} TABLE.puggan_table {border: solid gray 2px;}</style>';

			$classes = $wpdb->get_results('SELECT game_classes.* FROM game_classes', OBJECT_K);

			if(!empty($_GET['team_id']))
			{
				$team = $wpdb->get_row('SELECT game_teams.* FROM game_teams WHERE team_id = ' . ((int) $_GET['team_id']));

				var_dump($team);
			}
			else
			{
				$teams = $wpdb->get_results(
					'SELECT game_teams.team_id, game_teams.team_name, game_classes.class_name, game_groups.group_name FROM game_teams LEFT JOIN game_classes USING (class_id) LEFT JOIN game_groups USING (group_id) ORDER BY game_teams.class_id, game_teams.group_id, game_teams.team_name',
					OBJECT_K
				);

				$new_teams = $wpdb->get_results(
					'SELECT `value` AS team_name, COUNT(*) AS team_members, MAX(lead_id) AS lead_id FROM `wp_rg_lead_detail` WHERE `form_id` = ' . GAME_FROM_ID . ' AND `field_number` = ' . GAME_TEAM_FIELD_ID . ' GROUP BY team_name ORDER BY team_members DESC, team_name',
					OBJECT_K
				);

				$team_names = [];

				foreach($teams as $team)
				{
					$team_names[$team->team_name] = $team->team_id;
				}

				$missing_teams = [];
				$missing_team_lead_ids = [];

				foreach($new_teams as $team)
				{
					if(!isset($team_names[$team->team_name]))
					{
						$missing_teams[$team->lead_id] = $team;
						$missing_team_lead_ids[$team->lead_id] = (int) $team->lead_id;
					}
				}

				if($missing_teams)
				{
					$team_classes_rows = $wpdb->get_results(
						'SELECT lead_id, `value` AS class_name FROM `wp_rg_lead_detail` WHERE `form_id` = ' . GAME_FROM_ID . ' AND `field_number` = ' . GAME_CLASS_FIELD_ID . ' AND lead_id IN (' . implode(
							', ',
							$missing_team_lead_ids
						) . ')',
						OBJECT_K
					);

					foreach($missing_teams as $lead_id => $team)
					{
						$missing_teams[$lead_id]->class_id = (int) $team_classes_rows[$team->lead_id]->class_name;
					}

					$js = <<<JS_BLOCK
var e = this;
var t = 0;
for(t = 0; t < 20; t++)
	if(e.tagName == 'TABLE')
		break;
	else e = e.parentNode;
if(e.tagName == 'TABLE')
{
	var ee = e.getElementsByTagName('INPUT'); 
	for(t = 0; t < ee.length; t++) 
		if(ee[t].type == 'checkbox') 
			if(ee[t] != this) 
				ee[t].checked = this.checked;
}
JS_BLOCK;
					$js_mini = preg_replace("/[ \t\r\n]+/", ' ', $js);

					echo '<h2>Anmälda, oregistrerade lag</h2>';
					echo "<form action='#' method='post'>";
					echo "<table class='puggan_table'>";
					echo '<thead>';
					echo '<tr>';
					echo "<th><input type='checkbox' onchange=\"{$js_mini}\" /></th>";
					echo '<th>Lagnamn</th>';
					echo '<th>Klass</th>';
					echo '</tr>';
					echo '</thead>';
					echo '<tbody>';

					foreach($missing_teams as $lead_id => $team)
					{
						echo '<tr>';
						echo "<td><input type='checkbox' name='bulk[{$lead_id}][action]' value='add' /></td>";
						echo "<td><input type='text' readonly='readonly' name='bulk[{$lead_id}][team_name]' value='" . self::html_encode($team->team_name) . "' /></td>";
						echo "<td><input type='hidden' name='bulk[{$lead_id}][class_id]' value='{$team->class_id}' />" . self::html_encode($classes[$team->class_id]->class_name) . '</td>';
						echo '</tr>';
					}

					echo '</tbody>';
					echo '</table>';
					echo "<input type='submit' name='bulk_button' value='Lägg till markerade lag' />";
					echo '</form>';
				}
				else
				{
					echo "<p class='notice'>Alla anmälda lag är registrerade</p>";
				}

				echo "<form action='#' method='post'>";
				echo '<fieldset>';
				echo '<legend>';
				echo '<h2>Registrera Lag</h2>';
				echo '</legend>';
				echo "<lable><span style='display: inline-block; width: 120px;'>Lagnamn:</span><input name='add[team_name]' style='width: 200px;' /></label><br />";
				echo "<lable><span style='display: inline-block; width: 120px;'>Klass:</span><select name='add[class_id]' style='width: 200px;' >";
				echo "<option value=''>V&auml;lj klass</option>";
				foreach($classes as $class_id => $class)
				{
					echo "<option value='{$class_id}'>" . self::html_encode($class->class_name) . '</option>';
				}
				echo '</select></label><br />';
				echo "<lable><span style='display: inline-block; width: 120px;'></span><input type='submit' name='add[action]' style='width: 200px;' value='Registrera lag' >";
				echo '</fieldset>';
				echo '</form>';
			}
		}

		public static function game_add_team($data)
		{
			global $wpdb;

			if(empty($data['team_name']))
			{
				trigger_error("self::game_add_team() require 'team_name'");
				return FALSE;
			}

			if(empty($data['class_id']))
			{
				trigger_error("self::game_add_team() require 'class_id'");
				return FALSE;
			}

			// FIXME: buld add, double encodes ' => in database \'
			return $wpdb->insert('game_teams', ['team_name' => $data['team_name'], 'class_id' => $data['class_id']], ['%s', '%d']);
		}

		public static function game_group_page() : void
		{
			global $wpdb;

			if(!empty($_POST['add']['action']))
			{
				if(self::game_add_group($_POST['add']))
				{
					echo "<p class='notice'>Grupp " . self::html_encode($_POST['add']['group_name']) . ' registrerad</p>';
				}
				else
				{
					echo "<p class='warning'>Misslyckades registrera Grupp " . self::html_encode($_POST['add']['group_name']) . '</p>';
				}
			}

			$groups = $wpdb->get_results(
				'SELECT game_groups.*, game_classes.class_name, COUNT(game_teams.team_id) AS team_count, GROUP_CONCAT(game_teams.team_name SEPARATOR ", ") AS teams FROM game_groups LEFT JOIN game_classes USING (class_id) LEFT JOIN game_teams USING (class_id, group_id) GROUP BY group_id ORDER BY game_groups.class_id, game_groups.group_name',
				OBJECT_K
			);
			$classes = $wpdb->get_results('SELECT game_classes.* FROM game_classes', OBJECT_K);
			$teams = $wpdb->get_results(
				'SELECT game_teams.team_id, game_teams.team_name, game_classes.class_name, game_groups.group_name FROM game_teams LEFT JOIN game_classes USING (class_id) LEFT JOIN game_groups USING (group_id) ORDER BY IF(game_teams.group_id IS NULL, 0, 1), game_teams.class_id, game_teams.group_id, game_teams.team_name',
				OBJECT_K
			);

			if(!empty($_POST['connect']['action']))
			{
				if(self::set_team_group($_POST['connect']['team_id'], $_POST['connect']['group_id']))
				{
					echo "<p class='notice'>Kopplade '" . self::html_encode($teams[$_POST['connect']['team_id']]->group_name) . "' till grupp '" . self::html_encode(
							$groups[$_POST['connect']['group_id']]->group_name
						) . "'</p>";

					// reload teams and groups
					$groups = $wpdb->get_results(
						'SELECT game_groups.*, game_classes.class_name, COUNT(game_teams.team_id) AS team_count, GROUP_CONCAT(game_teams.team_name SEPARATOR ", ") AS teams FROM game_groups LEFT JOIN game_classes USING (class_id) LEFT JOIN game_teams USING (class_id, group_id) GROUP BY group_id ORDER BY game_groups.class_id, game_groups.group_name',
						OBJECT_K
					);
					$teams = $wpdb->get_results(
						'SELECT game_teams.team_id, game_teams.team_name, game_classes.class_name, game_groups.group_name FROM game_teams LEFT JOIN game_classes USING (class_id) LEFT JOIN game_groups USING (group_id) ORDER BY IF(game_teams.group_id IS NULL, 0, 1), game_teams.class_id, game_teams.group_id, game_teams.team_name',
						OBJECT_K
					);
				}
				else
				{
					echo "<p class='warning'>Misslyckades koppla '" . self::html_encode($teams[$_POST['connect']['team_id']]->group_name) . "' till grupp '" . self::html_encode(
							$groups[$_POST['connect']['group_id']]->group_name
						) . "'</p>";
				}
			}

			echo '<style>TABLE.puggan_table TD, TABLE.puggan_table TH {padding: 3px 8px; border: solid black 1px;} TABLE.puggan_table {border: solid gray 2px;}</style>';

			// 		echo "<pre>"; print_r($classes); print_r($groups); echo "</pre>";

			echo '<h2>Grupper</h2>';
			echo "<table class='puggan_table'>";
			echo '<thead>';
			echo '<tr>';
			echo '<th>Grupp</th>';
			echo '<th>Class</th>';
			echo "<th colspan='2'>Lag</th>";
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			if($groups)
			{
				foreach($groups as $group)
				{
					echo '<tr>';
					echo "<td>{$group->group_name}</td>";
					echo "<td>{$group->class_name}</td>";
					echo "<td>{$group->team_count}</td>";
					echo "<td>{$group->teams}</td>";
					echo '</tr>';
				}
			}
			else
			{
				echo "<tr><td colspan='4'>Inga grupper registrerade</td></tr>";
			}
			echo '</tbody>';
			echo '</table>';

			echo "<form action='#' method='post'>";
			echo '<fieldset>';
			echo '<legend>';
			echo '<h2>Registrera Grupp</h2>';
			echo '</legend>';
			echo "<lable><span style='display: inline-block; width: 120px;'>Gruppnamn:</span><input name='add[group_name]' style='width: 200px;' /></label><br />";
			echo "<lable><span style='display: inline-block; width: 120px;'>Klass:</span><select name='add[class_id]' style='width: 200px;' >";
			echo "<option value=''>V&auml;lj klass</option>";
			foreach($classes as $class_id => $class)
			{
				echo "<option value='{$class_id}'>" . self::html_encode($class->class_name) . '</option>';
			}
			echo '</select></label><br />';
			echo "<lable><span style='display: inline-block; width: 120px;'></span><input type='submit' name='add[action]' style='width: 200px;' value='Registrera grupp' >";
			echo '</fieldset>';
			echo '</form>';

			echo "<form action='#' method='post'>";
			echo '<fieldset>';
			echo '<legend>';
			echo '<h2>Koppla Lag</h2>';
			echo '</legend>';
			echo "<lable><span style='display: inline-block; width: 120px;'>Klass:</span><select name='connect[team_id]' style='width: 200px;' >";
			echo "<option value=''>V&auml;lj lag</option>";
			foreach($teams as $team_id => $team)
			{
				echo "<option value='{$team_id}'>" . self::html_encode($team->team_name) . ' - ' . ($team->group_name ? self::html_encode($team->group_name) : '?') . ' - ' . self::html_encode(
						$team->class_name
					) . '</option>';
			}
			echo '</select></label><br />';
			echo "<lable><span style='display: inline-block; width: 120px;'>Grupp:</span><select name='connect[group_id]' style='width: 200px;' >";
			echo "<option value=''>V&auml;lj grupp</option>";
			foreach($groups as $group_id => $group)
			{
				$selected = (isset($_POST['connect']['group_id']) && $_POST['connect']['group_id'] == $group_id);
				echo "<option value='{$group_id}'" . ($selected ? " selected='selected'" : '') . '>' . self::html_encode($group->group_name) . '</option>';
			}
			echo '</select></label><br />';
			echo "<lable><span style='display: inline-block; width: 120px;'></span><input type='submit' name='connect[action]' style='width: 200px;' value='Koppla' >";
			echo '</fieldset>';
			echo '</form>';

		}

		public static function game_add_group($data)
		{
			global $wpdb;

			if(empty($data['group_name']))
			{
				trigger_error("game_add_group() require 'group_name'");
				return FALSE;
			}

			if(empty($data['class_id']))
			{
				trigger_error("game_add_group() require 'class_id'");
				return FALSE;
			}

			return $wpdb->insert('game_groups', ['group_name' => $data['group_name'], 'class_id' => $data['class_id']], ['%s', '%d']);
		}

		public static function set_team_group($team_id, $group_id)
		{
			global $wpdb;

			return $wpdb->update('game_teams', ['group_id' => $group_id], ['team_id' => $team_id], ['%d'], ['%d']);
		}

		public static function game_match_page() : void
		{
			global $wpdb;

			if(isset($_POST['add']))
			{
				$set_parts = [];
				$set_parts[] = "match_type = 'OTHER'";
				$set_parts[] = 'home_team_id = ' . (int) $_POST['add']['home'];
				$set_parts[] = 'away_team_id = ' . (int) $_POST['add']['away'];
				$query = 'INSERT INTO game_matches SET ' . implode(', ', $set_parts);
				$wpdb->query($query);
			}

			if(!empty($_POST['generate']['group']))
			{
				$query = 'INSERT INTO game_matches(match_type, match_type_id, home_team_id, away_team_id) SELECT "GROUP", t1.group_id, t1.team_id, t2.team_id FROM `game_teams` AS t1 INNER JOIN `game_teams` AS t2 ON (t1.group_id = t2.group_id AND t1.team_id < t2.team_id) LEFT JOIN game_matches AS m ON (m.match_type = "GROUP" AND m.match_type_id = t1.group_id AND m.home_team_id = t1.team_id AND m.away_team_id = t2.team_id) WHERE m.match_id IS NULL';
				if($wpdb->query($query))
				{
					echo "<p class='notice'>Matcher genererade</p>";
				}
				else
				{
					echo "<p class='warning'>Misslyckades generera matcher</p>";
				}
			}

			$sql = "UPDATE game_matches LEFT JOIN game_teams AS h ON (h.team_id = home_team_id) LEFT JOIN game_teams AS a ON (a.team_id = away_team_id) SET match_display_name = IF(COALESCE(h.team_name, home_team_description) = COALESCE(a.team_name, away_team_description), COALESCE(h.team_name, home_team_description), CONCAT(COALESCE(h.team_name, home_team_description), ' - ', COALESCE(a.team_name, away_team_description)))";
			$wpdb->query($sql);

			$sql = '
SELECT game_matches.*, game_match_time.match_time, game_fields.field_name, game_referees.referee_name, game_match_time.match_status
FROM game_matches
	LEFT JOIN game_match_time USING (match_id)
	LEFT JOIN game_match_referees USING (match_id)
	LEFT JOIN game_referees USING (referee_id)
	LEFT JOIN game_fields USING (field_id)
ORDER BY game_match_time.match_time, game_matches.match_id
';
			$matcher = $wpdb->get_results($sql, OBJECT_K);
			$sql = '
SELECT game_teams.team_id, game_teams.team_name, game_classes.class_name, game_groups.group_name
FROM game_teams
	LEFT JOIN game_classes USING (class_id)
	LEFT JOIN game_groups USING (group_id)
ORDER BY IF(game_teams.group_id IS NULL, 0, 1), game_teams.class_id, game_teams.group_id, game_teams.team_name';
			$teams = $wpdb->get_results($sql, OBJECT_K);

			echo '<style>TABLE.puggan_table TD, TABLE.puggan_table TH {padding: 3px 8px; border: solid black 1px;} TABLE.puggan_table {border: solid gray 2px;}</style>';

			echo '<h2>Matcher</h2>';
			echo "<table class='puggan_table'>";
			echo '<thead>';
			echo '<tr>';
			echo '<th>Id</th>';
			echo '<th>Lag 1</th>';
			echo '<th>Lag 2</th>';
			echo '<th>Grupp</th>';
			echo '<th>Class</th>';
			echo '<th>Time</th>';
			echo '<th>Plan</th>';
			echo '<th>Referee</th>';
			echo '<th>Status</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			if($matcher)
			{
				foreach($matcher as $match)
				{
					echo '<tr>';
					echo "<td>{$match->match_id}</td>";
					if($match->home_team_id)
					{
						echo "<td>{$teams[$match->home_team_id]->team_name}</td>";
					}
					else
					{
						echo "<td>{$match->home_team_description}</td>";
					}
					if($match->away_team_id)
					{
						echo "<td>{$teams[$match->away_team_id]->team_name}</td>";
					}
					else
					{
						echo "<td>{$match->away_team_description}</td>";
					}
					echo "<td>{$teams[$match->home_team_id]->group_name}</td>";
					echo "<td>{$teams[$match->home_team_id]->class_name}</td>";
					if($match->match_time)
					{
						echo "<td><a href=\"?page=game_edit_match&amp;id={$match->match_id}\" style=\"cursor: pointer; text-decoration: underline;\">{$match->match_time}</td>";
					}
					else
					{
						echo "<td><a href=\"?page=game_edit_match&amp;id={$match->match_id}\" style=\"cursor: pointer; text-decoration: underline;\">(set)</td>";
					}
					echo '<td>' . self::html_encode($match->field_name) . '</td>';
					echo '<td>' . self::html_encode($match->referee_name) . '</td>';
					echo '<td>' . self::html_encode($match->match_status) . '</td>';
					echo '</tr>';
				}
			}
			else
			{
				echo '<tr>';
				echo "<td colspan='4'>Inga matcher</td>";
				echo '</tr>';
			}

			echo '</tbody>';
			echo '</table>';

			echo "<form action='#' method='post'>";
			echo '<fieldset>';
			echo '<legend>';
			echo '<h2>Lägg till match</h2>';
			echo '</legend>';
			echo "<lable><span style='display: inline-block; width: 120px;'>Type</span><select name='add[type]' style='width: 200px;'>";
			echo '<option value="OTHER">Annan</option>';
			echo '</select><br />';
			echo "<lable><span style='display: inline-block; width: 120px;'>Hemma lag</span><select type='submit' name='add[home]' style='width: 200px;'>";
			echo '<option value="">-- Hemma lag --</option>';
			foreach($teams as $team)
			{
				echo "<option value=\"{$team->team_id}\">" . self::html_encode($team->team_name) . '</option>';
			}
			echo '</select><br />';
			echo "<lable><span style='display: inline-block; width: 120px;'>Borta lag</span><select type='submit' name='add[away]' style='width: 200px;'>";
			echo '<option value="">-- Borta lag --</option>';
			foreach($teams as $team)
			{
				echo "<option value=\"{$team->team_id}\">" . self::html_encode($team->team_name) . '</option>';
			}
			echo '</select><br />';
			echo "<lable><span style='display: inline-block; width: 120px;'></span><input type='submit' name='add[submit]' style='width: 200px;' value='Add match' >";
			echo '</fieldset>';
			echo '</form>';
			echo "<form action='#' method='post'>";
			echo '<fieldset>';
			echo '<legend>';
			echo '<h2>Generera Matcher</h2>';
			echo '</legend>';
			echo "<lable><span style='display: inline-block; width: 120px;'></span><input type='submit' name='generate[group]' style='width: 200px;' value='Genererar Gruppmatcher' >";
			echo '</fieldset>';
			echo '</form>';
		}

		public static function game_edit_match_page() : void
		{
			global $wpdb;

			if(empty($_GET['id']))
			{
				die('No id');
			}

			if(isset($_POST['home_team_id']))
			{
				$set_parts = [];
				$set_parts[] = 'home_team_id = ' . (int) $_POST['home_team_id'];
				$set_parts[] = 'away_team_id = ' . (int) $_POST['away_team_id'];
				$set_parts[] = "home_team_description = '" . esc_sql($_POST['home_team_description']) . "'";
				$set_parts[] = "away_team_description = '" . esc_sql($_POST['away_team_description']) . "'";

				$query = 'UPDATE game_matches SET  ' . implode(', ', $set_parts) . ' WHERE match_id = ' . (int) $_GET['id'];
				$wpdb->query($query);
			}

			if(isset($_POST['time']))
			{
				$set_parts = [];
				$set_parts[] = 'match_id = ' . (int) $_GET['id'];
				if(!$_POST['time'])
				{
					$query = 'DELETE FROM game_match_time WHERE ' . implode(' AND ', $set_parts);
				}
				else
				{
					$set_parts[] = 'field_id = ' . (int) $_POST['field'];
					$set_parts[] = 'match_time = ' . ($_POST['time'] ? date("'Y-m-d H:i'", strtotime($_POST['time'])) : 'NULL');
					$query = 'INSERT INTO game_match_time SET  ' . implode(', ', $set_parts) . ", match_status = 'QUEUE' ON DUPLICATE KEY UPDATE " . implode(', ', $set_parts);
				}
				$wpdb->query($query);
			}

			$matcher = $wpdb->get_results('SELECT * FROM game_matches LEFT JOIN game_match_time USING (match_id) WHERE match_id = ' . (int) $_GET['id'], OBJECT_K);
			if(empty($matcher))
			{
				die('Bad id');
			}

			$match = array_values($matcher)[0];

			$teams = $wpdb->get_results(
				'SELECT game_teams.team_id, game_teams.team_name, game_classes.class_name, game_groups.group_name FROM game_teams LEFT JOIN game_classes USING (class_id) LEFT JOIN game_groups USING (group_id) ORDER BY IF(game_teams.group_id IS NULL, 0, 1), game_teams.class_id, game_teams.group_id, game_teams.team_name',
				OBJECT_K
			);

			echo '<form action="#" method="post">';
			echo '<label><span>ID: </span>' . self::html_encode($match->match_id) . '</label><br />';
			echo '<label><span>Home: </span><select name="home_team_id">';
			echo '<option value="">-- no team --</option>';
			foreach($teams as $team)
			{
				if($match->home_team_id == $team->team_id)
				{
					echo "<option value=\"{$team->team_id}\" selected=\"selected\">" . self::html_encode($team->team_name) . '</option>';
				}
				else
				{
					echo "<option value=\"{$team->team_id}\">" . self::html_encode($team->team_name) . '</option>';
				}
			}
			echo '</select></label>';
			echo '<input name="home_team_description" value="' . self::html_encode($match->home_team_description) . '" /><br />';
			echo '<label><span>Away: </span><select name="away_team_id">';
			echo '<option value="">-- no team --</option>';
			foreach($teams as $team)
			{
				if($match->away_team_id == $team->team_id)
				{
					echo "<option value=\"{$team->team_id}\" selected=\"selected\">" . self::html_encode($team->team_name) . '</option>';
				}
				else
				{
					echo "<option value=\"{$team->team_id}\">" . self::html_encode($team->team_name) . '</option>';
				}
			}
			echo '</select></label>';
			echo '<input name="away_team_description" value="' . self::html_encode($match->away_team_description) . '" /><br />';
			echo '<label><span>Plan: </span><input name="field" value="' . self::html_encode($match->field_id) . '" /></label><br />';
			echo '<label><span>Time: </span><input name="time" value="' . self::html_encode($match->match_time) . '" /></label><br />';
			echo '<label><span></span><input type="submit" value="Save" /></label><br />';
			echo '</form>';
		}

		public static function game_referees_page() : void
		{
			global $wpdb;

			if(!empty($_POST['add']['action']))
			{
				$result = $wpdb->insert('game_referees', ['referee_code' => $_POST['add']['referee_code'], 'referee_name' => $_POST['add']['referee_name']], ['%s', '%s']);

				if($result)
				{
					echo '<p>Dommare tillagd.</p>';
				}
				else
				{
					echo '<p>Misslyckades lägga till dommare.</p>';
				}
			}

			$sql = 'SELECT game_referees.*, COUNT(game_match_referees.referee_id) AS c FROM game_referees LEFT JOIN game_match_referees USING (referee_id) GROUP BY referee_id ORDER BY referee_code';
			$referees = $wpdb->get_results($sql, OBJECT_K);

			echo '<style>TABLE.puggan_table TD, TABLE.puggan_table TH {padding: 3px 8px; border: solid black 1px;} TABLE.puggan_table {border: solid gray 2px;}</style>';

			echo '<h2>Dommare</h2>';
			echo "<table class='puggan_table'>";
			echo '<thead>';
			echo '<tr>';
			echo '<th>Kod</th>';
			echo '<th>Namn</th>';
			echo '<th>Matcher</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			if($referees)
			{
				foreach($referees as $referee)
				{
					echo '<tr>';
					echo "<td>{$referee->referee_code}</td>";
					echo "<td>{$referee->referee_name}</td>";
					echo "<td>{$referee->c}</td>";
					echo '</tr>';
				}
			}
			else
			{
				echo '<tr>';
				echo "<td colspan='2'>Inga dommare</td>";
				echo '</tr>';
			}

			echo '</tbody>';
			echo '</table>';

			echo "<form action='#' method='post'>";
			echo '<fieldset>';
			echo '<legend>';
			echo '<h2>Lägg till dommare</h2>';
			echo '</legend>';
			echo "<lable><span style='display: inline-block; width: 120px;'>Kod:</span><input name='add[referee_code]' style='width: 200px;' /></label><br />";
			echo "<lable><span style='display: inline-block; width: 120px;'>Namn:</span><input name='add[referee_name]' style='width: 200px;' /></label><br />";
			echo "<lable><span style='display: inline-block; width: 120px;'></span><input type='submit' name='add[action]' style='width: 200px;' value='Lägg till dommare' >";
			echo '</fieldset>';
			echo '</form>';
		}

	}
