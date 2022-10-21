import Mock from 'mockjs';
import qs from 'query-string';
import dayjs from 'dayjs';
import setupMock from '@/utils/setupMock';

const { list } = Mock.mock({
  'list|100': [
    {
      id: /[0-9]{8}/,
      name: () =>
      Mock.Random.ctitle(),
      'contentType|0-2': 0,
      'filterType|0-1': 0,
      'count|0-2000': 0,
      'startedAt': Mock.Random.datetime(),
      'endedAt': Mock.Random.datetime(),
      'status|0-1': 0,
    },
  ],
});

const filterData = (
  rest: {
    id?: string;
    name?: string;
    'contentType[]'?: string[];
    'filterType[]'?: string[];
    'startedAt[]'?: string[];
    'endedAt[]'?: string[];
    'status[]'?: string;
  } = {}
) => {
  const {
    id,
    name,
    'contentType[]': contentType,
    'filterType[]': filterType,
    'startedAt[]': startedAt,
    'endedAt[]': endedAt,
    'status[]': status,
  } = rest;
  if (id) {
    return list.filter((item) => item.id === id);
  }
  let result = [...list];
  if (name) {
    result = result.filter((item) => {
      return (item.name as string).toLowerCase().includes(name.toLowerCase());
    });
  }
  if (contentType) {
    result = result.filter((item) =>
      contentType.includes(item.contentType.toString())
    );
  }
  if (filterType) {
    result = result.filter((item) =>
      filterType.includes(item.filterType.toString())
    );
  }
  if (startedAt && startedAt.length === 2) {
    const [begin, end] = startedAt;
    result = result.filter((item) => {
      const time = dayjs()
        .subtract(item.startedAt, 'days')
        .format('YYYY-MM-DD HH:mm:ss');
      return time;
    });
  }

  if (endedAt && endedAt.length === 2) {
    const [begin, end] = endedAt;
    result = result.filter((item) => {
      const time = dayjs()
        .subtract(item.endedAt, 'days')
        .format('YYYY-MM-DD HH:mm:ss');
      return (
        !dayjs(time).isBefore(dayjs(begin)) && !dayjs(time).isAfter(dayjs(end))
      );
    });
  }

  if (status && status.length) {
    result = result.filter((item) => status.includes(item.status.toString()));
  }

  return result;
};

setupMock({
  setup: () => {
    Mock.mock(new RegExp('/problems$'), (params) => {
      const {
        page = 1,
        pageSize = 10,
        ...rest
      } = qs.parseUrl(params.url).query;
      const p = page as number;
      const ps = pageSize as number;

      const result = filterData(rest);
      return {
        list: result.slice((p - 1) * ps, p * ps),
        total: result.length,
      };
    });
  },
});
