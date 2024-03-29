<?php

namespace Puggan\Ibn;

use PHPDoc\DbResults\AutoCount;
use PHPDoc\DbResults\ClassCount;
use PHPDoc\DbResults\GFClass;
use PHPDoc\DbResults\GFTeam;
use PHPDoc\DbResults\GFTeamWithClass;
use PHPDoc\DbResults\GroupWithTeams;
use PHPDoc\DbResults\MatchWithExtra;
use PHPDoc\DbResults\MatchWithTime;
use PHPDoc\DbResults\RefereeCount;
use PHPDoc\DbResults\Team;
use PHPDoc\DbResults\TeamPlaceholderCount;
use PHPDoc\Models\GameClass;

add_action('admin_menu', [ScoutTournament::class, 'init_add_game_menu']);

class ScoutTournament
{
    public static int $GAME_FROM_ID = 1;
    public static int $GAME_CLASS_FIELD_ID = 14;
    public static int $GAME_TEAM_FIELD_ID = 16;

    /**
     * @return void
     */
    public static function init_add_game_menu(): void
    {
        $icon_url = get_template_directory_uri() . '/game/ball.png';
        $menu_slug = 'game';
        $capability = 'edit_pages';
        /** @noinspection UnusedFunctionResultInspection */
        add_menu_page('Administrera Spel', 'Spel', $capability, $menu_slug, [self::class, 'game_page'], $icon_url, 58);
        /** @noinspection UnusedFunctionResultInspection */
        add_submenu_page($menu_slug, 'Deltagande Lag', 'Lag', $capability, $menu_slug . '_teams', [self::class, 'game_team_page']);
        /** @noinspection UnusedFunctionResultInspection */
        add_submenu_page($menu_slug, 'Grupper', 'Grupp', $capability, $menu_slug . '_groups', [self::class, 'game_group_page']);
        /** @noinspection UnusedFunctionResultInspection */
        add_submenu_page($menu_slug, 'Matcher', 'Match', $capability, $menu_slug . '_match', [self::class, 'game_match_page']);
        /** @noinspection UnusedFunctionResultInspection */
        add_submenu_page($menu_slug, 'Edit Match', 'Edit Match', $capability, $menu_slug . '_edit_match', [self::class, 'game_edit_match_page']);
        /** @noinspection UnusedFunctionResultInspection */
        add_submenu_page($menu_slug, 'Domare', 'Domare', $capability, $menu_slug . '_referees', [self::class, 'game_referees_page']);
    }

    //<editor-fold desc="Functions">

    /**
     * @param string $s
     *
     * @return string
     */
    public static function html_encode(string $s): string
    {
        return htmlentities($s, ENT_QUOTES | ENT_XHTML);
    }

