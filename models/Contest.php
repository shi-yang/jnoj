<?php

namespace app\models;

use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "{{%contest}}".
 *
 * @property int $id
 * @property string $title
 * @property string $start_time
 * @property string $end_time
 * @property string $lock_board_time
 * @property string $status
 * @property string $description
 * @property string $editorial
 * @property int $type
 * @property int $scenario
 * @property int $created_by
 */
class Contest extends \yii\db\ActiveRecord
{
    /**
     * 单人赛榜单中一道题基础分数
     */
    const BASIC_SCORE = 500;

    /**
     * 第一次参加排位赛的初始分数
     */
    const RATING_INIT_SCORE = 1149;

    /**
     * 比赛的状态信息
     */
    const STATUS_NOT_START = 0;
    const STATUS_RUNNING = 1;
    const STATUS_ENDED = 2;

    /**
     * 比赛的类型
     */
    const TYPE_EDUCATIONAL = 0;
    const TYPE_RANK_SINGLE = 1;
    const TYPE_RANK_GROUP  = 2;
    const TYPE_HOMEWORK    = 3;

    /**
     * 是否可见
     */
    const STATUS_HIDDEN = 0;
    const STATUS_VISIBLE = 1;

    /**
     * 线上线下场景
     */
    const SCENARIO_ONLINE = 0;
    const SCENARIO_OFFLINE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contest}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time', 'lock_board_time'], 'safe'],
            [['description', 'editorial'], 'string'],
            [['id', 'status', 'type', 'scenario', 'created_by'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Contest ID'),
            'title' => Yii::t('app', 'Title'),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
            'lock_board_time' => Yii::t('app', 'Lock Board Time'),
            'editorial' => Yii::t('app', 'Editorial'),
            'description' => Yii::t('app', 'Description'),
            'status' => Yii::t('app', 'Status'),
            'type' => Yii::t('app', 'Type'),
            'scenario' => Yii::t('app', 'Scenario')
        ];
    }

    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_by = Yii::$app->user->id;
            }
            return true;
        } else {
            return false;
        }
    }

    public function getSolutions()
    {
        return $this->hasMany(Solution::className(), ['problem_id' => 'problem_id'])
            ->viaTable(ContestProblem::tableName(), ['contest_id' => 'id']);
    }

    public function getType()
    {
        switch ($this->type) {
            case Contest::TYPE_EDUCATIONAL:
                $res = Yii::t('app', 'Educational');
                break;
            case Contest::TYPE_RANK_SINGLE:
                $res = Yii::t('app', 'Single Ranked');
                break;
            case Contest::TYPE_HOMEWORK;
                $res = Yii::t('app', 'Homework');
                break;
            default:
                $res = "null";
        }
        return $res;
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * 返回比赛的状态，还没开始，正在进行，已经结束
     * @param $description boolean 是否显示文字描述
     * @return mixed
     */
    public function getRunStatus($description = false)
    {
        $start_time = strtotime($this->start_time);
        $end_time = strtotime($this->end_time);
        $current_time = time();
        if ($description) {
            if ($start_time > $current_time) {
                return Yii::t('app', 'Not started yet');
            } else if ($start_time <= $current_time && $current_time <= $end_time) {
                return Yii::t('app', 'Running');
            } else {
                return Yii::t('app', 'Ended');
            }
        } else {
            if ($start_time > $current_time) {
                return Contest::STATUS_NOT_START;
            } else if ($start_time <= $current_time && $current_time <= $end_time) {
                return Contest::STATUS_RUNNING;
            } else {
                return Contest::STATUS_ENDED;
            }
        }
    }

    public static function getContestList()
    {
        $res = (new Query())->select('id, title')
            ->from('{{%contest}}')
            ->orderBy('id DESC')
            ->all();
        $list = ['' => 'None'];
        foreach ($res as $key => $value) {
            $list[$value['id']] = $value['id'] . ' [' . $value['title'] . ']';
        }
        return $list;
    }

    public function getAnnouncements()
    {
        return $this->hasMany(ContestAnnouncement::className(), ['contest_id' => 'id']);
    }

    /**
     * 获取比赛问题
     */
    public function getProblems()
    {
        return Yii::$app->db->createCommand('
            SELECT `p`.`title`, `p`.`id` AS `problem_id`, `c`.`num`
            FROM `problem` `p`
            LEFT JOIN `contest_problem` `c` ON `c`.`contest_id`=:cid
            WHERE p.id=c.problem_id
            ORDER BY `c`.`num`
        ', [':cid' => $this->id])->queryAll();
    }

    /**
     * 获取用户的提交
     * @return array
     */
    public function getUsersSolution()
    {
        return Yii::$app->db->createCommand('
            SELECT `username`, `nickname`, `result`, `s`.`problem_id`, `s`.`created_at`, `s`.`id`
            FROM `solution` `s` LEFT JOIN `user` `u` ON u.id=s.created_by
            WHERE `contest_id`=:id AND `s`.`created_at` <= :endtime ORDER BY `s`.`id`
        ', [':id' => $this->id, ':endtime' => $this->end_time])->queryAll();
    }

    /**
     * 获取比赛问题数目
     * @return int
     */
    public function getProblemCount()
    {
        return Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%contest_problem}} WHERE contest_id=' . $this->id)->queryScalar();
    }

    /**
     * 获取比赛用户
     * @return array
     */
    public function getContestUser()
    {
        return Yii::$app->db->cache(function ($db) {
            return $db->createCommand('
                SELECT `u`.`username`, `u`.`nickname`, `p`.`student_number`, `u`.`id` as `user_id`
                FROM `user` `u`
                LEFT JOIN `contest_user` `c` ON `c`.`contest_id`=:cid
                LEFT JOIN `user_profile` `p` ON `p`.`user_id`=`c`.`user_id`
                WHERE u.id=c.user_id ORDER BY `c`.`id`
            ', [':cid' => $this->id])->queryAll();
        }, 60);
    }

    public function getContestUserCount()
    {
        return Yii::$app->db->createCommand('
            SELECT COUNT(1) FROM {{%contest_user}} WHERE contest_id=:cid
        ', [':cid' => $this->id])->queryScalar();
    }

    /**
     * 获取比赛排名数据
     * @param $lock bool
     * @return array
     */
    public function getRankData($lock = true)
    {
        $users_solution_data = $this->getUsersSolution();
        $users = $this->getContestUser();
        $result = [];
        $first_blood = [];
        $submit_count = [];
        $count = count($users_solution_data);
        $start_time = $this->start_time;
        $end_time = $this->end_time;
        $lock_time = 0x7fffffff;

        foreach ($users as $user) {
            $result[$user['username']]['time'] = 0;
            $result[$user['username']]['solved'] = 0;
            $result[$user['username']]['submit'] = 0;
            $result[$user['username']]['nickname'] = $user['nickname'];
            $result[$user['username']]['student_number'] = $user['student_number'];
            $result[$user['username']]['user_id'] = $user['user_id'];
        }

        if (!empty($this->lock_board_time)) {
            $lock_time = $this->lock_board_time;
        }

        for ($i = 0; $i < $count; $i++) {
            $row = $users_solution_data[$i];
            $user = $row['username'];
            $pid = $row['problem_id'];
            $created_at = $row['created_at'];

            // 封榜，比赛结束 120 分钟后解榜
            if ($lock && strtotime($lock_time) <= strtotime($created_at) && time() <= strtotime($end_time) + 120 * 60)
                break;

            // 初始化数据信息
            if (!isset($submit_count[$pid]['solved']))
                $submit_count[$pid]['solved'] = 0;
            if (!isset($submit_count[$pid]['submit']))
                $submit_count[$pid]['submit'] = 0;

            // AC 时间
            if (!isset($result[$user]['ac_time'][$pid]))
                $result[$user]['ac_time'][$pid] = 0;
            // 没 AC 的次数
            if (!isset($result[$user]['wa_count'][$pid]))
                $result[$user]['wa_count'][$pid] = 0;
            // 正在测评
            if (!isset($result[$user]['pending'][$pid]))
                $result[$user]['pending'][$pid] = 0;
            // 最快解题
            if (!isset($first_blood[$pid]))
                $first_blood[$pid] = '';

            // 已经 Accepted
            if ($result[$user]['ac_time'][$pid] > 0) {
                continue;
            }

            $result[$user]['submit']++;
            $submit_count[$pid]['submit']++;
            // Accept
            if ($row['result'] == Solution::OJ_AC) {
                $submit_count[$pid]['solved']++;

                if (empty($first_blood[$pid])) {
                    if ($this->type == self::TYPE_RANK_SINGLE) {
                        $result[$user]['time'] += 0.1 * self::BASIC_SCORE;
                    }
                    $first_blood[$pid] = $user;
                }
                $sec = strtotime($created_at) - strtotime($start_time);
                $result[$user]['ac_time'][$pid] = $sec;

                ++$result[$user]['solved'];

                if ($this->type == self::TYPE_RANK_SINGLE) {
                    $result[$user]['time'] += 0.5 * self::BASIC_SCORE + max(0, self::BASIC_SCORE - 2 * $sec - $result[$user]['wa_count'][$pid] * 50);
                } else {
                    $result[$user]['time'] += $sec + $result[$user]['wa_count'][$pid] * 60 * 20;
                }
                //Other cases
            } else {
                if ($row['result'] <= 3) {
                    ++$result[$user]['pending'][$pid];
                } else {
                    $result[$user]['pending'][$pid] = 0;
                }
                ++$result[$user]['wa_count'][$pid];
            }
        }

        foreach ($result as $k => &$v) {
            $v['username'] = $k;
        }

        usort($result, function($a, $b) {
            if ($a['solved'] != $b['solved'])
                return $a['solved'] < $b['solved'];
            else if ($a['time'] != $b['time'])
                return $a['time'] > $b['time'];
            else
                return $a['submit'] < $b['submit'];
        });

        return [
            'rank_result' => $result,
            'submit_count' => $submit_count,
            'first_blood' => $first_blood
        ];
    }

    /**
     * 判断用户是否参加比赛
     * @return boolean
     */
    public function isUserInContest()
    {
        return Yii::$app->db->createCommand('SELECT count(1) FROM {{%contest_user}} WHERE user_id=:uid AND contest_id=:cid', [
            ':uid' => Yii::$app->user->id,
            ':cid' => $this->id
        ])->queryScalar();
    }

    /**
     * 通过题目在比赛中的序号来获取题目信息
     * @param $id
     * @return array|bool
     */
    public function getProblemById($id)
    {
        return Yii::$app->db->createCommand(
            "SELECT `cp`.`num`, `p`.`title`, `p`.`id`, `p`.`description`, 
            `p`.`input`, `p`.`output`, `p`.`sample_input`, `p`.`sample_output`, `p`.`hint`, `p`.`time_limit`, 
            `p`.`memory_limit` 
            FROM `problem` `p` 
            LEFT JOIN `contest_problem` `cp` ON cp.problem_id=p.id 
            WHERE (`cp`.`num`={$id}) AND (`cp`.`contest_id`={$this->id})"
        )->queryOne();
    }

    public function getClarifies()
    {
        return $this->hasMany(Discuss::className(), ['contest_id' => 'id']);
    }

    /**
     * 计算某个比赛的Rating
     *
     * @see https://en.wikipedia.org/wiki/Elo_rating_system
     */
    public function calRating()
    {
        $users = Yii::$app->db->createCommand('
            SELECT `u`.`id` as `user_id`, `rating`, `rating_change`
            FROM `user` `u`
            LEFT JOIN `contest_user` `c` ON `c`.`contest_id`=:cid
            WHERE u.id=c.user_id ORDER BY `c`.`id`
        ', [':cid' => $this->id])->queryAll();

        $rankResult = $this->getRankData()['rank_result'];
        $tmp = [];
        foreach ($rankResult as $k => $user) {
            $tmp[$user['user_id']] = ['solved' => $user['solved'], 'rank' => $k];
        }
        $rankResult = $tmp;

        $userCount = 0;
        foreach ($users as $user) {
            if ($rankResult[$user['user_id']]['solved'] != 0) {
                //如果该场比赛已经计算过了，就不再计算
                if ($user['rating_change'] != NULL) {
                    return;
                }
                $userCount++;
            }
        }

        foreach ($users as $user) {
            // 解题数为 0 的用户不参与计算
            if ($rankResult[$user['user_id']]['solved'] == 0) {
                continue;
            }
            $old = $user['rating'] == NULL ? self::RATING_INIT_SCORE : $user['rating'];
            $exp = 0;
            if ($user['rating']) {
                foreach ($users as $u) {
                    if ($user['user_id'] != $u['user_id'] && $rankResult[$u['user_id']]['solved'] > 0) {
                        $exp += 1.0 / (1.0 + pow(10, ($u['rating'] ? $u['rating'] : self::RATING_INIT_SCORE) - $old) / 400.0);
                    }
                }
            } else {
                $exp = intval($userCount / 2);
            }

            // 此处 ELO 算法中 K 的合理性有待改进
            if ($old < 1150) {
                $eloK = 2;
            } else if ($old < 1400) {
                $eloK = 3;
            } else if ($old < 1650) {
                $eloK = 4;
            } else if ($old < 1900) {
                $eloK = 5;
            } else if ($old < 2150) {
                $eloK = 6;
            } else {
                $eloK = 7;
            }
            $newRating = intval($old + $eloK * (($userCount - $rankResult[$user['user_id']]['rank'] - 1) - $exp));
            Yii::$app->db->createCommand()->update('{{%user}}', [
                'rating' => $newRating
            ], ['id' => $user['user_id']])->execute();
            Yii::$app->db->createCommand()->update('{{contest_user}}', [
                'rating_change' => $newRating - $old,
                'rank' => $rankResult[$user['user_id']]['rank'] + 1
            ], ['user_id' => $user['user_id'], 'contest_id' => $this->id])->execute();
        }
    }
}
