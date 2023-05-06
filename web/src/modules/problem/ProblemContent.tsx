import useLocale from '@/utils/useLocale';
import { Divider, Typography } from '@arco-design/web-react';
import ReactMarkdown from 'react-markdown';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';
import 'katex/dist/katex.min.css';
import locale from './locale';
import styles from './style/index.module.less';
import { IconCopy } from '@arco-design/web-react/icon';
import { useMemo } from 'react';
import React from 'react';
const { Title, Paragraph } = Typography;
export default function ProblemContent({problem, language}: any) {
  const t = useLocale(locale);
  const refsById = useMemo(() => {
    const refs = {};
    problem.sampleTests.forEach((_, index) => {
        refs[index+'input'] = React.createRef();
        refs[index+'output'] = React.createRef();
    });
    return refs;
  }, [problem.sampleTests]);
  const onCopy = (ref) => {
    ref.current.innerHTML = t['copied'];
    setTimeout(() => {
      if (ref.current) {
        ref.current.innerHTML = t['copy'];
      }
    }, 1000);
  };
  return (
    <Typography className={styles.content}>
      <Paragraph type='secondary' spacing='close'>
        {t['timeLimit']}：{problem.timeLimit / 1000} s
        <Divider type='vertical' />
        {t['memoryLimit']}：{problem.memoryLimit} MB
      </Paragraph>
      <Paragraph>
        <ReactMarkdown
          remarkPlugins={[remarkMath]}
          rehypePlugins={[rehypeKatex]}
        >
          {problem.statements[language].legend}
        </ReactMarkdown>
      </Paragraph>
      { problem.statements[language].input != '' &&
        <>
          <Title className={styles['subtitle']} heading={5}>{t['input']}</Title>
          <Paragraph>
            <ReactMarkdown
              remarkPlugins={[remarkMath]}
              rehypePlugins={[rehypeKatex]}
            >
              {problem.statements[language].input}
            </ReactMarkdown>
          </Paragraph>
        </>
      }
      { problem.statements[language].output != '' &&
        <>
          <Title className={styles['subtitle']} heading={5}>{t['output']}</Title>
          <Paragraph>
            <ReactMarkdown
              remarkPlugins={[remarkMath]}
              rehypePlugins={[rehypeKatex]}
            >
              {problem.statements[language].output}
            </ReactMarkdown>
          </Paragraph>
        </>
      }
      <Title className={styles['subtitle']} heading={5}>{t['sample']}</Title>
      {
        problem.sampleTests.map((item, index) => {
          return (
            <div className={styles['sample-test']} key={index}>
              <div className={styles.input}>
                <h4>
                  {t['input']} {index + 1}
                  <CopyToClipboard text={item.input} onCopy={() => onCopy(refsById[index+'input'])}>
                    <span className={styles['btn-copy']}>
                      <IconCopy />
                      <span ref={refsById[index+'input']}>{t['copy']}</span>
                    </span>
                  </CopyToClipboard>
                </h4>
                <pre>{item.input}</pre>
              </div>
              <div className={styles.output}>
                <h4>
                  {t['output']} {index + 1}
                  <CopyToClipboard text={item.output} onCopy={() => onCopy(refsById[index+'output'])}>
                    <span className={styles['btn-copy']}>
                      <IconCopy />
                      <span ref={refsById[index+'output']}>{t['copy']}</span>
                    </span>
                  </CopyToClipboard>
                </h4>
                <pre>{ item.output }</pre>
              </div>
            </div>
          );
        })
      }
      { problem.statements[language].note != '' &&
        <>
          <Title className={styles['subtitle']} heading={5}>{t['notes']}</Title>
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
      { problem.source != '' &&
        <>
          <Title className={styles['subtitle']} heading={5}>{t['source']}</Title>
          <Paragraph>
            <ReactMarkdown
              remarkPlugins={[remarkMath]}
              rehypePlugins={[rehypeKatex]}
            >
              {problem.source}
            </ReactMarkdown>
          </Paragraph>
        </>
      }
    </Typography>
  );
}