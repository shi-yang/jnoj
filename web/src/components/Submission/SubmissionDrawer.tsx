import useLocale from "@/utils/useLocale";
import { Collapse, Divider, Drawer, Space, Typography } from "@arco-design/web-react";
import { useEffect, useState } from "react";
import { getSubmission, getSubmissionInfo } from "@/api/submission";
import { FormatMemorySize } from "@/utils/format";
import styles from './style/submission.module.less'
import Highlight from "@/components/Highlight";
import locale from "./locale";
import SubmissionVerdict from "./SubmissionVerdict";

export default ({id, visible, onCancel}) => {
  const t = useLocale(locale);
  const [submissionInfo, setSubmissionInfo] = useState({
    subtasks: [],
    compileMsg: '',
    hasSubtask: false,
    acceptedTestCount: 0,
    totalTestCount: 0,
  });
  const [submission, setSubmission] = useState({source: ''});

  function fetchData() {
    getSubmissionInfo(id)
      .then(res => {
        setSubmissionInfo(res.data);
      })
    getSubmission(id)
      .then(res => {
        setSubmission(res.data);
      })
  }
  useEffect(() => {
    fetchData();
  }, [id]);
  return (
    <Drawer
      width={900}
      title={<span>{t['submission']}: {id}</span>}
      visible={visible}
      onCancel={onCancel}
      footer={null}
    >
      <Typography.Title heading={4}>{t['drawer.source']}</Typography.Title>
      <Highlight content={submission.source} />
      {submissionInfo.compileMsg != '' && (
        <>
          <Divider />
          <Typography.Title heading={4}>{t['drawer.compileInfo']}</Typography.Title>
          <Highlight content={submissionInfo.compileMsg} />
        </>
      )}
      <Divider />
      <Typography.Title heading={4}>{t['drawer.tests']}</Typography.Title>
      <div>
        {submissionInfo.acceptedTestCount} / {submissionInfo.totalTestCount}
      </div>
      <Collapse
        style={{ maxWidth: 1180 }}
      >
        {
          submissionInfo.subtasks.map((item, index) => 
            submissionInfo.hasSubtask 
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
                    <span>{t['score']}: {item.score.toFixed(1)}</span>
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
    </Drawer>
  )
}