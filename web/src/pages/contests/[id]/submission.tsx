import React, { useContext, useEffect, useRef, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps, Switch, Link, Input } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { LanguageMap } from '@/api/submission';
import { listContestSubmissions } from '@/api/contest';
import { FormatMemorySize, FormatTime } from '@/utils/format';
import SubmissionDrawer from '@/modules/submission/SubmissionDrawer';
import SubmissionVerdict, { VerdictMap } from '@/modules/submission/SubmissionVerdict';
import ContestContext from './context';
import { userInfo } from '@/store/reducers/user';
import { useAppSelector } from '@/hooks';
import { isLogged } from '@/utils/auth';
import ContestLayout from './Layout';
import { IconSearch } from '@arco-design/web-react/icon';
function Submission() {
  const t = useLocale(locale);
  const contest = useContext(ContestContext);
  const user = useAppSelector(userInfo);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [visible, setVisible] = useState(false);
  const [id, setId] = useState(0);
  const [formParams, setFormParams] = useState({});
  const [isMounted, setIsMounted] = useState(false);
  const inputRef = useRef(null);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 20,
    current: 1,
    sizeOptions: [20, 50, 100],
    pageSizeChangeResetCurrent: true,
  });
  useEffect(() => {
    fetchData();
  }, [pagination.pageSize, pagination.current, JSON.stringify(formParams)]);
  function fetchData() {
    const { current, pageSize } = pagination;
    const params = {
      page: current,
      per_page: pageSize,
      userId: 0,
      ...formParams,
    };
    // 第一次获取数据时，若是登录了，默认查当前用户
    if (!isMounted && isLogged()) {
      setIsMounted(true);
      params.userId = user.id;
    }
    setLoading(true);
    listContestSubmissions(contest.id, params)
      .then((res) => {
        setData(res.data.data || []);
        setPatination({
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
  function onDrawerCancel() {
    setVisible(false);
  }

  function onChangeTable({ current, pageSize }, sorter, filters) {
    setFormParams({...formParams, ...filters});
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }

  function onSwitchChange(value: boolean, event: any) {
    if (value) {
      setFormParams({...formParams, userId: user.id});
    } else {
      setFormParams({...formParams, userId: 0});
    }
  }
  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'id',
      align: 'center',
    },
    {
      title: t['user'],
      dataIndex: 'username',
      align: 'center',
      render: (_, record) => <Link href={`/u/${record.user.id}`}>{record.user.nickname}</Link>,
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
      title: t['problem'],
      dataIndex: 'problem',
      align: 'center',
      render: (col, record) => <span> {String.fromCharCode(65 + record.problemNumber)}. {record.problemName}</span>,
      filters: contest.problems.map(item => {
        return {
          text: String.fromCharCode(65 + item.number) + '. ' + item.name,
          value: item.number
        };
      }),
      filterMultiple: false,
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
      title: t['language'],
      dataIndex: 'language',
      align: 'center',
      render: col => LanguageMap[col]
    },
    {
      title: t['createdAt'],
      dataIndex: 'createdAt',
      align: 'center',
      render: col => FormatTime(col)
    },
    {
      title: t['action'],
      align: 'center',
      render: (_, record) => (
        <>
          <Button type="text" size="small" onClick={(e) => { setVisible(true); setId(record.id); }}>查看</Button>
        </>
      ),
    },
  ];

  return (
    <Card>
      <div style={{marginBottom: '20px'}}>
        <Switch defaultChecked={isLogged()} onChange={onSwitchChange} /> {t['submission.justMySubmission']}
      </div>
      <Table
        rowKey={r => r.id}
        loading={loading}
        columns={columns}
        onChange={onChangeTable}
        pagination={pagination}
        data={data}
      />
      {visible && <SubmissionDrawer id={id} visible={visible} onCancel={onDrawerCancel} />}
    </Card>
  );
};
Submission.getLayout = ContestLayout;
export default Submission;
