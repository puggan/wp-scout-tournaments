CREATE TABLE game_fields
(
	field_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	field_name VARCHAR(200) NOT NULL,
	PRIMARY KEY (field_id)
);

INSERT INTO game_fields SET field_name = 'Vänster Plan';
INSERT INTO game_fields SET field_name = 'Höger Plan';

CREATE TABLE game_classes
(
	class_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	class_name VARCHAR(200) NOT NULL,
	PRIMARY KEY (class_id)
);

INSERT INTO game_classes SET class_name = 'Utmanare';
INSERT INTO game_classes SET class_name = 'Rover';
INSERT INTO game_classes SET class_name = 'Mix';

CREATE TABLE game_groups
(
	group_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	class_id BIGINT UNSIGNED NOT NULL,
	group_name VARCHAR(200) NOT NULL,
	PRIMARY KEY (group_id),
	KEY (class_id)
);

CREATE TABLE game_teams
(
	team_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	class_id BIGINT UNSIGNED NOT NULL,
	group_id BIGINT UNSIGNED NULL,
	team_name VARCHAR(200) NOT NULL,
	PRIMARY KEY (team_id),
	KEY (class_id),
	KEY (group_id)
);

CREATE TABLE game_playoffs
(
	playoff_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	class_id BIGINT UNSIGNED NOT NULL,
	playoff_name VARCHAR(200) NOT NULL,
	team_count BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY (playoff_id),
	KEY (class_id)
);

CREATE TABLE game_playoffs_team
(
	playoffs_team_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	playoff_id BIGINT UNSIGNED NOT NULL,
	playoffs_team_position BIGINT UNSIGNED NOT NULL,
	team_id BIGINT UNSIGNED NULL,
	PRIMARY KEY (playoffs_team_id),
	KEY (playoff_id, playoffs_team_position),
	KEY (playoff_id, team_id)
);

CREATE TABLE game_matches
(
	match_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	match_type ENUM('GROUP','PLAYOFF','OTHER') NULL,
	match_type_id BIGINT UNSIGNED NULL,
	home_team_id BIGINT NULL,
	away_team_id BIGINT NULL,
	home_team_description VARCHAR(255) NULL,
	away_team_description VARCHAR(255) NULL,
	match_display_name tinytext NOT NULL,
	PRIMARY KEY (match_id),
	KEY (home_team_id),
	KEY (away_team_id)
);

CREATE TABLE game_match_time
(
	match_id BIGINT UNSIGNED NOT NULL,
	field_id BIGINT UNSIGNED NOT NULL,
	match_time DATETIME NOT NULL,
	match_status ENUM('QUEUE','STARTED','PLAYED') NULL,
	PRIMARY KEY (match_id)
);

CREATE TABLE game_breaks
(
	break_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	break_name VARCHAR(200) NOT NULL,
	break_from DATETIME NOT NULL,
	break_to DATETIME NOT NULL,
	break_hidden TINYINT NOT NULL,
	PRIMARY KEY (break_id),
	KEY (break_from),
	KEY (break_to)
);

CREATE TABLE game_field_breaks
(
	field_id BIGINT UNSIGNED NOT NULL,
	break_id BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY (field_id, break_id),
	KEY (break_id)
);

CREATE TABLE game_referees
(
	referee_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	referee_code VARCHAR(10) NULL,
	referee_name VARCHAR(200) NOT NULL,
	PRIMARY KEY (referee_id),
	UNIQUE (referee_code)
);

CREATE TABLE game_match_referees
(
	match_id BIGINT UNSIGNED NOT NULL,
	referee_id BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY (match_id, referee_id)
);

CREATE TABLE game_results
(
	match_id BIGINT UNSIGNED NOT NULL,
	referee_id BIGINT UNSIGNED NOT NULL,
	home_goals BIGINT UNSIGNED NOT NULL,
	away_goals BIGINT UNSIGNED NOT NULL,
	done TINYINT UNSIGNED NOT NULL,
	PRIMARY KEY (match_id),
	KEY (referee_id)
);
