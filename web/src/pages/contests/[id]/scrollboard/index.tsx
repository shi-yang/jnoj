import { getContest, listContestAllSubmissions, listContestProblems, listContestUsers } from '@/api/contest';
import { createRoot } from 'react-dom/client';
import { useRouter } from 'next/router';
import React, { useContext, useEffect, useRef, useState } from 'react';
import Head from 'next/head';
import { Modal, Slider, Tooltip } from '@arco-design/web-react';
import IconButton from '@/components/Layouts/IconButton';
import { GlobalContext } from '@/context';
import { IconMoonFill, IconSunFill } from '@arco-design/web-react/icon';

class User {
  who: string; // 用户
  userId: number; // 用户ID
  solved: number; // 解答数
  isRank: boolean; // 是否参与排名。只有正式选手才参与排名
  penalty: number; // 罚时
  // 题目
  problem?: {
    [key: string]: any
  };
  nowRank: number; // 当前排名
  finalRank: number; // 最终排名
  submitList: Submission[]; // 提交列表
  submitProblemList: { [key: number]: UserProblem }; // 提交题目列表
  unkonwnProblemIdMap: { [key: string]: boolean };
  constructor(who: string, userId: number, isRank: boolean) {
    this.userId = userId;
    this.who = who;
    this.solved = 0;
    this.isRank = isRank;
    this.penalty = 0;
    this.problem = {};
    this.nowRank = 0;
    this.finalRank = 0;
    this.unkonwnProblemIdMap = {};
    this.submitList = [];
    this.submitProblemList = {};
  }
  init(startTime: Date, frozenTime: Date): void {
    this.submitList.sort((a, b) => a.submitTime.getTime() - b.submitTime.getTime());
    for (const submission of this.submitList) {
      let p = this.submitProblemList[submission.problemId];
      if (!p) {
        p = {
          problemId: submission.problemId,
          isAccepted: false,
          penalty: 0,
          acceptedTime: null,
          submitCount: 0,
          isUnknown: false
        };
      }
      if (p.isAccepted)
        continue;
      // 封榜后的提交设置isUnkonwn为true
      if (submission.submitTime.getTime() > frozenTime.getTime()) {
        p.isUnknown = true;
        this.unkonwnProblemIdMap[p.problemId] = true;
      }
      // 编译错误不算提交
      if (submission.verdict != 'COMPILER_ERROR') {
        p.submitCount++;
      }
      p.isAccepted = (submission.verdict === 'CORRECT');
      if (p.isAccepted) {
        p.acceptedTime = submission.submitTime.getTime() - startTime.getTime();
        if (p.acceptedTime < frozenTime.getTime() - startTime.getTime()) {
          p.penalty += Math.floor(p.acceptedTime / 60000) + (p.submitCount - 1) * 20;
          this.solved++;
          this.penalty += p.penalty;
        }
      }
      this.submitProblemList[submission.problemId] = p;
    }
  }
  countUnknownProblem(): number {
    return Object.keys(this.unkonwnProblemIdMap).length;
  }
  updateOneProblem(): boolean {
    for (const index in this.submitProblemList) {
      const p = this.submitProblemList[index];
      if (p.isUnknown) {
        p.isUnknown = false;
        delete this.unkonwnProblemIdMap[p.problemId];
        if (p.isAccepted) {
          p.penalty += Math.floor(p.acceptedTime / 60000) + (p.submitCount - 1) * 20;
          this.solved++;
          this.penalty += p.penalty;
          return true;
        }
        return false;
      }
    }
    return false;
  }
}

interface UserProblem {
  problemId: number; // 题目ID
  isAccepted: boolean; // 是否通过
  penalty: number; // 罚时
  acceptedTime: number; // 通过时间
  submitCount: number; // AC前提交次数，如果AC了，加1
  isUnknown: boolean; // 是否封榜后提交
}

