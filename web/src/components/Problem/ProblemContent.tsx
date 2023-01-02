import useLocale from "@/utils/useLocale";
import { Divider, Message, Typography } from "@arco-design/web-react"
import ReactMarkdown from "react-markdown";
import { CopyToClipboard } from 'react-copy-to-clipboard';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';
import 'katex/dist/katex.min.css';
import locale from "./locale";
import styles from './style/index.module.less';
import { IconCopy } from "@arco-design/web-react/icon";
const { Title, Paragraph } = Typography;
export default ({problem, language}) => {
  const t = useLocale(locale);
  return (
    <Typography className={styles.content}>
      <Paragraph type='secondary' spacing='close'>
        {t['timeLimit']}：{problem.timeLimit / 1000}s
        <Divider type='vertical' />
        {t['memoryLimit']}：{problem.memoryLimit}MB
      </Paragraph>
      <Paragraph>
        <ReactMarkdown
          remarkPlugins={[remarkMath]}
          rehypePlugins={[rehypeKatex]}
        >
          {problem.statements[language].legend}
        </ReactMarkdown>
      </Paragraph>
      <Title heading={5}>{t['input']}</Title>
      <Paragraph>
        <ReactMarkdown
          remarkPlugins={[remarkMath]}
          rehypePlugins={[rehypeKatex]}
        >
          {problem.statements[language].input}
        </ReactMarkdown>
      </Paragraph>
      <Title heading={5}>{t['output']}</Title>
      <Paragraph>
        <ReactMarkdown
          remarkPlugins={[remarkMath]}
          rehypePlugins={[rehypeKatex]}
        >
          {problem.statements[language].output}
        </ReactMarkdown>
      </Paragraph>
      <Title heading={5}>{t['sample']}</Title>
      {
        problem.sampleTests.map((item, index) => {
          return (
            <div className={styles['sample-test']} key={index}>
              <div className={styles.input}>
                <h4>
                  {t['input']} {index + 1}
                  <CopyToClipboard text={item.input} onCopy={() => Message.success(t['copied'])}>
                    <span className={styles['btn-copy']}><IconCopy />{t['copy']}</span>
                  </CopyToClipboard>
                </h4>
                <pre>{item.input}</pre>
              </div>
              <div className={styles.output}>
                <h4>
                  {t['output']} {index + 1}
                  <CopyToClipboard text={item.output} onCopy={() => Message.success(t['copied'])}>
                    <span className={styles['btn-copy']}><IconCopy />{t['copy']}</span>
                  </CopyToClipboard>
                </h4>
                <pre>{ item.output }</pre>
              </div>
            </div>
          )
        })
      }
      { problem.statements[language].note != '' &&
        <>
          <Title heading={5}>{t['notes']}</Title>
          <Paragraph>
            <ReactMarkdown
              remarkPlugins={[remarkMath]}
              rehypePlugins={[rehypeKatex]}
            >
              {problem.statements[language].note}
            </ReactMarkdown>
          </Paragraph>
        </>
      }
    </Typography>
  )
}