    /**
     * @param object|array $a
     *
     * @return object
     */
    public static function html_encode_object(array|object $a): object
    {
        $o = (object)[];
        foreach ((array)$a as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $o->$key = self::html_encode_object($value);
                continue;
            }
            if (!$value) {
                $o->$key = '';
                continue;
            }
            $o->$key = self::html_encode($value);
        }
        return $o;
    }

    /**
     * @param int[]|string[] $data string team_name, int class_id
     *
     * @return void
     */
    public static function game_add_team(array $data): void
    {
        global $wpdb;
        if (empty($data['team_name'])) {
            throw new \RuntimeException("self::game_add_team() require 'team_name'");
        }
        if (empty($data['class_id'])) {
            throw new \RuntimeException("self::game_add_team() require 'class_id'");
        }
        // FIXME: build add, double encodes ' => in database \'
        $result = $wpdb->insert(
            'game_teams',
            [
                'team_name' => $data['team_name'],
                'class_id' => $data['class_id'],
            ],
            [
                '%s',
                '%d',
            ]
        );
        if($result === false) {
            throw new \RuntimeException('insert failed');
        }
    }

    /**
     * @param int[]|string[] $data string group_name & int class_id
     *
     * @return void
     */
    public static function game_add_group(array $data): void
    {
        global $wpdb;
        if (empty($data['group_name'])) {
            throw new \RuntimeException("game_add_group() require 'group_name'");
        }
        if (empty($data['class_id'])) {
            throw new \RuntimeException("game_add_group() require 'class_id'");
        }
        $result = $wpdb->insert(
            'game_groups',
            [
                'group_name' => $data['group_name'],
                'class_id' => $data['class_id'],
            ],
            [
                '%s',
                '%d',
            ]
        );
        if($result === false) {
            throw new \RuntimeException('insert failed');
        }
    }

    /**
     * @param string $name string
     *
     * @return void
     */
    public static function game_add_class(string $name): void
    {
        global $wpdb;
        if (empty($name)) {
            throw new \RuntimeException('game_add_class() require name');
        }
        $result = $wpdb->insert(
            'game_classes',
            [
                'class_name' => $name,
            ],
            [
                '%s',
            ]
        );
        if($result === false) {
            throw new \RuntimeException('insert failed');
        }
    }

    /**
     * @param int $team_id
     * @param int $group_id
     *
     * @return void
     */
    public static function set_team_group(int $team_id, int $group_id): void
    {
        global $wpdb;
        $result = $wpdb->update(
            'game_teams',
            [
                'group_id' => $group_id,
            ],
            [
                'team_id' => $team_id,
            ],
            [
                '%d',
            ],
            [
                '%d',
            ]
        );
        if($result === false) {
            throw new \RuntimeException('insert failed');
        }
    }

    /**
     * @param string $placeholder
     * @param int $team_id
     */
    public static function game_connect_placeholder(string $placeholder, int $team_id): void
    {
        global $wpdb;

        $result = $wpdb->update(
            'game_matches',
            [
                'home_team_id' => $team_id,
            ],
            [
                'home_team_id' => null,
                'home_team_description' => $placeholder,
            ],
            [
                '%d',
            ],
            [
                '%d',
                '%s',
            ]
        );
        if($result === false) {
            throw new \RuntimeException('update failed');
        }

        $result = $wpdb->update(
            'game_matches',
            [
                'away_team_id' => $team_id,
            ],
            [
                'away_team_id' => null,
                'away_team_description' => $placeholder,
            ],
            [
                '%d',
            ],
            [
                '%d',
                '%s',
            ]
        );
        if($result === false) {
            throw new \RuntimeException('update failed');
        }
    }
    //</editor-fold>

    //<editor-fold desc="Pages">
    /**
     * @return void
     */
    public static function game_page(): void
    {
        global $wpdb;
        $html_parts = (object)[];

        if (!empty($_GET['attendance'])) {
            $result = $wpdb->replace(
                'game_attendance',
                [
                    'team_id' => $_GET['attendance'],
                    'attendance_status' => 'ok',
                ],
                [
                    '%d',
                    '%s',
                ]
            );
            if($result === false) {
                throw new \RuntimeException('replace failed');
            }
        }

        $query = <<<'SQL_BLOCK'
				SELECT
					game_classes.*,
					COUNT(game_teams.team_id) AS teams
				FROM game_classes
					LEFT JOIN game_teams USING (class_id)
				GROUP BY class_id
			SQL_BLOCK;
        /** @var ClassCount[] $classes */
        $classes = $wpdb->get_results($query, OBJECT_K);
        if (!$classes) {
            $html_parts->classes_tbody = <<<HTML_BLOCK
					<tr>
						<td colspan='2'>Inga klasser</td>
					</tr>
				HTML_BLOCK;
        } else {
            $html_parts->classes_tbody = '';
            foreach ($classes as $class) {
                /** @var ClassCount $safe_class */
                $safe_class = self::html_encode_object($class);
                $html_parts->classes_tbody .= <<<HTML_BLOCK
						<tr>
							<td>{$safe_class->class_name}</td>
							<td>{$safe_class->teams}</td>
						</tr>
					HTML_BLOCK;
            }
        }
        $query = <<<'SQL_BLOCK'
				SELECT
					game_teams.team_id,
					game_teams.team_name,
					game_classes.class_name,
					game_groups.group_name,
					game_attendance.attendance_status
				FROM game_teams
					LEFT JOIN game_classes USING (class_id)
					LEFT JOIN game_groups USING (group_id)
					LEFT JOIN game_attendance USING (team_id)
				ORDER BY
					game_teams.class_id,
					game_teams.group_id,
					game_teams.team_name
			SQL_BLOCK;
        /** @var Team[] $teams */
        $teams = $wpdb->get_results($query, OBJECT_K);
        if (!$teams) {
            $html_parts->team_tbody = <<<HTML_BLOCK
					<tr>
						<td colspan='3'>Inga lag</td>
					</tr>
				HTML_BLOCK;
        } else {
            $html_parts->team_tbody = '';
            foreach ($teams as $team) {
                /** @var Team $teams */
                $safe_team = self::html_encode_object($team);
                $status = self::html_encode($safe_team->attendance_status) ?: "<a href='?page=game&amp;attendance={$safe_team->team_id}'>add</a>";
                $html_parts->team_tbody .= <<<HTML_BLOCK
						<tr>
							<td><a href='?page=game_teams&amp;team_id={$safe_team->team_id}'>{$safe_team->team_name}</a></td>
							<td>{$safe_team->class_name}</td>
							<td>{$safe_team->group_name}</td>
							<td>{$status}</td>
						</tr>
					HTML_BLOCK;
            }
        }

        echo <<<HTML_BLOCK
				<style>
					TABLE.puggan_table TD, TABLE.puggan_table TH
					{
						padding: 3px 8px;
						border: solid black 1px;
					}
					TABLE.puggan_table
					{
						border: solid gray 2px;
					}
				</style>

				<h2>Klasser</h2>
				<table class='puggan_table'>
					<thead>
						<tr>
							<th>Klass</th>
							<th>Antal Lag</th>
						</tr>
					</thead>
					<tbody>
						{$html_parts->classes_tbody}
					</tbody>
				</table>

				<h2>Lag</h2>
				<table class='puggan_table'>
					<thead>
						<tr>
							<th>Lagnamn</th>
							<th>Klass</th>
							<th>Grupp</th>
							<th>Närvarande</th>
						</tr>
					</thead>
					<tbody>
						{$html_parts->team_tbody}
					</tbody>
				</table>
			HTML_BLOCK;
    }

    /**
     * @throws \RuntimeException
     */
    public static function game_team_page(): void
    {
        global $wpdb;
        $safe_post = self::html_encode_object($_POST);
        $html_parts = (object)[];

        if (isset($safe_post->bulk_button)) {
            foreach ($safe_post->bulk as $row_index => $row) {
                if (($row->action ?? '') === 'add') {
                    self::game_add_team($_POST['bulk'][$row_index]);
                    echo "<p class='notice'>Lag {$row->team_name} registrerad</p>";
                }
            }
        }
        if (!empty($safe_post->add->action)) {
            self::game_add_team($_POST['add']);
            echo "<p class='notice'>Lag {$safe_post->add->team_name} registrerad</p>";
        }
        if (!empty($safe_post->connect->action)) {
            self::game_connect_placeholder($_POST['connect']['placeholder'], $_POST['connect']['team_id']);
        }
        /** @var GameClass[] $classes */
        $classes = $wpdb->get_results('SELECT game_classes.* FROM game_classes', OBJECT_K);
        $query = <<<'SQL_BLOCK'
				SELECT
					game_teams.team_id,
					game_teams.team_name,
					game_classes.class_name,
					game_groups.group_name
				FROM game_teams
					LEFT JOIN game_classes USING (class_id)
					LEFT JOIN game_groups USING (group_id)
				ORDER BY
					game_teams.class_id,
					game_teams.group_id,
					game_teams.team_name
			SQL_BLOCK;
        if (!empty($_GET['team_id'])) {
            $where_part = 'WHERE team_id = ' . ((int)$_GET['team_id']);
            $query = str_replace('ORDER BY', $where_part . ' ORDER BY', $query);
        }
        /** @var Team[] $teams */
        $teams = $wpdb->get_results($query, OBJECT_K);
        $team_count = count($teams);
        $FIELD_ID = self::$GAME_TEAM_FIELD_ID;
        $GAME_FROM_ID = self::$GAME_FROM_ID;
        $query = <<<SQL_BLOCK
				SELECT
					value AS team_name,
					COUNT(*) AS team_members,
					MAX(lead_id) AS lead_id
				FROM wp_rg_lead_detail
				WHERE
					form_id = {$GAME_FROM_ID} AND
					field_number = {$FIELD_ID}
				GROUP BY
					team_name
				ORDER BY
					team_members DESC,
					team_name
			SQL_BLOCK;
        /** @var GFTeam[] $new_teams */
        $new_teams = $wpdb->get_results(
            $query,
            OBJECT_K
        );
        $team_names = [];
        foreach ($teams as $team) {
            $team_names[$team->team_name] = $team->team_id;
        }
        /** @var GFTeamWithClass[] $missing_teams */
        $missing_teams = [];
        /** @var int[] $missing_team_lead_ids */
        $missing_team_lead_ids = [];
        foreach ($new_teams as $team) {
            if (!isset($team_names[$team->team_name])) {
                $missing_teams[$team->lead_id] = $team;
                $missing_team_lead_ids[$team->lead_id] = (int)$team->lead_id;
            }
        }
        if ($missing_teams) {
            $missing_id_string = implode(
                ', ',
                $missing_team_lead_ids
            );
            $GAME_FROM_ID = self::$GAME_FROM_ID;
            $GAME_CLASS_FIELD_ID = self::$GAME_CLASS_FIELD_ID;
            $query = <<<SQL_BLOCK
					SELECT
						lead_id,
						value AS class_name
					FROM wp_rg_lead_detail
					WHERE
						form_id = {$GAME_FROM_ID} AND
						field_number = {$GAME_CLASS_FIELD_ID} AND
						lead_id IN ({$missing_id_string})
				SQL_BLOCK;
            /** @var GFClass[] $team_classes_rows */
            $team_classes_rows = $wpdb->get_results($query, OBJECT_K);
            foreach ($missing_teams as $team) {
                $team->class_id = (int)$team_classes_rows[$team->lead_id]->class_name;
            }
            $html_parts->missing_teams_tbody = '';
            foreach ($missing_teams as $lead_id => $team) {
                $team_class = $classes[$team->class_id];
                /** @var GFTeam $safe_team */
                $safe_team = self::html_encode_object($team);
                /** @var GameClass $safe_team_class */
                $safe_team_class = self::html_encode_object($team_class);
                $html_parts->missing_teams_tbody .= <<<HTML_BLOCK
						<tr>
							<td><input type='checkbox' name='bulk[{$lead_id}][action]' value='add' /></td>
							<td><input type='text' readonly='readonly' name='bulk[{$lead_id}][team_name]' value='{$safe_team->team_name}' /></td>
							<td><input type='hidden' name='bulk[{$lead_id}][class_id]' value='{$team->class_id}' />{$safe_team_class->class_name}</td>
						</tr>
					HTML_BLOCK;
            }
        } else {
            $html_parts->missing_teams_tbody = <<<HTML_BLOCK
					<tr>
						<td colspan="3">
							<p class='notice'>Alla anmälda lag är registrerade</p>
						</td>
					</tr>
				HTML_BLOCK;
        }

        $js = <<<JS_BLOCK
					let e = this;
					for(let t = 0; t < 20; t++)
					{
						if(e.tagName === 'TABLE')
						{
							break;
						}
						e = e.parentNode;
					}
					if(e.tagName === 'TABLE')
					{
						const ee = e.getElementsByTagName('INPUT');
						for(let t = 0; t < ee.length; t++)
						{
							if(ee[t].type === 'checkbox')
							{
								if(ee[t] !== this)
								{
									ee[t].checked = this.checked;
								}
							}
						}
					}
				JS_BLOCK;
        $html_parts->master_checkbox_js = self::html_encode(preg_replace("/[ \t\r\n]+/", ' ', $js));

        $html_parts->class_options = '';
        foreach ($classes as $class_id => $class) {
            /** @var GameClass $safe_class */
            $safe_class = self::html_encode_object($class);
            $html_parts->class_options .= "<option value='{$class_id}'>{$safe_class->class_name}</option>";
        }

        $query = <<<SQL_BLOCK
				SELECT
			      game_team_autoselect.match_id * 2 + IF(game_team_autoselect.side = 'HOME', 0, 1) as dummy_id,
					game_team_autoselect.auto_type,
					game_team_autoselect.type_id,
					COUNT(game_team_autoselect.match_id) AS auto_count,
					COUNT(IF(game_team_autoselect.side = 'HOME' AND game_matches.home_team_id > 0, 1, NULL)) +
					COUNT(IF(game_team_autoselect.side = 'AWAY' AND game_matches.away_team_id > 0, 1, NULL)) AS auto_connected
				FROM game_team_autoselect
					LEFT JOIN game_matches USING (match_id)
				GROUP BY
					game_team_autoselect.auto_type DESC,
					game_team_autoselect.type_id
			SQL_BLOCK;

        /** @var AutoCount[] $auto_rows */
        $auto_rows = $wpdb->get_results($query, OBJECT_K);
        $html_parts->auto_connect_tbody = '';
        foreach ($auto_rows as $auto_row) {
            /** @var AutoCount $safe_auto_row */
            $safe_auto_row = self::html_encode_object($auto_row);
            if ($auto_row->auto_connected < $auto_row->auto_count) {
                $status = 'Pending';
            } else {
                $status = 'Done';
            }

            $html_parts->auto_connect_tbody .= <<<HTML_BLOCK
					<tr>
						<td>{$safe_auto_row->auto_type}</td>
						<td>{$safe_auto_row->type_id}</td>
						<td>{$safe_auto_row->auto_connected}</td>
						<td>{$safe_auto_row->auto_count}</td>
						<td>{$status}</td>
					</tr>
				HTML_BLOCK;
        }

        $query = <<<SQL_BLOCK
				SELECT
					team,
					COUNT(*) as c
				FROM
				(

				   SELECT
						match_id * 2 + 0 as dummy_id,
						home_team_description as team
				   FROM game_matches
				   WHERE home_team_id IS NULL

				   UNION

				   SELECT
						match_id * 2 + 1,
						away_team_description
				   FROM game_matches
				   WHERE away_team_id IS NULL

			   ) as l
				GROUP BY team;
			SQL_BLOCK;

        $make_team_option = static function ($t) {
            /** @var Team t */
            /** @var Team $team */
            $team = self::html_encode_object($t);
            return "<option value='{$team->team_id}'>{$team->team_name}</option>";
        };
        $team_options_list = [];
        foreach ($teams as $team) {
            $team_options_list["{$team->class_name} - {$team->group_name}"][$team->team_name] = $make_team_option($team);
        }
        ksort($team_options_list);
        $team_options_groups = [];
        foreach ($team_options_list as $group => $team_option) {
            $team_options_groups[$group] = implode(PHP_EOL, $team_option);
            $group_name = self::html_encode($group);
            $team_options_groups[$group] = "<optgroup label='{$group_name}'>{$team_options_groups[$group]}</optgroup>";
        }
        $team_options = implode(PHP_EOL, $team_options_groups);

        /** @var TeamPlaceholderCount[] $placeholders */
        $placeholders = $wpdb->get_results($query, OBJECT_K);
        $html_parts->placeholder_tbody = '';
        foreach ($placeholders as $placeholder) {
            /** @var TeamPlaceholderCount $safe_placeholder */
            $safe_placeholder = self::html_encode_object($placeholder);

            $html_parts->placeholder_tbody .= <<<HTML_BLOCK
					<tr>
						<td>{$safe_placeholder->team}</td>
						<td>{$safe_placeholder->c}</td>
						<td>
							<form method="post" action="#">
								<input type="hidden" name="connect[placeholder]" value="{$safe_placeholder->team}" />
								<select name="connect[team_id]"><option value=''>-- teams --</option>{$team_options}</select>
								<input type="submit" name="connect[action]" value="Connect" />
							</form>
						</td>
					</tr>
				HTML_BLOCK;
        }

        echo <<<HTML_BLOCK
				<style>
					TABLE.puggan_table TD, TABLE.puggan_table TH
					{
						padding: 3px 8px;
						border: solid black 1px;
					}
					TABLE.puggan_table
					{
						border: solid gray 2px;
					}
				</style>

				<h2>Anmälda, oregistrerade lag</h2>
				<form action='#' method='post'>
					<table class='puggan_table'>
						<thead>
							<tr>
								<th><input type='checkbox' onchange='{$html_parts->master_checkbox_js}' /></th>
								<th>Lagnamn</th>
								<th>Klass</th>
							</tr>
						</thead>
						<tbody>
							{$html_parts->missing_teams_tbody}
						</tbody>
					</table>
					<label>
						<span></span>
						<input type='submit' name='bulk_button' value='Lägg till markerade lag' />
					</label>
				</form>
				<form action='#' method='post'>
					<fieldset>
						<legend>
							<h2>Registrera Lag</h2>
						</legend>
						<label>
							<span style='display: inline-block; width: 120px;'>Lagnamn:</span>
							<input name='add[team_name]' style='width: 200px;' />
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'>Klass:</span>
							<select name='add[class_id]' style='width: 200px;' >
								<option value=''>V&auml;lj klass</option>
								{$html_parts->class_options}
							</select>
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'></span>
							<input type='submit' name='add[action]' style='width: 200px;' value='Registrera lag' />
						</label>
					</fieldset>
					<fieldset>
						<legend>
							<h2>Registrerade lag</h2>
						</legend>
						<p>
							<a href='?page=game'>
								{$team_count} Lag registrerade
							</a>
						</p>
					</fieldset>
					<fieldset>
						<legend>
							<h2>Lag Matchning - Auto</h2>
						</legend>
						<p>Automatiskt koppla vinnare och grupp-placeringar till nya matcher</p>
						<table class='puggan_table'>
							<colgroup>
								<col />
								<col />
								<col />
								<col />
								<col />
							</colgroup>
							<thead>
								<tr>
									<th>Type</th>
									<th>ID</th>
									<th>Connected</th>
									<th>Count</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								{$html_parts->auto_connect_tbody}
							</tbody>
						</table>
					</fieldset>
					<fieldset>
						<legend>
							<h2>Lag Matchning - Manuell</h2>
						</legend>
						<p>Manuellt koppla lag till placeholders</p>
						<table class='puggan_table'>
							<colgroup>
								<col />
								<col />
								<col />
							</colgroup>
							<thead>
								<tr>
									<th>Placeholder</th>
									<th>Count</th>
									<th>Team</th>
								</tr>
							</thead>
							<tbody>
								{$html_parts->placeholder_tbody}
							</tbody>
						</table>
					</fieldset>
				</form>
			HTML_BLOCK;
    }

    /**
     * @throws \RuntimeException
     */
    public static function game_group_page(): void
    {
        global $wpdb;
        $safe_post = self::html_encode_object($_POST);
        $html_parts = (object)[];

        if (!empty($safe_post->add->action)) {
            self::game_add_group($_POST['add']);
            echo "<p class='notice'>Grupp {$safe_post->add->group_name} registrerad</p>";
        }
        if (!empty($safe_post->class->action)) {
            self::game_add_class($_POST['class']['class_name']);
            echo "<p class='notice'>Klass {$safe_post->class->class_name} registrerad</p>";
        }
        $group_query = <<<'SQL_BLOCK'
				SELECT
					game_groups.*,
					game_classes.class_name,
					COUNT(game_teams.team_id) AS team_count,
					GROUP_CONCAT(game_teams.team_name SEPARATOR ', ') AS teams
				FROM game_groups
				   LEFT JOIN game_classes USING (class_id)
				   LEFT JOIN game_teams USING (class_id, group_id)
				GROUP BY
					group_id
				ORDER BY
					game_groups.class_id,
					game_groups.group_name
			SQL_BLOCK;
        /** @var GroupWithTeams[] $groups */
        $groups = $wpdb->get_results($group_query, OBJECT_K);
        /** @var GameClass[] $classes */
        $classes = $wpdb->get_results('SELECT game_classes.* FROM game_classes', OBJECT_K);
        $team_query = <<<'SQL_BLOCK'
				SELECT
					game_teams.team_id,
					game_teams.team_name,
					game_classes.class_name,
					game_groups.group_name
				FROM game_teams
				   LEFT JOIN game_classes USING (class_id)
				   LEFT JOIN game_groups USING (group_id)
				ORDER BY
					IF(game_teams.group_id IS NULL, 0, 1),
					game_teams.class_id,
					game_teams.group_id,
					game_teams.team_name
			SQL_BLOCK;
        /** @var Team[] $teams */
        $teams = $wpdb->get_results($team_query, OBJECT_K);
        if (!empty($safe_post->connect->action)) {
            /** @var Team $safe_team */
            $safe_team = self::html_encode_object($teams[$_POST['connect']['team_id']]);
            /** @var GroupWithTeams $safe_group */
            $safe_group = self::html_encode_object($groups[$_POST['connect']['group_id']]);
            self::set_team_group($_POST['connect']['team_id'], $_POST['connect']['group_id']);
            echo "<p class='notice'>Kopplade '{$safe_team->group_name}' till grupp '{$safe_group->group_name}'</p>";
            // reload teams and groups
            /** @var GroupWithTeams[] $groups */
            $groups = $wpdb->get_results($group_query, OBJECT_K);
            /** @var Team[] $teams */
            $teams = $wpdb->get_results($team_query, OBJECT_K);
        }

        if ($groups) {
            $html_parts->groups_tbody = '';
            foreach ($groups as $group) {
                /** @var GroupWithTeams $safe_group */
                $safe_group = self::html_encode_object($group);
                $html_parts->groups_tbody .= <<<HTML_BLOCK
						<tr>
							<td>{$safe_group->group_name}</td>
							<td>{$safe_group->class_name}</td>
							<td>{$safe_group->team_count}</td>
							<td>{$safe_group->teams}</td>
						</tr>
					HTML_BLOCK;
            }
        } else {
            $html_parts->groups_tbody = <<<HTML_BLOCK
					<tr>
						<td colspan='4'>Inga grupper registrerade</td>
					</tr>
				HTML_BLOCK;
        }
        $html_parts->class_options = '';
        foreach ($classes as $class_id => $class) {
            /** @var GameClass $safe_class */
            $safe_class = self::html_encode_object($class);
            $html_parts->class_options .= "<option value='{$class_id}'>{$safe_class->class_name}</option>";
        }
        $html_parts->team_options = '';
        foreach ($teams as $team_id => $team) {
            /** @var Team $safe_team */
            $safe_team = self::html_encode_object($team);
            if ($team->group_name) {
                $html_parts->team_options .= "<option value='{$team_id}'>{$safe_team->team_name} - {$safe_team->group_name} - {$safe_team->class_name}</option>";
            } else {
                $html_parts->team_options .= "<option value='{$team_id}'>{$safe_team->team_name} - ? - {$safe_team->class_name}</option>";
            }
        }
        $html_parts->group_options = '';
        foreach ($groups as $group_id => $group) {
            /** @var GroupWithTeams $safe_group */
            $safe_group = self::html_encode_object($group);
            if ($group_id === +($_POST['connect']['group_id'] ?? 0)) {
                $html_parts->group_options .= "<option value='{$group_id}' selected='selected'>{$safe_group->group_name}</option>";
            } else {
                $html_parts->group_options .= "<option value='{$group_id}'>{$safe_group->group_name}</option>";
            }
        }

        echo <<<HTML_BLOCK
				<style>
					TABLE.puggan_table TD, TABLE.puggan_table TH
					{
						padding: 3px 8px;
						border: solid black 1px;
					}
					TABLE.puggan_table
					{
						border: solid gray 2px;
					}
				</style>

				<h2>Grupper</h2>
				<table class='puggan_table'>
					<thead>
						<tr>
							<th>Grupp</th>
							<th>Class</th>
							<th colspan='2'>Lag</th>
						</tr>
					</thead>
					<tbody>
						{$html_parts->groups_tbody}
					</tbody>
				</table>

				<form action='#' method='post'>
					<fieldset>
						<legend>
							<h2>Registrera Grupp</h2>
						</legend>
						<label>
							<span style='display: inline-block; width: 120px;'>Gruppnamn:</span>
							<input name='add[group_name]' style='width: 200px;' />
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'>Klass:</span>
							<select name='add[class_id]' style='width: 200px;'>
								<option value=''>V&auml;lj klass</option>
								{$html_parts->class_options}
							</select>
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'></span>
							<input type='submit' name='add[action]' style='width: 200px;' value='Registrera grupp' />
						</label>
					</fieldset>
				</form>

				<form action='#' method='post'>
					<fieldset>
						<legend>
							<h2>Koppla Lag</h2>
						</legend>
						<label>
							<span style='display: inline-block; width: 120px;'>Lag:</span>
							<select name='connect[team_id]' style='width: 200px;' >
								<option value=''>V&auml;lj lag</option>
								{$html_parts->team_options}
							</select>
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'>Grupp:</span>
							<select name='connect[group_id]' style='width: 200px;' >
								<option value=''>V&auml;lj grupp</option>
								{$html_parts->group_options}
							</select>
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'></span>
							<input type='submit' name='connect[action]' style='width: 200px;' value='Koppla' />
						</label>
					</fieldset>
				</form>
				<form action='#' method='post'>
					<fieldset>
						<legend>
							<h2>Skapa Klass</h2>
						</legend>
						<label>
							<span style='display: inline-block; width: 120px;'>Klass:</span>
							<input name='class[class_name]' style='width: 200px;' />
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'></span>
							<input type='submit' name='class[action]' style='width: 200px;' value='Skapa' />
						</label>
					</fieldset>
				</form>
			HTML_BLOCK;
    }

    /**
     * @return void
     */
    public static function game_match_page(): void
    {
        global $wpdb;
        $safe_post = self::html_encode_object($_POST);
        $html_parts = (object)[];

        if (isset($safe_post->add)) {
            $set_parts = [];
            $set_parts[] = "match_type = 'OTHER'";
            $set_parts[] = 'home_team_id = ' . (int)$_POST['add']['home'];
            $set_parts[] = 'away_team_id = ' . (int)$_POST['add']['away'];
            $query = 'INSERT INTO game_matches SET ' . implode(', ', $set_parts);
            $result = $wpdb->query($query);
            if($result === false) {
                throw new \RuntimeException('query failed');
            }
        }
        if (!empty($safe_post->generate->group)) {
            $query = <<<'SQL_BLOCK'
					INSERT INTO game_matches(
						match_type,
						match_type_id,
						home_team_id,
						away_team_id
					)
					SELECT
					       'GROUP',
					       t1.group_id,
					       t1.team_id,
					       t2.team_id
					FROM game_teams AS t1
					   INNER JOIN `game_teams` AS t2 ON (
					      t1.group_id = t2.group_id AND
					      t1.team_id < t2.team_id
				      )
					   LEFT JOIN game_matches AS m ON (
					      m.match_type = 'GROUP' AND
					      m.match_type_id = t1.group_id AND
					      m.home_team_id = t1.team_id AND
					      m.away_team_id = t2.team_id
				      )
					WHERE m.match_id IS NULL
				SQL_BLOCK;
            if ($wpdb->query($query)) {
                echo "<p class='notice'>Matcher genererade</p>";
            } else {
                echo "<p class='warning'>Misslyckades generera matcher</p>";
            }
        }
        $query = <<<SQL_BLOCK
				UPDATE game_matches
			      LEFT JOIN game_teams AS h ON (h.team_id = home_team_id)
				   LEFT JOIN game_teams AS a ON (a.team_id = away_team_id)
				SET
					match_display_name = IF(
						COALESCE(h.team_name, home_team_description) = COALESCE(a.team_name, away_team_description),
						COALESCE(h.team_name, home_team_description),
						CONCAT(
							COALESCE(h.team_name, home_team_description),
						   ' - ',
						   COALESCE(a.team_name, away_team_description)
					   )
				   )
			SQL_BLOCK;
        $result = $wpdb->query($query);
        if($result === false) {
            throw new \RuntimeException('query failed');
        }
        $query = <<<'SQL_BLOCK'
				SELECT
					game_matches.*,
					game_match_time.match_time,
					game_fields.field_name,
					game_referees.referee_name,
					game_results.home_goals,
					game_results.away_goals,
					game_match_time.match_status
				FROM game_matches
					LEFT JOIN game_match_time USING (match_id)
					LEFT JOIN game_match_referees USING (match_id)
					LEFT JOIN game_referees USING (referee_id)
					LEFT JOIN game_fields USING (field_id)
					LEFT JOIN game_results USING (match_id)
				ORDER BY
					game_match_time.match_time,
					game_matches.match_id
			SQL_BLOCK;
        /** @var MatchWithExtra[] $matcher */
        $matcher = $wpdb->get_results($query, OBJECT_K);
        $query = <<<'SQL_BLOCK'
				SELECT
					game_teams.team_id,
					game_teams.team_name,
					game_classes.class_name,
					game_groups.group_name
				FROM game_teams
					LEFT JOIN game_classes USING (class_id)
					LEFT JOIN game_groups USING (group_id)
				ORDER BY
					IF(game_teams.group_id IS NULL, 0, 1),
					game_teams.class_id,
					game_teams.group_id,
					game_teams.team_name
			SQL_BLOCK;
        /** @var Team[] $teams */
        $teams = $wpdb->get_results($query, OBJECT_K);

        if ($matcher) {
            $html_parts->match_tbody = '';
            foreach ($matcher as $match) {
                /** @var MatchWithExtra $safe_match */
                $safe_match = self::html_encode_object($match);
                /** @var Team $safe_home_team */
                $safe_home_team = isset($teams[$match->home_team_id]) ? self::html_encode_object($teams[$match->home_team_id]) : (object) ['class_name' => '', 'team_name' => ''];
                /** @var Team $safe_away_team */
                $safe_away_team = isset($teams[$match->away_team_id]) ? self::html_encode_object($teams[$match->away_team_id]) : (object) ['class_name' => '', 'team_name' => ''];
                $home_team_name = $safe_home_team->team_name ?: $match->home_team_description;
                $away_team_name = $safe_away_team->team_name ?: $match->away_team_description;
                $match_time = $match->match_time ? $safe_match->match_time : '(set)';
                $tr_class = 'match_status_' . strtolower($safe_match->match_status);
                $goals = ($safe_match->home_goals ?: 0) . ' - ' . ($safe_match->away_goals ?: 0);
                $goals = $safe_match->match_status === 'QUEUE' ? '' : $goals;
                $group_name = $safe_match->match_type === 'GROUP' ? $safe_home_team->group_name : $safe_match->match_type;
                $html_parts->match_tbody .= <<<HTML_BLOCK
						<tr class='{$tr_class}'>
							<td>{$match->match_id}</td>
							<td>{$home_team_name}</td>
							<td>{$away_team_name}</td>
							<td>{$group_name}</td>
							<td>{$safe_home_team->class_name}</td>
							<td><a href="?page=game_edit_match&amp;id={$safe_match->match_id}" style="cursor: pointer; text-decoration: underline;">{$match_time}</td>
							<td>{$safe_match->field_name}</td>
							<td>{$safe_match->referee_name}</td>
							<td>{$goals}</td>
							<td>{$safe_match->match_status}</td>
						</tr>
					HTML_BLOCK;
            }
        } else {
            $html_parts->match_tbody = <<<HTML_BLOCK
					<tr>
						<td colspan='4'>Inga matcher</td>
					</tr>
				HTML_BLOCK;
        }

        $html_parts->team_options = '';
        foreach ($teams as $team) {
            /** @var Team $safe_team */
            $safe_team = self::html_encode_object($team);
            $html_parts->team_options .= "<option value='{$team->team_id}'>{$safe_team->team_name}</option>";
        }

        echo <<<HTML_BLOCK
				<style>
					TABLE.puggan_table TD, TABLE.puggan_table TH
					{
						padding: 3px 8px;
						border: solid black 1px;
					}

					TABLE.puggan_table
					{
						border: solid gray 2px;
					}
					
					TR.match_status_played {
						background-color: rgba(0,0,255, 0.2);
					}
					TR.match_status_started {
						background-color: rgba(0,255,0, 0.2);
					}
				</style>

				<h2>Matcher</h2>
				<table class='puggan_table'>
					<thead>
						<tr>
							<th>Id</th>
							<th>Lag 1</th>
							<th>Lag 2</th>
							<th>Grupp</th>
							<th>Class</th>
							<th>Time</th>
							<th>Plan</th>
							<th>Referee</th>
							<th>Mål</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						{$html_parts->match_tbody}
					</tbody>
				</table>

				<form action='#' method='post'>
					<fieldset>
						<legend>
							<h2>Lägg till match</h2>
						</legend>
						<label>
							<span style='display: inline-block; width: 120px;'>Type</span>
							<select name='add[type]' style='width: 200px;'>
								<option value='OTHER'>Annan</option>
							</select>
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'>Hemma lag</span>
							<select name='add[home]' style='width: 200px;'>
								<option value=''>-- Hemma lag --</option>
								{$html_parts->team_options}
							</select>
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'>Borta lag</span>
							<select name='add[away]' style='width: 200px;'>
								<option value=''>-- Borta lag --</option>
								{$html_parts->team_options}
							</select>
						<label><br />
						<label>
							<span style='display: inline-block; width: 120px;'></span>
							<input type='submit' name='add[submit]' style='width: 200px;' value='Add match' />
						</label>
					</fieldset>
				</form>

				<form action='#' method='post'>
					<fieldset>
						<legend>
							<h2>Generera Matcher</h2>
						</legend>
						<label>
							<span style='display: inline-block; width: 120px;'></span>
							<input type='submit' name='generate[group]' style='width: 200px;' value='Genererar Gruppmatcher' />
						</label>
					</fieldset>
				</form>
			HTML_BLOCK;
    }

    /**
     * @throws \RuntimeException
     * @throws \JsonException
     */
    public static function game_edit_match_page(): void
    {
        global $wpdb;
        $safe_post = self::html_encode_object($_POST);
        $html_parts = (object)[];

        if (empty($_GET['id'])) {
            throw new \RuntimeException('No id');
        }
        if (isset($safe_post->home_team_id)) {
            $set_parts = [];
            $set_parts[] = 'home_team_id = ' . (int)$_POST['home_team_id'];
            $set_parts[] = 'away_team_id = ' . (int)$_POST['away_team_id'];
            $set_parts[] = "home_team_description = '" . esc_sql($_POST['home_team_description']) . "'";
            $set_parts[] = "away_team_description = '" . esc_sql($_POST['away_team_description']) . "'";
            $query = 'UPDATE game_matches SET  ' . implode(', ', $set_parts) . ' WHERE match_id = ' . (int)$_GET['id'];
            $result = $wpdb->query($query);
            if($result === false) {
                throw new \RuntimeException('query failed');
            }
        }
        if (isset($safe_post->time)) {
            $set_parts = [];
            $set_parts[] = 'match_id = ' . (int)$_GET['id'];
            if (!$_POST['time']) {
                $query = 'DELETE FROM game_match_time WHERE ' . implode(' AND ', $set_parts);
            } else {
                $set_parts[] = 'field_id = ' . (int)$_POST['field'];
                $set_parts[] = 'match_time = ' . date("'Y-m-d H:i'", strtotime($_POST['time']));
                $query = 'INSERT INTO game_match_time SET  ' . implode(', ', $set_parts) . ", match_status = 'QUEUE' ON DUPLICATE KEY UPDATE " . implode(', ', $set_parts);
            }
            $result = $wpdb->query($query);
            if($result === false) {
                throw new \RuntimeException('query failed');
            }
        }
        if ($safe_post->reset->action) {
            $result = $wpdb->update(
                'game_match_time',
                [
                    'match_status' => 'QUEUE',

                ],
                [
                    'match_id' => $_GET['id'],
                ],
                [
                    '%s',
                ],
                [
                    '%d',
                ]
            );
            if($result === false) {
                throw new \RuntimeException('update failed');
            }
            $result = $wpdb->delete(
                'game_results',
                [
                    'match_id' => $_GET['id'],
                ]
            );
            if($result === false) {
                throw new \RuntimeException('delete failed');
            }
        }

        $match_id = (int)$_GET['id'];
        /** @var MatchWithTime[] $matcher */
        $query = <<<SQL_BLOCK
				SELECT
					game_matches.*,
					game_match_time.*,
					game_referees.*,
					game_results.*
				FROM game_matches 
				   LEFT JOIN game_match_time USING (match_id) 
				   LEFT JOIN game_match_referees USING (match_id) 
				   LEFT JOIN game_referees USING (referee_id)
				   LEFT JOIN game_results USING (match_id, referee_id)
				WHERE match_id = {$match_id}
			SQL_BLOCK;
        $matcher = $wpdb->get_results($query, OBJECT_K);
        if (empty($matcher)) {
            throw new \RuntimeException('Bad id');
        }
        $match = array_values($matcher)[0];
        /** @var MatchWithTime $safe_match */
        $safe_match = self::html_encode_object($match);
        $query = <<<'SQL_BLOCK'
				SELECT
					game_teams.team_id,
					game_teams.team_name,
					game_classes.class_name,
					game_groups.group_name
				FROM game_teams
				   LEFT JOIN game_classes USING (class_id)
				   LEFT JOIN game_groups USING (group_id)
				ORDER BY
					IF(game_teams.group_id IS NULL, 0, 1),
					game_teams.class_id,
					game_teams.group_id,
					game_teams.team_name
			SQL_BLOCK;
        /** @var Team[] $teams */
        $teams = $wpdb->get_results($query, OBJECT_K);

        $html_parts->team_options = '';
        foreach ($teams as $team) {
            /** @var Team $safe_team */
            $safe_team = self::html_encode_object($team);
            $html_parts->team_options .= "<option value='{$safe_team->team_id}'>{$safe_team->team_name}</option>";
        }

        $html_parts->team_options_home = str_replace(
            "<option value='{$match->home_team_id}'>",
            "<option value='{$match->home_team_id}' selected='selected'>",
            $html_parts->team_options,
        );

        $html_parts->team_options_away = str_replace(
            "<option value='{$match->away_team_id}'>",
            "<option value='{$match->away_team_id}' selected='selected'>",
            $html_parts->team_options,
        );

        $html_parts->debug = '<pre>' . self::html_encode(json_encode($match, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</pre>';

        echo <<<HTML_BLOCK
				<form action='#' method='post'>
					<label>
						<span>ID: </span>
						{$safe_match->match_id}
					</label><br />
					<label>
						<span>Home: </span>
						<select name='home_team_id'>
							<option value=''>-- no team --</option>
							{$html_parts->team_options_home}
						</select>
					</label>
					<label>
						<input name="home_team_description" value="{$safe_match->home_team_description}" />
					</label><br />
					<label>
						<span>Away: </span>
						<select name='away_team_id'>
							<option value=''>-- no team --</option>
							{$html_parts->team_options_away}
						</select>
					</label>
					<label>
						<input name='away_team_description' value='{$safe_match->away_team_description}' />
					</label><br />
					<label>
						<span>Plan: </span>
						<input name='field' value='{$safe_match->field_id}' />
					</label><br />
					<label>
						<span>Time: </span>
						<input name='time' value='{$safe_match->match_time}' />
					</label><br />
					<label>
						<span></span>
						<input type="submit" value="Save" />
					</label><br />
				</form>
				{$html_parts->debug}
				<form action='#' method='post'>
					<input type='submit' name='reset[action]' value='Reset score / status' />
				</form>
			HTML_BLOCK;
    }

    /**
     * @return void
     */
    public static function game_referees_page(): void
    {
        global $wpdb;
        $safe_post = self::html_encode_object($_POST);
        $html_parts = (object)[];

        if (!empty($safe_post->add->action)) {
            $result = $wpdb->insert(
                'game_referees',
                [
                    'referee_code' => $_POST['add']['referee_code'],
                    'referee_name' => $_POST['add']['referee_name'],
                ],
                [
                    '%s',
                    '%s',
                ]
            );
            if ($result) {
                echo '<p>Domare tillagd.</p>';
            } else {
                echo '<p>Misslyckades lägga till domare.</p>';
            }
        }
        $query = <<<'SQL_BLOCK'
				SELECT
					game_referees.*,
					COUNT(game_match_referees.referee_id) AS c
				FROM game_referees
					LEFT JOIN game_match_referees USING (referee_id)
				GROUP BY referee_id
				ORDER BY referee_code
			SQL_BLOCK;
        /** @var RefereeCount[] $referees */
        $referees = $wpdb->get_results($query, OBJECT_K);
        if ($referees) {
            $html_parts->referee_tbody = '';
            foreach ($referees as $referee) {
                /** @var RefereeCount $safe_referee */
                $safe_referee = self::html_encode_object($referee);
                $html_parts->referee_tbody .= <<<HTML_BLOCK
						<tr>
							<td>{$safe_referee->referee_code}</td>
							<td>{$safe_referee->referee_name}</td>
							<td>{$safe_referee->c}</td>
						</tr>
					HTML_BLOCK;
            }
        } else {
            $html_parts->referee_tbody = <<<'HTML_BLOCK'
					<tr>
						<td colspan='2'>Inga domare</td>
					</tr>
				HTML_BLOCK;
        }

        echo <<<HTML_BLOCK
				<style>
					TABLE.puggan_table TD, TABLE.puggan_table TH
					{
						padding: 3px 8px;
						border: solid black 1px;
					}
					TABLE.puggan_table
					{
						border: solid gray 2px;
					}
				</style>
				<h2>Domare</h2>
				<table class='puggan_table'>
					<thead>
						<tr>
							<th>Kod</th>
							<th>Namn</th>
							<th>Matcher</th>
						</tr>
					</thead>
					<tbody>
						{$html_parts->referee_tbody}
					</tbody>
				</table>
				<form action='#' method='post'>
					<fieldset>
						<legend>
							<h2>Lägg till domare</h2>
						</legend>
						<label>
							<span style='display: inline-block; width: 120px;'>Kod:</span>
							<input name='add[referee_code]' style='width: 200px;' />
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'>Namn:</span>
							<input name='add[referee_name]' style='width: 200px;' />
						</label><br />
						<label>
							<span style='display: inline-block; width: 120px;'></span>
							<input type='submit' name='add[action]' style='width: 200px;' value='Lägg till domare' />
						</label>
					</fieldset>
				</form>
			HTML_BLOCK;
    }
    //</editor-fold>
}
