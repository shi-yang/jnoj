import React, { useEffect, useState } from 'react';
import { Button, Card, Table, TableColumnProps, PaginationProps, Drawer, Collapse, Divider } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { getSubmission, getSubmissionInfo, listSubmissions } from '@/api/submission';
import { VerdictMap } from './constants';
import styles from './style/description.module.less'
import hljs from 'highlight.js';
import 'highlight.js/styles/github.css';
const CollapseItem = Collapse.Item;
const Submission = (props) => {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [submissionInfo, setSubmissionInfo] = useState({tests: [], compileMsg: ''});
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
      problemId: props.problem.id
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
      align: 'center',
    },
    {
      title: t['language'],
      dataIndex: 'language',
      align: 'center',
      render: (col) => languageMap[col]
    },
    {
      title: t['verdict'],
      dataIndex: 'verdict',
      align: 'center',
      render: (col) => VerdictMap[col]
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
    },
    {
      title: t['createdAt'],
      dataIndex: 'createdAt',
      align: 'center',
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
    hljs.highlightAll();
  }, [submission]);
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <Card>
      <Drawer
        width={900}
        title={<span>{t['submission']}</span>}
        visible={visible}
        onOk={() => {
          setVisible(false);
        }}
        onCancel={() => {
          setVisible(false);
        }}
      >
        <pre>
          <code className='language-cpp'>
            {submission.source}
          </code>
        </pre>
        {submissionInfo.compileMsg != '' && (
          <pre>
            {submissionInfo.compileMsg}
          </pre>
        )}
        <Collapse
          style={{ maxWidth: 1180 }}
        >
          {
            submissionInfo.tests.map((item, index) => (
              <CollapseItem header={
                (<div>Test #{index + 1}: {VerdictMap[item.verdict]}, Time: {item.time}, Memory: {item.memory}</div>)
              } name={`${index}`} key={index}>
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
                    <h4>{t['answer']}</h4>
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
