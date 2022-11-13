import React, { useEffect, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps, Drawer, Collapse, Divider } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { getSubmissionInfo, listSubmissions } from '@/api/submission';
const CollapseItem = Collapse.Item;
const Submission = (props) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [submissionInfo, setSubmissionInfo] = useState({tests: []});
  const [visible, setVisible] = useState(false);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 10,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  function fetchData() {
    const { current, pageSize } = pagination;
    const params = {
      page: current,
      pageSize,
      problem_id: props.problem.id
    };
    setLoading(true);
    listSubmissions(params)
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
  function onView(id) {
    setVisible(true)
    getSubmissionInfo(id)
      .then(res => {
        setSubmissionInfo(res.data)
      })
  }
  function onChangeTable({ current, pageSize }) {
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }
  const languageMap = {
    0: 'C',
    1: 'C++',
    2: 'Java',
    3: 'Python3'
  }
  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'id',
    },
    {
      title: t['language'],
      dataIndex: 'language',
      render: (col) => {
        return languageMap[col]
      }
    },
    {
      title: t['verdict'],
      dataIndex: 'verdict',
    },
    {
      title: t['time'],
      dataIndex: 'time'
    },
    {
      title: t['memory'],
      dataIndex: 'memory'
    },
    {
      title: t['createdAt'],
      dataIndex: 'createdAt',
    },
    {
      title: t['action'],
      dataIndex: 'action',
      align: 'center',
      render: (_, record) => (
        <>
          <Button type="text" size="small" onClick={() => { onView(record.id) }}>查看</Button>
        </>
      ),
    },
  ];

  useEffect(() => {
    fetchData();
  }, []);
  return (
    <Card>
      <Drawer
        width={900}
        title={<span>Submission Info</span>}
        visible={visible}
        onOk={() => {
          setVisible(false);
        }}
        onCancel={() => {
          setVisible(false);
        }}
      >
        <Collapse
          defaultActiveKey={['1', '2']}
          style={{ maxWidth: 1180 }}
        >
          {
            submissionInfo.tests.map((item, index) => (
              <CollapseItem header={
                <>Test #{index + 1}: {item.verdict}, Time: {item.time}, Memory: {item.memory}</>
              } name='1' key={index}>
                {item.stdin}
                <Divider />
                {item.stdout}
                <Divider />
                {item.stderr}
                <Divider />
                {item.answer}
              </CollapseItem>
            ))
          }
        </Collapse>
      </Drawer>
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
