import useLocale from '@/utils/useLocale';
import { Card, Collapse, Descriptions, Divider, Link, Message, Space, Tooltip, Typography } from '@arco-design/web-react';
import React, { useEffect, useState } from 'react';
import { LanguageMap, getSubmission } from '@/api/submission';
import { FormatMemorySize, FormatTime } from '@/utils/format';
import styles from './style/submission.module.less';
import Highlight from '@/components/Highlight';
import locale from './locale';
import SubmissionVerdict from './SubmissionVerdict';
import { IconShareInternal } from '@arco-design/web-react/icon';

export default function Submission ({id}: {id: number}) {
  const t = useLocale(locale);
  const [submission, setSubmission] = useState({id: 0, source: '', language: 0, info: {
    subtasks: [],
    compileMsg: '',
    hasSubtask: false,
    acceptedTestCount: 0,
    totalTestCount: 0,
  }});
  const [currentDomain, setCurrentDomain] = useState(null);
  useEffect(() => {
    setCurrentDomain(window.location.host);
  }, []);
  const [descriptionData, setDescriptionData] = useState([]);

  function fetchData() {
    getSubmission(id)
      .then(res => {
        const { data } = res;
        setSubmission(data);
        let problemLink;
        if (data.entityType === 'PROBLEMSET') {
          problemLink = <Link href={`/problemsets/${data.entityId}/problems/${data.problemNumber}`}>{`${data.problemNumber} - ${data.problemName}`}</Link>;
        } else if (data.entityType === 'CONTEST') {
          problemLink = <Link href={`/contests/${data.entityId}/problem/${data.problemNumber}`}>{`${data.problemNumber} - ${data.problemName}`}</Link>;
        }
        setDescriptionData([
          {label: t['user'], value: <Link href={`/u/${data.userId}`}>{data.nickname}</Link>},
          {label: t['verdict'], value: <SubmissionVerdict verdict={data.verdict} />},
          {label: t['score'], value: data.score},
          {label: t['time'], value: `${data.time / 1000} ms`},
          {label: t['memory'], value: FormatMemorySize(data.memory)},
          {label: t['language'], value: LanguageMap[data.language]},
          {label: t['problemName'], value: problemLink},
          {label: t['createdAt'], value: FormatTime(data.createdAt)},
        ]);
      })
      .catch(res => {
        Message.error(res.response.data.message);
      });
  }
  useEffect(() => {
    fetchData();
  }, [id]);
  return (
    <>
      <Typography.Title heading={4} copyable={{
        text: currentDomain + '/submissions/' + submission.id,
        icon: <IconShareInternal />,
        tooltips: [
          <Tooltip key={0}>复制提交链接</Tooltip>,
          <Tooltip key={1}>已复制</Tooltip>
        ]
      }}>
        {t['submissionID']}:{submission.id}
      </Typography.Title>
      <Descriptions
        data={descriptionData}
        layout='vertical'
        border
        column={5}
        style={{ marginBottom: 20 }}
      />
      <Typography.Title heading={4}>{t['drawer.source']}</Typography.Title>
      <Highlight content={submission.source} language={submission.language} />
      {
        submission.info &&
        <div>
          {submission.info.compileMsg != '' && (
            <>
              <Divider />
              <Typography.Title heading={4}>{t['drawer.compileInfo']}</Typography.Title>
              <Highlight content={submission.info.compileMsg} />
            </>
          )}
          <Divider />
          <Typography.Title heading={4}>{t['drawer.tests']}</Typography.Title>
          <div>
            {submission.info.acceptedTestCount} / {submission.info.totalTestCount}
          </div>
          <Collapse
            style={{ maxWidth: 1180 }}
          >
            {
              submission.info.subtasks.map((item, index) => 
                submission.info.hasSubtask 
                ? <Collapse.Item
                    header={(
                      <Space split={<Divider type='vertical' />}>
                        <span>#{index + 1}</span>
                        <SubmissionVerdict verdict={item.verdict} />
                        <span>{t['score']}: {item.score.toFixed(1)}</span>
                        <span>{t['time']}: {(item.time / 1000)} ms</span>
                        <span>{t['memory']}: {FormatMemorySize(item.memory)}</span>
                      </Space>
                    )}
                    name={`${index}`}
                    key={index}
                  >
                    <Collapse>
                      {
                        item.tests.map((test, testIndex) => 
                          <Collapse.Item
                            key={testIndex}
                            header={(
                              <Space split={<Divider type='vertical' />}>
                                <span>#{testIndex + 1}</span>
                                <SubmissionVerdict verdict={test.verdict} />
                                <span>{t['time']}: {(test.time / 1000)} ms</span>
                                <span>{t['memory']}: {FormatMemorySize(test.memory)}</span>
                              </Space>
                            )}
                            name={`${index}-${testIndex}`}
                          >
                            <div className={styles['sample-test']}>
                              <div className={styles.input}>
                                <h4>{t['input']}</h4>
                                <pre>{test.stdin}</pre>
                              </div>
                              <div className={styles.output}>
                                <h4>{t['output']}</h4>
                                <pre>{ test.stdout }</pre>
                              </div>
                              <div className={styles.output}>
                                <h4>{t['answer']}</h4>
                                <pre>{ test.answer }</pre>
                              </div>
                              <div className={styles.output}>
                                <h4>Checker out</h4>
                                <pre>{ test.checkerStdout }</pre>
                              </div>
                            </div>
                          </Collapse.Item>
                        )
                      }
                    </Collapse>
                  </Collapse.Item>
                : item.tests.map((test, testIndex) =>
                  <Collapse.Item
                    header={(
                      <Space split={<Divider type='vertical' />}>
                        <span>#{testIndex + 1}</span>
                        <SubmissionVerdict verdict={test.verdict} />
                        <span>{t['score']}: {test.score.toFixed(1)}</span>
                        <span>{t['time']}: {(test.time / 1000)} ms</span>
                        <span>{t['memory']}: {FormatMemorySize(test.memory)}</span>
                      </Space>
                    )}
                    name={`${testIndex}`}
                    key={testIndex}
                  >
                    <div className={styles['sample-test']}>
                      <div className={styles.input}>
                        <h4>{t['input']}</h4>
                        <pre>{test.stdin}</pre>
                      </div>
                      <div className={styles.output}>
                        <h4>{t['output']}</h4>
                        <pre>{ test.stdout }</pre>
                      </div>
                      <div className={styles.output}>
                        <h4>{t['answer']}</h4>
                        <pre>{ test.answer }</pre>
                      </div>
                      <div className={styles.output}>
                        <h4>Checker out</h4>
                        <pre>{ test.checkerStdout }</pre>
                      </div>
                    </div>
                  </Collapse.Item>
                )
              )
            }
          </Collapse>
        </div>
      }
    </>
  );
}
