import Mock from 'mockjs';
import setupMock from '@/utils/setupMock';
import { Random } from 'mockjs';
const { userChekcers } = Mock.mock({
  'userChekcers|3': [
    {
      id: /[0-9]{8}/,
      name: () => Random.ctitle(),
      description: () => Random.ctitle(),
    },
  ],
});
const { stdChekcers } = Mock.mock({
  'stdChekcers|12': [
    {
      id: /[0-9]{8}/,
      name: () => Random.ctitle(),
      description: () => Random.ctitle(),
    },
  ],
});
setupMock({
  setup: () => {
    Mock.mock(/\/problems\/\d*$/, () => {
      return {
        id: 10002,
        timeLimit: 2000,
        memoryLimit: 268435456,
        statements: [{
          language: 'zh-CN',
          name: 'Hello, world',
          legend: '请输出 Hello, world',
          input: '没有输入',
          output: '输出一个字符串 Hello, world',
          notes: 'In the first example $5$ is also a valid answer because the elements with indices $[1, 3, 4, 6]$ is less than or equal to $5$ and obviously less than or equal to $6$.\r\n\r\nIn the second example you cannot choose any number that only $2$ elements of the given sequence will be less than or equal to this number because $3$ elements of the given sequence will be also less than or equal to this number.',
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
    })
    Mock.mock(/\/problems\/\d*\/solutions$/, () => {
      return [
        {
          id: 1,
          name: 'main.cpp',
          length: 233,
          created_at: Random.date(),
          language: 'C++',
          type: 'Main correct solution',
        },
        {
          id: 2,
          name: 'haha.cpp',
          length: 233,
          language: 'C++',
          created_at: Random.date(),
          type: 'Correct',
        }
      ]
    })
    Mock.mock(/\/problems\/\d*\/statements$/, () => {
      return {
        data: [
          {
            id: 1,
            language: 'English',
            name: Random.title(),
            legend: Random.paragraph(),
            input: Random.paragraph(),
            output: Random.paragraph(),
            created_at: Random.date(),
            notes: Random.paragraph()
          },
          {
            id: 2,
            language: '中文',
            name: Random.ctitle(),
            legend: Random.cparagraph(),
            input: Random.cparagraph(),
            output: Random.cparagraph(),
            created_at: Random.date(),
            notes: Random.paragraph()
          }
        ]
      }
    })
    Mock.mock(/\/problems\/\d*\/tests$/, () => {
      return {
        data: [
          {
            id: '1',
            content: 'Jane Doe',
            size: 23000,
            remark: '32 Park Road, London',
            example: true,
            created_at: Random.datetime(),
          },
          {
            id: '2',
            content: 'Alisa Ross',
            size: 25000,
            remark: '35 Park Road, London',
            example: true,
            created_at: Random.datetime(),
          },
          {
            id: '3',
            content: 'Kevin Sandra',
            size: 22000,
            remark: '31 Park Road, London',
            example: true,
            created_at: Random.datetime(),
          },
          {
            id: '4',
            content: 'Ed Hellen',
            size: 17000,
            remark: '42 Park Road, London',
            example: false,
            created_at: Random.datetime(),
          },
          {
            id: '5',
            content: 'William Smith',
            size: 27000,
            remark: '62 Park Road, London',
            example: false,
            created_at: Random.datetime(),
          },
        ],
        total: 12,
      }
    })
    Mock.mock(/\/problems\/\d*\/checkers$/, () => {
      return {
        std_checkers: stdChekcers,
        user_checkers: userChekcers,
      }
    })
  },
});