interface Submission {
  submitId: number; // 提交ID
  problemId: number; // 题目ID
  userId: number; // 用户ID
  submitTime: Date; // 提交时间
  verdict: string; // 结果
}

function smoothScrollTo(endX, endY, duration) {
  const startX = window.scrollX;
  const startY = window.scrollY;
  const distanceX = endX - startX;
  const distanceY = endY - startY;
  const startTime = new Date().getTime();

  duration = typeof duration !== 'undefined' ? duration : 400;

  // 缓动函数
  const easeInOutQuart = (time, from, distance, duration) => {
    if ((time /= duration / 2) < 1) return distance / 2 * time * time * time * time + from;
    return -distance / 2 * ((time -= 2) * time * time * time - 2) + from;
  };

  const timer = setInterval(() => {
    const time = new Date().getTime() - startTime;
    const newX = easeInOutQuart(time, startX, distanceX, duration);
    const newY = easeInOutQuart(time, startY, distanceY, duration);

    if (time >= duration) {
      clearInterval(timer);
    }

    window.scrollTo(newX, newY);
  }, 1000 / 60); // 60 fps
};

function animateElement(element, properties, duration, onComplete) {
  const start = performance.now();
  const initialStyles = {};

  // 记录初始样式
  for (const prop in properties) {
      initialStyles[prop] = parseInt(getComputedStyle(element)[prop], 10);
  }

  function animate(time) {
      let progress = (time - start) / duration;
      progress = progress > 1 ? 1 : progress;

      for (const prop in properties) {
          const initialValue = initialStyles[prop];
          const finalValue = properties[prop];
          const newValue = initialValue + (finalValue - initialValue) * progress;
          element.style[prop] = newValue + 'px';
      }

      if (progress < 1) {
          requestAnimationFrame(animate);
      } else {
          if (onComplete) onComplete();
      }
  }

  requestAnimationFrame(animate);
}

function blinkElement(element, speed, callback) {
  if (!element) {
    return;
  }
  var blinkCount = 0;
  function blink() {
    element.style.opacity = element.style.opacity === '0' ? '1' : '0';
    blinkCount++;

    if (blinkCount < 4) {
      setTimeout(blink, speed);
    } else if (callback) {
      callback();
    }
  }
  blink();
}

//设置表头宽度百分比
const rankThWidth = 5; // Rank列宽度百分比
const nameThWidth = 25; // Name列宽度百分比
const solvedThWidth = 4; // Solved列宽度百分比
const penaltyThWidth = 7; // Penalty列宽度百分比
const userHeight = 67;
const headerHeight = 44;

