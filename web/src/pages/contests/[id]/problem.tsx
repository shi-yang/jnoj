import { getContestProblem } from "@/api/contest";
import { useEffect, useState } from "react";
import styles from './style/problem.module.less';
import { Grid, ResizeBox, Typography } from "@arco-design/web-react";
import useLocale from "@/utils/useLocale";
import locale from "./locale";
import Editor from './editor';
import ProblemContent from "@/components/Problem/ProblemContent";
import { useRouter } from "next/router";

const { Title, Paragraph } = Typography;

export default ({contest, number}) => {
  const t = useLocale(locale);
  const router = useRouter();
  const [loading, setLoading] = useState(true);
  const [problem, setProblem] = useState({
    statements: [],
    timeLimit: 0,
    memoryLimit: 0,
    sampleTests: []
  });
  const [language, setLanguage] = useState(0);
  function fetchData() {
    setLoading(true);
    getContestProblem(contest.id, number.charCodeAt(0) - 65)
      .then((res) => {
        setProblem(res.data);
      })
      .catch(err => {
        console.log(err)
      })
      .finally(() => {
        setLoading(false);
      });
  }
  useEffect(() => {
    fetchData()
  }, [contest.id, number])
  return (
    <div className={styles['container']}>
      { !loading && (
        <div className={styles['problem-layout']}>
          <Grid.Row className={styles.header} justify="space-between" align="center">
            <Grid.Col span={24}>
              <Typography.Title className={styles.title} heading={5}>
              {number} - {problem.statements[language].name}
              </Typography.Title>
            </Grid.Col>
          </Grid.Row>
          <ResizeBox.Split
            max={0.8}
            min={0.2}
            style={{ height: '100%' }}
            panes={[
              <div key='first' className={styles.left}>
                {!loading && (
                  <div className={styles['description-content']}>
                    <ProblemContent problem={problem} language={language}></ProblemContent>
                  </div>
                )}
              </div>,
              <div key='second' className={styles.right}>
                <Editor problem={problem} language={language} contest={contest} />
              </div>,
            ]}
          />
        </div>
      )}
    </div>
  )
}
