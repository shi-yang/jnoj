import Mock, { Random } from 'mockjs';
import setupMock from '@/utils/setupMock';

const { problems } = Mock.mock({
  'problems|12': [
    {
      id: /[0-9]{8}/,
      'key|+1': 0,
      name: () => Random.ctitle(),
      attempted: () => Random.integer(0, 300),
      accepted: () => Random.integer(0, 300),
      created_at: () => Random.datetime(),
      is_solved: () => Random.boolean(),
    },
  ],
});
const { standings } = Mock.mock({
  'standings|100': [
    {
      'id|+1': 1,
      'rank|+1': 1,
      who: () => Random.cname(),
      solved: () => Random.integer(0, 12),
      score: () => Random.integer(0, 900),
      'problems|12': [
        {
          is_solved: Random.boolean(),
          attempted: Random.integer(0, 12),
        }
      ]
    }
  ]
})
const { submissions } = Mock.mock({
  'submissions|100': [
    {
      id: /[0-9]{8}/,
      name: () => Random.ctitle(),
      verdict: () => Random.integer(0, 12),
      user: {
        id: () => Random.integer(100, 10000),
        nickname: () => Random.cname(),
      },
      created_at: () => Random.datetime(),
    },
  ],
});
const { users } = Mock.mock({
  'users|200': [
    {
      'id|+1': 1,
      'nickname': () => Random.cname(),
    }
  ]
})
const statusMap = {
  0: 'pending',
  1: 'correct',
  2: 'incorrect'
}
const { status } = Mock.mock({
  'status|2000': [
    {
      'id|+1': 1,
      problem_id: () => Random.integer(0, 11),
      status: () => statusMap[Random.integer(0, 2)],
      user_id: () => Random.integer(1, 200),
      score: () => Random.integer(0, 100),
      interval: () => Random.integer(0, 120102)
    }
  ]
})
setupMock({
  setup: () => {
    Mock.mock(/^\/contests\/\d*$/, () => {
      return {
        id: 10002,
        title: Random.ctitle(),
        start_time: Random.datetime(),
        end_time: Random.datetime(),
      }
    });
    Mock.mock(/^\/contests$/, () => {
      return {
        data: problems,
        total: problems.length,
      }
    })
    Mock.mock(/^\/contests\/\d*\/standings$/, () => {
      return {
        data: standings,
        total: standings.length,
      }
    })
    Mock.mock(/^\/contests\/\d*\/problems$/, () => {
      return {
        data: problems,
        total: problems.length,
      }
    })
    Mock.mock(/^\/contests\/\d*\/statuses$/, () => {
      return {
        data: status,
        total: status.length,
      }
    })
    Mock.mock(/^\/contests\/\d*\/users$/, () => {
      return {
        data: users,
        total: users.length,
      }
    })
    Mock.mock(/^\/contests\/\d*\/problems\/[A-Z]$/, () => {
      return {
        id: 10002,
        key: 0,
        timeLimit: 2000,
        memoryLimit: 268435456,
        statements: [{
          language: 'zh-CN',
          name: Random.ctitle(),
          legend: Random.cparagraph(8, 12),
          input: Random.cparagraph(),
          output: Random.cparagraph(),
          notes: Random.cparagraph(),
        }],
        sampleTests: [
          {
            input: '8',
            output: '7 4\r\n3 7 5 1 10 3 20\r\n'
          },
          {
            input: '-1\r\n',
            output: '7 2\r\n3 7 5 1 10 3 20\r\n'
          }
        ],
      }
    });
    Mock.mock(/^\/contests\/\d*\/submissions$/, () => {
      return {
        data: submissions,
        total: submissions.length,
      }
    })
  },
});
