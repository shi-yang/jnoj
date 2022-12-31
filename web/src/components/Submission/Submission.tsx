import React, { useEffect, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps, Drawer, Collapse, Divider, Typography, Space } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { getSubmission, getSubmissionInfo, LanguageMap, listSubmissions } from '@/api/submission';
import styles from './style/submission.module.less'
import { FormatMemorySize, FormatTime } from '@/utils/format';
import Highlight from '@/components/Highlight';
import SubmissionVerdict from './SubmissionVerdict';
const CollapseItem = Collapse.Item;

const Submission = ({pid, entityType}) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [submissionInfo, setSubmissionInfo] = useState({tests: [], compileMsg: '', acceptedTestCount: 0, totalTestCount: 0});
  const [submission, setSubmission] = useState({source: ''})
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
      perPage: pageSize,
      problemId: pid,
      entityType: entityType,
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
    getSubmission(id)
      .then(res => {
        setSubmission(res.data)
      })
  }
  function onChangeTable({ current, pageSize }) {
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
    fetchData()
  }
  const columns: TableColumnProps[] = [
    {
      title: '#',
      dataIndex: 'id',
      align: 'center',
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
      render: (col) => <SubmissionVerdict verdict={col} />
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
    <Card style={{height: '100%', overflow: 'auto'}}>
      <Drawer
        width={900}
        title={<span>{t['submission']}</span>}
        visible={visible}
        onCancel={() => {
          setVisible(false);
        }}
        footer={null}
      >
        <Typography.Title heading={4}>源码</Typography.Title>
        <Highlight content={submission.source} />
        {submissionInfo.compileMsg != '' && (
          <>
            <Divider />
            <Typography.Title heading={4}>编译信息</Typography.Title>
            <Highlight content={submissionInfo.compileMsg} />
          </>
        )}
        <Divider />
        <Typography.Title heading={4}>测试点</Typography.Title>
        <div>
          {submissionInfo.acceptedTestCount} / {submissionInfo.totalTestCount}
        </div>
        <Collapse
          style={{ maxWidth: 1180 }}
        >
          {
            submissionInfo.tests.map((item, index) => (
              <CollapseItem
                header={(
                  <Space split={<Divider type='vertical' />}>
                    <span>#{index + 1}</span>
                    <SubmissionVerdict verdict={item.verdict} />
                    <span>{t['time']}: {(item.time / 1000)} ms</span>
                    <span>{t['memory']}: {FormatMemorySize(item.memory)}</span>
                  </Space>
                )}
                name={`${index}`}
                key={index}
              >
                <div className={styles['sample-test']} key={index}>
                  <div className={styles.input}>
                    <h4>{t['input']}</h4>
                    <pre>{item.stdin}</pre>
                  </div>
                  <div className={styles.output}>
                    <h4>{t['output']}</h4>
                    <pre>{ item.stdout }</pre>
                  </div>
                  <div className={styles.output}>
                    <h4>{t['answer']}</h4>
                    <pre>{ item.answer }</pre>
                  </div>
                  <div className={styles.output}>
                    <h4>Checker out</h4>
                    <pre>{ item.checkerStdout }</pre>
                  </div>
                </div>
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