class Board {
  problemCount: number; // 题目数量
  startTime: Date; // 开始时间
  frozenTime: Date; // 封榜时间
  userList: {[key: string]: User}; // 用户列表
  submitList: Submission[]; // 提交列表
  userNowSequence: User[]; // 当前排名，用户ID
  userNextSequence: User[]; // 下一排名，用户ID
  userCount: number; // 用户数量
  displayUserPos: number; // 当前显示用户位置
  noAnimate: boolean; // 是否在运行动画
  problemList: number[];
  constructor(problemCount: number, users: {[key: string]: User}, submissions: Submission[], startTime: Date, frozenTime: Date) {
    this.problemCount = problemCount;
    this.startTime = startTime;
    this.frozenTime = frozenTime;
    this.userList = users;
    this.submitList = submissions;
    this.userNowSequence = [];
    this.userNextSequence = [];
    this.problemList = [];
    this.userCount = Object.keys(users).length;
    this.displayUserPos = 0;
    this.noAnimate = true;
    for (const s of this.submitList) {
      this.userList[s.userId].submitList.push(s);
    }

    for (let i = 0; i < problemCount; i++) {
      this.problemList.push(i);
    }

    for (const u in this.userList) {
      const user = this.userList[u];
      user.init(this.startTime, this.frozenTime);
      this.userNowSequence.push(user);
    }
    this.displayUserPos = this.userCount - 1;
    this.userNowSequence.sort((a, b) => {
      if (a.solved !== b.solved) {
        return b.solved - a.solved;
      }
      if (a.penalty !== b.penalty) {
        return a.penalty - b.penalty;
      }
      return a.userId - b.userId;
    });
    this.userNextSequence = this.userNowSequence.slice(0);
  }
  updateUserSequence(): number {
    const userSequence = this.userNextSequence.slice(0);
    userSequence.sort((a, b) => {
      if (a.solved !== b.solved) {
        return b.solved - a.solved;
      }
      if (a.penalty !== b.penalty) {
        return a.penalty - b.penalty;
      }
      return a.userId - b.userId;
    });
    let toPos = -1;
    for (let i = 0; i < this.userCount; i++) {
      if (this.userNextSequence[i].userId != userSequence[i].userId) {
        toPos = i;
        break;
      }
    }

    this.userNowSequence = this.userNextSequence.slice(0);
    this.userNextSequence = userSequence.slice(0);

    return toPos;
  }
  updateOneUser(): User | null {
    let updateUserPos = this.userCount - 1;
    while (updateUserPos >= 0 && this.userNextSequence[updateUserPos].countUnknownProblem() < 1) {
      updateUserPos--;
    }
    if (updateUserPos >= 0) {
      while (this.userNextSequence[updateUserPos].countUnknownProblem() > 0) {
        this.userNextSequence[updateUserPos].updateOneProblem();
        return this.userNextSequence[updateUserPos];
      }
    }
    return null;
  }
  updateUserStatus(user: User): void {
    for (const index in user.submitProblemList) {
      const p = user.submitProblemList[index];
      let problemHTML = null;
      if (p.isUnknown) {
        problemHTML = (<span className='label label-warning'>{p.submitCount}</span>);
      } else {
        if (p.isAccepted) {
          problemHTML = (<span className='label label-success'>{p.submitCount}</span>);
        } else {
          problemHTML = (<span className='label label-danger'>{p.submitCount}</span>);
        }
      }
      const problemStatus = document.querySelector("#user_" + user.userId + " .problem-status[data-problem-id='" + p.problemId + "']");
      const statusSpan = problemStatus.querySelector('span[class="label label-warning"]') as HTMLElement;
      //让题目状态闪烁，并更新状态
      if (!p.isUnknown) {
        // 加高亮边框前去掉所有高亮边框
        document.querySelectorAll('.user-item.hold').forEach(function(element) {
          element.classList.remove('hold');
        });

        // 选择特定的队伍元素
        const userElement = document.querySelector("div[data-user-id='" + user.userId + "']");
    
        // 加高亮边框
        userElement.classList.add('hold');

        // 得到UserDiv距顶部的高度
        const clientHeight = document.documentElement.clientHeight || document.body.clientHeight || 0;
        const userTopHeight = document.querySelector(`.user-item[data-user-id="${user.userId}"]`).getBoundingClientRect().top + window.scrollY - clientHeight + 100;
        smoothScrollTo(0, userTopHeight, 500);

        const speed = 400; // 闪烁速度
        blinkElement(statusSpan, speed, function() {
          if (statusSpan && statusSpan.parentNode instanceof Element) {
            const root = createRoot(statusSpan.parentNode);
            root.render(problemHTML);
          }
        });
      }
    }

    const timer = document.getElementById('timer');
    animateElement(timer, { margin: 0}, 1600, () => {
      let rankValue = 0;
      let maxRank = 0;
      for (let i = 0; i < this.userCount; i++) {
        const user = this.userNextSequence[i];
        if (user.solved > 0) {
          rankValue = i + 1;
          maxRank = rankValue + 1;
        } else {
          rankValue = maxRank;
        }
        const userElement = document.querySelector(`div[data-user-id="${user.userId}"]`);
        if (userElement) {
          userElement.querySelector('.rank').innerHTML = rankValue.toString();
          userElement.querySelector('.solved').innerHTML = user.solved.toString();
          userElement.querySelector('.penalty').innerHTML = user.penalty.toFixed(0).toString();
        }
      }
    });
  }
  moveUser(toPos: number): void {
    for (let i = 0; i < this.userCount; i++) {
      const user = this.userNextSequence[i].userId;
      const userElement = document.querySelector(`div[data-user-id="${user}"]`);
      if (!userElement) {
        continue;
      }
      // 延时2.2s后更新位置，为了等待题目状态更新完成
      if (toPos != -1) {
        animateElement(userElement, { margin: 0 }, 2200, () => {
          animateElement(userElement, { top: i * userHeight + headerHeight }, 1000, () => {
            this.noAnimate = true;
          });
        });
      } else {
        animateElement(userElement, { margin: 0 }, 1800, () => {
          this.noAnimate = true;
        });
      }
    }
  }
  keydown(): void {
    if (this.noAnimate) {
      this.noAnimate = false;
      const user = this.updateOneUser();
      if (user) {
        const toPos = this.updateUserSequence();
        this.updateUserStatus(user);
        this.moveUser(toPos);
      } else {
        // 无队伍可更新时取消高亮边框
        document.querySelectorAll('.user-item.hold').forEach(function(element) {
          element.classList.remove('hold');
        });
      }
    }
  }
  showInitBoard() {
    let maxRank = 0;
    const problemThWidth = (100.0 - rankThWidth - nameThWidth - solvedThWidth - penaltyThWidth) / this.problemCount; //Problem列宽度百分比
    return (
      <div className='scrollboard'>
        <div id="timer"></div>
        <table className='w-full h-[44px] fixed z-50 header'>
          <tbody>
            <tr>
              <th style={{ width: rankThWidth + '%' }}>Rank</th>
              <th style={{ width: nameThWidth + '%' }}>Name</th>
              <th style={{ width: solvedThWidth + '%' }}>Solved</th>
              <th style={{ width: penaltyThWidth + '%' }}>Penalty</th>
              {this.problemList.map((_, index) => {
                return (<th key={index} style={{ width: problemThWidth + '%' }}>{String.fromCharCode(index + 65)}</th>);
              })}
            </tr>
          </tbody>
        </table>
        {this.userNowSequence.map((user, index) => {
          let rank = 0;
          if (user.solved != 0) {
            rank = index + 1;
            maxRank = rank + 1;
          } else {
            rank = maxRank;
          }
          return (
            <div key={index} id={`user_${user.userId}`} data-user-id={user.userId} className='user-item' style={{top: index * userHeight + headerHeight}}>
              <table className='w-full' key={index}>
                <tbody>
                  <tr>
                    <th style={{ width: rankThWidth + '%' }} className='rank'>{rank}</th>
                    <th style={{ width: nameThWidth + '%' }} className='user'>
                      {user.who}
                    </th>
                    <th style={{ width: solvedThWidth + '%' }} className='solved'>{user.solved}</th>
                    <th style={{ width: penaltyThWidth + '%' }} className='penalty'>{user.penalty.toFixed(0)}</th>
                    {this.problemList.map((problem, problemKey) => {
                      const p = user.submitProblemList[problem];
                      return (
                        <th key={problemKey} style={{width: problemThWidth + '%'}} className='problem-status' data-problem-id={problem}>
                          {p && (
                            <>
                              {p.isUnknown ? (
                                <span className='label label-warning'>{p.submitCount}</span>
                              ): (
                                p.isAccepted ? (
                                  <span className='label label-success'>{p.submitCount}</span>
                                ): (
                                  <span className='label label-danger'>{p.submitCount}</span>
                                )
                              )}
                            </>
                          )}
                        </th>
                      );
                    })}
                  </tr>
                </tbody>
              </table>
            </div>
          );
        })}
        <div id='user-void' className='user-item' style={{top: this.userCount * userHeight + headerHeight, margin: 0}}>
          <table className='w-full'>
            <tbody>
              <tr>
                <th></th>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    );
  }
}

