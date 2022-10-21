import Mock, { Random } from 'mockjs';
import setupMock from '@/utils/setupMock';

const { problems } = Mock.mock({
  'problems|12': [
    {
      id: /[0-9]{8}/,
      'key|+1': 1,
      name: () => Mock.Random.ctitle(),
      attempted: () => Mock.Random.integer(0, 300),
      accepted: () => Mock.Random.integer(0, 300),
      created_at: () => Random.datetime(),
    },
  ],
});
const { standings } = Mock.mock({
  'standings|100': [
    {
      'id|+1': 1,
      'rank|+1': 1,
      who: () => Mock.Random.cname(),
      solved: () => Mock.Random.integer(0, 12),
      score: () => Mock.Random.integer(0, 900),
      'problems|12': [
        {
          is_solved: Mock.Random.boolean(),
          attempted: Mock.Random.integer(0, 12),
        }
      ]
    }
  ]
})
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
      problem_id: () => Random.integer(1, 12),
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
  },
});
