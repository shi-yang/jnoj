<div class="table-responsive">

    <h3>比赛榜单计分方式</h3>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th style="min-width: 130px">Contest</th>
            <th>比赛榜单计分方式</th>
            <th>备注</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>单人</th>
            <th>
                比赛中的每道题目，都有一个分数 N (N = 500)，正确解答一道题目，会得到一个基础分数，基础分数为 50%N．最快答题可以得到 10%N．
                每次不为 AC 的提交，会扣50分．题目分数会随时间线性减少，每分钟减少 2 分．<br>
                得分计算方式为：<br>
                    非最快答题：x = 50% * N + max(0, N - 2 * 分钟 - 50 * 未AC次数)<br>
                    最快答题：x = 50% * N + max(0, N - 2 * 分钟 - 50 * 未AC次数) + 10% * N<br>
                榜单将按所有题目得分总和从高到低排序。
            </th>
            <th>要想拿高分：争取拿一血，争取用最少提交来解题，正确最快解题</th>
        </tr>
        <tr>
            <th>队伍</th>
            <th>每道试题用时将从竞赛开始到试题解答被判定为正确为止，其间每一次提交运行结果被判错误的话将被加罚20分钟时间，未正确解答的试题不记时。排名方式由解题数从多到少排序，如果解题数相同，则按时间从少到多排序</th>
            <th>AMC-ICPC 赛制比赛榜单排名方式</th>
        </tr>
        </tbody>
    </table>


    <hr>
    <h3>关于排位赛</h3>
    <p>
        参加排位赛后，将得到一定积分，依据积分的多少来决定段位。排位赛的榜单在 <?= \yii\helpers\Html::a('排行榜', ['/rating'], ['target' => '_blank']) ?> 页面。
    </p>
    <p>
        如果参加了比赛，但没有解决任何一道题目，不会被计算积分。只有在一场比赛中解决了一道或一道以上的题目才会计算积分。
    </p>
    <hr>

    <h3>段位表</h3>
    <p>未参加过任何比赛时，第一场比赛初始积分：1149</p>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th style="min-width: 130px">段位名称</th>
            <th>积分</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>青铜 [Bronze]</th>
            <th>Between 0 and 1149</th>
        </tr>
        <tr>
            <th>白银 [Silver]</th>
            <th>Between 1150 and 1399</th>
        </tr>
        <tr>
            <th>黄金 [Gold]</th>
            <th>Between 1400 and 1649</th>
        </tr>
        <tr>
            <th>铂金 [Platinum]</th>
            <th>Between 1650 and 1899</th>
        </tr>
        <tr>
            <th>钻石 [Diamond]</th>
            <th>Between 1900 and 2149</th>
        </tr>
        <tr>
            <th>王者 [Master]</th>
            <th>Between 2150 and 2399</th>
        </tr>
        <tr>
            <th>大师 [Challenger]</th>
            <th>2400 and above</th>
        </tr>
        </tbody>
    </table>
    <hr>
    <h3>参加排位赛比赛结束后积分计算方式</h3>
    <p>采用 Elo Ranking 算法，具体见:
        <a href="https://en.wikipedia.org/wiki/Elo_rating_system" target="_blank">
            https://en.wikipedia.org/wiki/Elo_rating_system
        </a>
    </p>
</div>
