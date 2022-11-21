import { getContestProblem } from "@/api/contest";
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom"
import styles from './style/problem.module.less';
import { Grid, ResizeBox, Typography } from "@arco-design/web-react";
import useLocale from "@/utils/useLocale";
import locale from "./locale";
import Editor from './editor';
import ProblemContent from "@/components/Problem/ProblemContent";

const { Title, Paragraph } = Typography;

export default ({contest}) => {
  const t = useLocale(locale);
  const params = useParams();
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
    getContestProblem(params.id, params.key.charCodeAt(0) - 65)
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
  }, [params.id, params.key])
  return (
    <div className={styles['container']}>
      { !loading && (
        <div className={styles['problem-layout']}>
          <Grid.Row className={styles.header} justify="space-between" align="center">
            <Grid.Col span={24}>
              <Typography.Title className={styles.title} heading={5}>
              {params.key} - {problem.statements[language].name}
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
