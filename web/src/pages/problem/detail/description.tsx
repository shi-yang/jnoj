import { Divider, Typography } from '@arco-design/web-react';
const { Title, Paragraph } = Typography;
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/description.module.less'
import ReactMarkdown from 'react-markdown';
import ProblemContent from '@/components/Problem/ProblemContent';
const Description = ({ problem, language }) => {
  const t = useLocale(locale);
  return (
    <div className={styles.descriptionContent}>
      <div className={styles.description}>
        <ProblemContent problem={problem} language={language} />
      </div>
    </div>
  );
};

export default Description;
