import Mock from 'mockjs';
import setupMock from '@/utils/setupMock';

const { data } = Mock.mock({
  'data|25': [
    {
      id: /[0-9]{8}[-][0-9]{4}/,
      name: () => Mock.Random.ctitle(),
      description: () => Mock.Random.cparagraph(),
      memberCount: () => Mock.Random.int(0, 100),
    },
  ],
});

setupMock({
  setup: () => {
    Mock.mock(new RegExp('/groups'), (params) => {
      return {
        data,
        total: data.length,
      };
    });
  },
});
