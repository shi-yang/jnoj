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
                榜单将按解题数从多到少排序，如果解题数相同，则按得分总和从高到低排序。
            </th>
            <th>要想拿高分：争取拿一血，争取用最少提交来解题，争取最快解题</th>
        </tr>
        <tr>
            <th>ICPC</th>
            <th>每道试题用时将从竞赛开始到试题解答被判定为正确为止，其间每一次提交运行结果被判错误的话将被加罚20分钟时间，未正确解答的试题不记时。排名方式由解题数从多到少排序，如果解题数相同，则按时间从少到多排序</th>
            <th>ACM-ICPC 赛制比赛榜单排名方式</th>
        </tr>
        <tr>
            <th>作业</th>
            <th>按正确解答的题目数量由多到少排序。不计罚时。比赛过程中用户可以查看出错信息。</th>
            <th></th>
        </tr>
        <tr>
            <th>OI</th>
            <th>对所有数据点进行测试，根据题目数据配置文件来按数据点算分。比赛结束前选手无法得知自己的过题情况。测评总分：每道题最后一次提交的得分之和。订正总分：每道题所有提交最高得分之和。</th>
            <th>OI 赛制比赛榜单排名方式</th>
        </tr>
        <tr>
            <th>IOI</th>
            <th>对所有数据点进行测试，根据题目数据配置文件来按数据点算分。测评总分：每道题最后一次提交的得分之和。订正总分：每道题所有提交最高得分之和。</th>
            <th>IOI 赛制比赛榜单排名方式</th>
        </tr>
        </tbody>
    </table>

    <hr>
    <h3>关于线上赛与线下赛的区别</h3>
    <p>线下赛即现场参加比赛，是为了举办现场赛而设立的一个场景．对于这两者来说，它们的区别在于：</p>
    <ul>
        <li>线下赛在比赛页面会有代码打印链接，用于给参赛选手提供代码打印服务的功能．线上赛无此功能．</li>
        <li>线下赛的参赛帐号只能在后台管理界面添加，需要为比赛批量创建帐号，前台界面不能注册比赛．
            线上赛无此功能．线上赛在比赛结束前任何时刻都可以注册比赛．</li>
        <li>线下赛场景中批量生成的帐号会被禁止修改个人信息．</li>
        <li>线下赛所添加的参赛帐号中，非批量生成的帐号为打星参赛模式（打星参赛即不参与比赛排名）．线上赛无此功能．</li>
        <li>线下赛可以滚榜（即比赛结束后逐步揭露榜单排名的视觉效果）．线上赛无此功能．</li>
    </ul>

    <hr>
    <h3>关于排位赛</h3>
    <p>
        参加排位赛后，将得到一定积分，依据积分的多少来决定段位。排位赛的榜单在 <?= \yii\helpers\Html::a('排行榜', ['/rating'], ['target' => '_blank']) ?> 页面。
    </p>
    <p>
        如果参加了比赛，但没有提交过任何题目，不会计算比赛积分。
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
            <th>超凡大师 [Challenger]</th>
            <th>Between 2150 and 2399</th>
        </tr>
        <tr>
            <th>最强王者 [Master]</th>
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
