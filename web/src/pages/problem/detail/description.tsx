import { Divider, Typography } from '@arco-design/web-react';
const { Title, Paragraph } = Typography;
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/description.module.less'
const Description = (props) => {
  const t = useLocale(locale);
  return (
    <div className={styles.descriptionContent}>
      <div className={styles.description}>
        <Typography className={styles.content}>
          <Paragraph type='secondary' spacing='close'>
            {t['timeLimit']}：{props.problem.timeLimit / 1000}s
            <Divider type='vertical' />
            {t['memoryLimit']}：{props.problem.memoryLimit}MB
          </Paragraph>
          <Paragraph>
            {props.problem.statements[props.language].legend}
          </Paragraph>
          <Title heading={5}>{t['input']}</Title>
          <Paragraph>
            {props.problem.statements[props.language].input}
          </Paragraph>
          <Title heading={5}>{t['output']}</Title>
          <Paragraph>
            {props.problem.statements[props.language].output}
          </Paragraph>
          <Title heading={5}>{t['sample']}</Title>
          {
            props.problem.sampleTests.map((item, index) => {
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
            {props.problem.statements[props.language].notes}
          </Paragraph>
        </Typography>
      </div>
    </div>
  );
};

export default Description;
