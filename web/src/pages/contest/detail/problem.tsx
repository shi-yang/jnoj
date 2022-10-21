import { getContestProblem } from "@/api/contest";
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom"
import Description from "@/pages/problem/detail/description";
import styles from './style/problem.module.less';
import { Grid, Typography } from "@arco-design/web-react";

export default () => {
  const params = useParams();
  const [loading, setLoading] = useState(true);
  const [problem, setProblem] = useState({});
  const [language, setLanguage] = useState(0);
  function fetchData() {
    setLoading(true);
    getContestProblem(params.id, params.key)
      .then((res) => {
        console.log('aa', res.data)
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
    <>
      { !loading && (
        <>
          <div className={styles.container}>
            <Grid.Row className={styles.header} justify="space-between" align="center">
              <Grid.Col span={24}>
                <Typography.Title className={styles.title} heading={5}>
                {params.key} - {problem.statements[language].name}
                </Typography.Title>
              </Grid.Col>
            </Grid.Row>
          </div>
          <div className={styles['problem-layout']}>
            <div className='container'>
              {!loading && (
                <Description problem={problem} language={language} />
              )}
            </div>
          </div>
        </>
      )}
    </>
  )
}
