import { Divider, Typography } from '@arco-design/web-react';
const { Title, Paragraph } = Typography;
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/description.module.less'
const Description = ({ problem, language }) => {
  const t = useLocale(locale);
  return (
    <div className={styles.descriptionContent}>
      <div className={styles.description}>
        <Typography className={styles.content}>
          <Paragraph type='secondary' spacing='close'>
            {t['timeLimit']}：{problem.timeLimit / 1000}s
            <Divider type='vertical' />
            {t['memoryLimit']}：{problem.memoryLimit}MB
          </Paragraph>
          <Paragraph>
            {problem.statements[language].legend}
          </Paragraph>
          <Title heading={5}>{t['input']}</Title>
          <Paragraph>
            {problem.statements[language].input}
          </Paragraph>
          <Title heading={5}>{t['output']}</Title>
          <Paragraph>
            {problem.statements[language].output}
          </Paragraph>
          <Title heading={5}>{t['sample']}</Title>
          {
            problem.sampleTests.map((item, index) => {
              return (
                <div className={styles['sample-test']} key={index}>
                  <div className={styles.input}>
                    <h4>{t['input']}</h4>
                    <pre>{item.input}</pre>
                  </div>
                  <div className={styles.output}>
                    <h4>{t['output']}</h4>
                    <pre>{ item.output }</pre>
                  </div>
                </div>
              )
            })
          }
          <Title heading={5}>{t['notes']}</Title>
          <Paragraph>
            {problem.statements[language].notes}
          </Paragraph>
        </Typography>
      </div>
    </div>
  );
};

export default Description;
