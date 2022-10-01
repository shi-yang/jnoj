import Mock, { Random } from 'mockjs';
import setupMock from '@/utils/setupMock';

const { submissions } = Mock.mock({
  'submissions|100': [
    {
      id: /[0-9]{8}/,
      name: () => Mock.Random.ctitle(),
      created_at: () => Random.datetime(),
    },
  ],
});
setupMock({
  setup: () => {
    Mock.mock(/^\/problems\/\d*$/, () => {
      return {
        id: 10002,
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
    Mock.mock(/^\/submissions$/, () => {
      return {
        data: submissions,
        total: submissions.length,
      }
    })
  },
});
