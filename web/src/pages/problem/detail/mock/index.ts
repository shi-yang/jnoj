import Mock from 'mockjs';
import setupMock from '@/utils/setupMock';

setupMock({
  setup: () => {
    Mock.mock(new RegExp('/problems/123'), () => {
      return {
        id: 10002,
        name: 'Hello, world',
        description: '请输出 Hello, world',
        input: '没有输入',
        output: '输出一个字符串 Hello, world',
        timeLimit: 2000,
        memoryLimit: 268435456,
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
        notes: 'In the first example $5$ is also a valid answer because the elements with indices $[1, 3, 4, 6]$ is less than or equal to $5$ and obviously less than or equal to $6$.\r\n\r\nIn the second example you cannot choose any number that only $2$ elements of the given sequence will be less than or equal to this number because $3$ elements of the given sequence will be also less than or equal to this number.'
      }
    })
    Mock.mock(new RegExp('/api/basicProfile'), () => {
      return {
        status: 2,
        video: {
          mode: '自定义',
          acquisition: {
            resolution: '720*1280',
            frameRate: 15,
          },
          encoding: {
            resolution: '720*1280',
            rate: {
              min: 300,
              max: 800,
              default: 1500,
            },
            frameRate: 15,
            profile: 'high',
          },
        },
        audio: {
          mode: '自定义',
          acquisition: {
            channels: 8,
          },
          encoding: {
            channels: 8,
            rate: 128,
            profile: 'ACC-LC',
          },
        },
      };
    });

    Mock.mock(new RegExp('/api/adjustment'), () => {
      return new Array(2).fill('0').map(() => ({
        contentId: `${Mock.Random.pick([
          '视频类',
          '音频类',
        ])}${Mock.Random.natural(1000, 9999)}`,
        content: '视频参数变更，音频参数变更',
        status: Mock.Random.natural(0, 1),
        updatedTime: Mock.Random.datetime('yyyy-MM-dd HH:mm:ss'),
      }));
    });
  },
});
