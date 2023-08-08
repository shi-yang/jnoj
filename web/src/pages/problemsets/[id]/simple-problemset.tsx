import React, { useEffect, useState } from 'react';
import { Card, Divider, Link, PageHeader, PaginationProps, Space, Table, TableColumnProps, Typography } from '@arco-design/web-react';
import { useRouter } from 'next/router';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { IconCheckCircle, IconExclamationCircle, IconUser } from '@arco-design/web-react/icon';
import { listProblemsetProblems } from '@/api/problemset';
import styles from './style/index.module.less';

export const ProblemStatus = {
  'NOT_START': '',
  'ATTEMPTED': <IconExclamationCircle style={{ color: 'rgb(var(--orange-5))' }} />,
  'SOLVED': <IconCheckCircle style={{ color: 'rgb(var(--green-5))' }} />,
};

function ProblemTable({problems, loading}: {problems: any[], loading: boolean}) {
  const columns: TableColumnProps[] = [
    {
      title: '状态',
      dataIndex: 'status',
      align: 'center' as 'center',
      width: 80,
      render: col => ProblemStatus[col]
    },
    {
      title: '题目名称',
      dataIndex: 'name',
      render: (col, record) => (
        <div className={styles['column-title']}>
          <Link href={`/problemsets/${record.problemsetId}/problems/${record.order}`}>
            <div>
              {record.order}. {record.name}
            </div>
          </Link>
        </div>
      )
    },
    {
      title: '通过&提交',
      dataIndex: 'count',
      align: 'center' as 'center',
      render(_, record) {
        return (
          <>
            {Number(record.acceptedCount).toLocaleString()} / {Number(record.submitCount).toLocaleString()}
          </>
        );
      },
      width: 150,
    }
  ];
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 50,
    current: 1,
    hideOnSinglePage: true,
    pageSizeChangeResetCurrent: true,
  });
  function onChangeTable({ current, pageSize }) {
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }
  return (
    <Table
      rowKey={r => r.id}
      className='arco-drag-table-container'
      loading={loading}
      onChange={onChangeTable}
      pagination={pagination}
      columns={columns}
      data={problems}
    />
  );
}

function Problem({problemset}: {problemset:any}) {
  const t = useLocale(locale);
  const router = useRouter();
  const { id } = router.query;
  const [loading, setLoading] = useState(false);
  const [formParams, setFormParams] = useState({});
  const [problems, setProblems] = useState([]);
  const [problemsetChildren, setProblemsetChildren] = useState([]);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 100,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  useEffect(() => {
    fetchData();
  }, []);

  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    const params = {
      page: current,
      perPage: pageSize,
      ...formParams,
    };
    listProblemsetProblems(id, params)
      .then((res) => {
        setProblems(res.data.problems);
        setProblemsetChildren(res.data.problemsets);
        setPatination({
          ...pagination,
          current,
          pageSize,
          total: res.data.problemTotal,
        });
        setLoading(false);
      });
  }
  return (
    <div>
      <PageHeader
        title={problemset.name}
        style={{ background: 'var(--color-bg-2)' }}
        extra={
          <div><IconUser />{problemset.memberCount}</div>
        }
      >
        {problemset.description}
      </PageHeader>
      <Divider />
      <Card>
        {problems.length > 0 && (
          <ProblemTable problems={problems} loading={loading}/>
        )}
        <Space direction='vertical' style={{width: '100%'}}>
          {problemsetChildren.map((item, index) => (
            <div key={index}>
              <Link hoverable={false} href={`/problemsets/${item.id}`}>
                <Typography.Title heading={5}>
                  {`${index+1}. ${item.name}`}
                </Typography.Title>
              </Link>
              {item.type !== 'EXAM' && (
                <ProblemTable problems={item.problems} loading={loading} />
              )}
            </div>
          ))}
        </Space>
      </Card>
    </div>
  );
}

export default Problem;
