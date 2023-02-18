<?php

namespace PHPDoc\Models {

    /**
     * Class Field
     * @package PHPDoc\Models
     * @property string field_id
     * @property string field_name
     */
    class Field
    {
    }

    /**
     * Class GameClass
     * @package PHPDoc\Models
     * @property string class_id
     * @property string class_name
     */
    class GameClass
    {
    }

    /**
     * Class GameGroup
     * @package PHPDoc\Models
     * @property string group_id
     * @property string class_id
     * @property string group_name
     */
    class GameGroup
    {
    }

    /**
     * Class Team
     * @package PHPDoc\Models
     * @property string team_id
     * @property string class_id
     * @property string|null group_id
     * @property string team_name
     */
    class Team
    {
    }

    /**
     * Class Playoffs
     * @package PHPDoc\Models
     * @property string playoff_id
     * @property string class_id
     * @property string playoff_name
     * @property string team_count
     */
    class Playoffs
    {
    }

    /**
     * Class PlayoffsTeam
     * @package PHPDoc\Models
     * @property string playoffs_team_id
     * @property string playoff_id
     * @property string playoffs_team_position
     * @property string|null team_id
     */
    class PlayoffsTeam
    {
    }

    /**
     * Class Match
     * @package PHPDoc\Models
     * @property string match_id
     * @property string|null match_type
     * @property string|null match_type_id
     * @property string|null home_team_id
     * @property string|null away_team_id
     * @property string|null home_team_description
     * @property string|null away_team_description
     * @property string match_display_name
     */
    class GameMatch
    {
    }

    /**
     * Class MatchTime
     * @package PHPDoc\Models
     * @property string match_id
     * @property string field_id
     * @property string match_time
     * @property string|null match_status
     */
    class MatchTime
    {
    }

    /**
     * Class
     * @package PHPDoc\Models
     * @property string break_id
     * @property string break_name
     * @property string break_from
     * @property string break_to
     * @property string break_hidden
     */
    class GameBreak
    {
    }

    /**
     * Class FieldBreak
     * @package PHPDoc\Models
     * @property string field_id
     * @property string break_id
     */
    class FieldBreak
    {
    }

    /**
     * Class Referee
     * @package PHPDoc\DbResults
     * @property string referee_id
     * @property string|null referee_code
     * @property string referee_name
     */
    class Referee
    {
    }

    /**
     * Class MatchReferre
     * @package PHPDoc\Models
     * @property string match_id
     * @property string referee_id
     */
    class MatchReferre
    {
    }

    /**
     * Class Result
     * @package PHPDoc\Models
     * @property string match_id
     * @property string referee_id
     * @property string home_goals
     * @property string away_goals
     * @property string done
     */
    class Result
    {
    }

    /**
     * Class TeamAutoselect
     * @package PHPDoc\Models
     * @property string match_id
     * @property string side HOME, AWAY
     * @property string auto_type MATCH, GROUP
     * @property string type_id
     * @property string position
     */
    class TeamAutoselect
    {
    }
}

namespace PHPDoc\DbResults {

    use PHPDoc\Models\GameClass;
    use PHPDoc\Models\GameGroup;
    use PHPDoc\Models\GameMatch;
    use PHPDoc\Models\Referee;

    /**
     * Class RefereesCount
     * @package PHPDoc\DbResults
     * @property string c
     */
    class RefereeCount extends Referee
    {
    }

    /**
     * Class ClassCount
     * @package PHPDoc\DbResults
     * @property string teams
     */
    class ClassCount extends GameClass
    {
    }

    /**
     * Class Team
     * @package PHPDoc\DbResults
     * @property string team_id
     * @property string team_name
     * @property string class_name
     * @property string group_name
     * @property string attendance_status
     */
    class Team
    {
    }

    /**
     * Class GFTeams
     * @package PHPDoc\DbResults
     * @property string team_name
     * @property string team_members
     * @property string lead_id
     */
    class GFTeam
    {
    }

    /**
     * Class GFClasses
     * @package PHPDoc\DbResults
     * @property string lead_id
     * @property string class_name
     */
    class GFClass
    {
    }

    /**
     * Class GFTeamWithClass
     * @package PHPDoc\DbResults
     * @property string class_id
     */
    class GFTeamWithClass extends GFTeam
    {
    }

    /**
     * Class GroupsWithTeams
     * @package PHPDoc\DbResults
     * @property string class_name
     * @property string team_count
     * @property string teams
     */
    class GroupWithTeams extends GameGroup
    {
    }

    /**
     * Class MatchesWithExtra
     * @package PHPDoc\DbResults
     * @property string field_id
     * @property string match_time
     * @property string|null match_status
     */
    class MatchWithTime extends GameMatch
    {
    }

    /**
     * Class MatchesWithExtra
     * @package PHPDoc\DbResults
     * @property string match_time
     * @property string field_name
     * @property string referee_name
     * @property string home_goals
     * @property string away_goals
     * @property string match_status
     */
    class MatchWithExtra extends GameMatch
    {
    }

    /**
     * Class AutoCount
     * @package PHPDoc\DbResults
     * @property string auto_type
     * @property string type_id
     * @property string auto_count
     * @property string auto_connected
     */
    class AutoCount
    {
    }

    /**
     * Class TeamPlaceholderCount
     * @package PHPDoc\DbResults
     * @property string team
     * @property string c
     */
    class TeamPlaceholderCount
    {
    }

    /**
     * Class MatchResult
     * @package PHPDoc\DbResults
     *
     * @property string home_team_name
     * @property string away_team_name
     * @property string match_id
     * @property string referee_id
     * @property string home_goals
     * @property string away_goals
     * @property string done
     * @property string|null referee_code
     * @property string referee_name
     * @property string field_id
     * @property string match_time
     * @property string|null match_status
     */
    class MatchResult
    {
    }
}

namespace PHPDoc\DbResults2022 {

    /**
     * @property int $match_id,
     * @property string $match_type,
     * @property string $home_team_name,
     * @property string $away_team_name,
     * @property int $away_goals,
     * @property int $done,
     * @property int $home_goals,
     * @property int $referee_id,
     * @property int $field_id,
     * @property string $match_status,
     * @property string $match_time,
     * @property string $group_name,
     * @property string $class_name
     */
    class NuPage
    {
    }

    /**
     * @property int|string $field_id
     */
    class Breaks extends \PHPDoc\Models\GameBreak
    {}
}