function Page() {
  const router = useRouter();
  const [contest, setContest] = useState({} as any);
  const [board, setBoard] = useState(null as any);
  const [isListen, setIsListen] = useState(false);
  const [visible, setVisible] = useState(true);
  const [speed, setSpeed] = useState(1000);
  const timerActive = useRef(false);
  const { theme, setTheme } = useContext(GlobalContext);
  let timerId = null;
  async function  fetchData() {
    const c = await getContest(router.query.id);
    setContest(c.data);
    const submissionList = await listContestAllSubmissions(router.query.id);
    const u = await listContestUsers(router.query.id);
    const p = await listContestProblems(router.query.id);
    const submissionData:Submission[] = [];
    for (const submission of submissionList.data.data) {
      submissionData.push({
        submitId: submission.id,
        problemId: submission.problem,
        userId: submission.userId,
        submitTime: new Date(submission.createdAt),
        verdict: submission.status,
      } as Submission);
    }
    const users: { [key: string]: User } = {};
    for (const user of u.data.data) {
      users[user.userId] = new User(user.name, user.userId, user.role === 'ROLE_VIRTUAL_PLAYER');
    }
    const frozenTime = c.data.frozenTime ? new Date(c.data.frozenTime) : new Date();
    setBoard(new Board(p.data.data.length, users, submissionData, new Date(c.data.startTime), frozenTime));
  }

  const autoScrollboard = async () => {
    board.keydown();
    if (timerActive.current) {
        timerId = setTimeout(autoScrollboard, speed);
    }
  };
  useEffect(() => {
    if (isListen || !board) {
      return;
    }
    setIsListen(true);
    document.addEventListener('keydown', function(event) {
      if (event.key === ' ' || event.key === 'Enter') {
        board.keydown();
      }
      if (event.key === 'a' || event.key === 'A') {
        timerActive.current = true;
        if (!timerId) {
          autoScrollboard();
        }
      }
      if (event.key === 's' || event.key === 'S') {
        timerActive.current = false;
        if (timerId) {
            clearTimeout(timerId);
            timerId = null;
        }
      }
    });
    // 清理函数，在组件卸载时清除定时器
    return () => {
      if (timerId) {
        clearTimeout(timerId);
      }
    };
  }, [board]);
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <div>
      <Head>
        <title>{contest.name}</title>
      </Head>
      <Modal
        title='滚榜说明'
        visible={visible}
        autoFocus={false}
        onCancel={() => {
          setVisible(false);
        }}
        focusLock={true}
        footer={null}
      >
        <div>关闭此说明对话框后：</div>
        <div>1. 自动滚榜：按下A键（Auto）自动滚榜，按下S键（Stop）停止自动滚榜</div>
        <div>2. 手动滚榜：按下回车键手动滚榜，按一次跳一次</div>
        <div>3. 滚榜过程中请勿刷新此页面，刷新页面会需要从头滚榜，同时建议按 F11 将浏览器开启全屏模式进行滚榜</div>
        <div>4. 自动滚榜速度设置（单位毫秒）：
          <Slider
            value={speed}
            min={500}
            max={3000}
            onChange={(val) => setSpeed(Number(val))}
            style={{ width: 200 }}
          />
        </div>
        <div>5. 主题模式（如果投到大屏展示建议切换至暗黑模式，效果更佳）：
          <IconButton
            icon={theme !== 'dark' ? <IconMoonFill /> : <IconSunFill />}
            onClick={() => setTheme(theme === 'light' ? 'dark' : 'light')}
          />
        </div>
      </Modal>
      <main>
        <div className='table-header'>
          {board && board.showInitBoard()}
        </div>
      </main>
    </div>
  );
}

function Layout(page) {
  return page;
}

Page.getLayout = Layout;
export default Page;
