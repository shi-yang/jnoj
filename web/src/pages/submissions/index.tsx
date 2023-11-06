import React, { useEffect, useRef, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps, Link, Input } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { LanguageMap, listSubmissions } from '@/api/submission';
import { FormatMemorySize, FormatTime } from '@/utils/format';
import SubmissionVerdict, { VerdictMap } from '@/modules/submission/SubmissionVerdict';
import SubmissionDrawer from '@/modules/submission/SubmissionDrawer';
import { IconSearch } from '@arco-design/web-react/icon';

const Submission = () => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [submissionId, setSubmissionId] = useState(0);
  const [visible, setVisible] = useState(false);
  const [formParams, setFormParams] = useState({});
  const inputRef = useRef(null);
  const [pagination, setPagination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100]
  });
  function fetchData() {
    const { current, pageSize } = pagination;
    const params = {
      page: current,
      perPage: pageSize,
      ...formParams,
    };
    console.log('params', params);
    setLoading(true);
    listSubmissions(params)
      .then((res) => {
        setData(res.data.data || []);
        setPagination({
          ...pagination,
          current,
          pageSize,
          total: res.data.total,
        });
      })
      .finally(() => {
        setLoading(false);
      });
  }
  function onView(id) {
    setSubmissionId(id);
    setVisible(true);
  }
  function onChangeTable({ current, pageSize }, sorter, filters) {
    setFormParams({...formParams, ...filters});
    setPagination({
      ...pagination,
      current,
      pageSize,
    });
  }
  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'id',
      align: 'center',
      filterMultiple: false,
      filterIcon: <IconSearch />,
      filterDropdown: ({ filterKeys, setFilterKeys, confirm }) => {
        return (
          <div className='arco-table-custom-filter'>
            <Input.Search
              ref={inputRef}
              searchButton
              placeholder='输入ID进行搜索'
              value={filterKeys[0] || ''}
              onChange={(value) => {
                setFilterKeys(value ? [value] : []);
              }}
              onSearch={() => {
                confirm();
              }}
            />
          </div>
        );
      },
      onFilterDropdownVisibleChange: (visible) => {
        if (visible) {
          setTimeout(() => inputRef.current.focus(), 150);
        }
      },
    },
    {
      title: t['user'],
      dataIndex: 'username',
      align: 'center',
      render: (_, record) => <Link href={`/u/${record.userId}`}>{record.nickname}</Link>,
      filterMultiple: false,
      filterIcon: <IconSearch />,
      filterDropdown: ({ filterKeys, setFilterKeys, confirm }) => {
        return (
          <div className='arco-table-custom-filter'>
            <Input.Search
              ref={inputRef}
              searchButton
              placeholder='输入用户名进行搜索'
              value={filterKeys[0] || ''}
              onChange={(value) => {
                setFilterKeys(value ? [value] : []);
              }}
              onSearch={() => {
                confirm();
              }}
            />
          </div>
        );
      },
      onFilterDropdownVisibleChange: (visible) => {
        if (visible) {
          setTimeout(() => inputRef.current.focus(), 150);
        }
      },
    },
    {
      title: t['problemName'],
      dataIndex: 'problemId',
      align: 'center',
      render: (_, record) => {
        if (record.entityType === 'CONTEST') {
          return (
            <Link href={`/contests/${record.entityId}/problem/${record.problemNumber}`}>
              {record.problemNumber}. {record.problemName}
            </Link>
          );
        } else if (record.entityType === 'PROBLEMSET') {
          return (
            <Link href={`/problemsets/${record.entityId}/problems/${record.problemNumber}`}>
              {record.problemNumber}. {record.problemName}
            </Link>
          );
        } else if (record.entityType === 'PROBLEM_FILE') {
          return (
            <Link href={`/problems/${record.problemNumber}/update`}>
              {record.problemNumber}. {record.problemName}
            </Link>
          );
        }
        return <span>{record.problemNumber}. {record.problemName}</span>;
      },
      filterMultiple: false,
      filterIcon: <IconSearch />,
      filterDropdown: ({ filterKeys, setFilterKeys, confirm }) => {
        return (
          <div className='arco-table-custom-filter'>
            <Input.Search
              ref={inputRef}
              searchButton
              placeholder='输入题号进行搜索'
              value={filterKeys[0] || ''}
              onChange={(value) => {
                setFilterKeys(value ? [value] : []);
              }}
              onSearch={() => {
                confirm();
              }}
            />
          </div>
        );
      },
      onFilterDropdownVisibleChange: (visible) => {
        if (visible) {
          setTimeout(() => inputRef.current.focus(), 150);
        }
      },
    },
    {
      title: t['language'],
      dataIndex: 'language',
      align: 'center',
      render: (col) => LanguageMap[col]
    },
    {
      title: t['verdict'],
      dataIndex: 'verdict',
      align: 'center',
      render: (col) => <SubmissionVerdict verdict={col} />,
      filters: Object.keys(VerdictMap).map(item => {
        return {
          text: <SubmissionVerdict verdict={Number(item)} />,
          value: item
        };
      }),
    },
    {
      title: t['score'],
      dataIndex: 'score',
      align: 'center',
    },
    {
      title: t['time'],
      dataIndex: 'time',
      align: 'center',
      render: (col) => `${col / 1000} ms`
    },
    {
      title: t['memory'],
      dataIndex: 'memory',
      align: 'center',
      render: (col) => FormatMemorySize(col)
    },
    {
      title: t['createdAt'],
      dataIndex: 'createdAt',
      align: 'center',
      render: (col) => FormatTime(col)
    },
    {
      title: t['action'],
      dataIndex: 'action',
      align: 'center',
      render: (_, record) => <Button type="text" size="small" onClick={() => { onView(record.id); }}>查看</Button>,
    },
  ];
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize]);
  useEffect(() => {
    setPagination({
      ...pagination,
      current: 1,
    });
    fetchData();
  }, [JSON.stringify(formParams)]);
  return (
    <Card>
      <SubmissionDrawer visible={visible} id={submissionId} onCancel={() => setVisible(false)} />
      <Table
        rowKey={r => r.id}
        loading={loading}
        columns={columns}
        onChange={onChangeTable}
        pagination={pagination}
        data={data}
      />
    </Card>
  );
};

export default Submission;
