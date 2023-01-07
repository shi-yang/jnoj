import { Button, Divider, Drawer, Link, List, PaginationProps, Typography } from '@arco-design/web-react';
const { Title, Paragraph } = Typography;
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/description.module.less'
import ProblemContent from '@/components/Problem/ProblemContent';
import { useContext, useEffect, useState } from 'react';
import { IconLeft, IconRight } from '@arco-design/web-react/icon';
import { listProblemsetProblems } from '@/api/problemset';
import ProblemContext from './context';
import router, { useRouter } from 'next/router';

const Description = ({ language, problemset }) => {
  const {problem, updateProblem} = useContext(ProblemContext);
  const t = useLocale(locale);
  const { pid } = router.query
  function getNextProblem() {
    const next = Number(pid)+1;
    router.push({
      pathname: router.pathname,
      query: { id: problemset.id, pid: next }
    }, undefined, {shallow: true});
    updateProblem(next);
  }
  function getPreviousProblem() {
    const previous = Number(pid)-1;
    router.push({
      pathname: router.pathname,
      query: { id: problemset.id, pid: previous }
    }, undefined, {shallow: true});
    updateProblem(previous);
  }
  return (
    <div className={styles.descriptionContent}>
      <div className={styles.description}>
        <ProblemContent problem={problem} language={language} />
      </div>
      <div className={styles.footer}>
        <div className={styles.left}>
          <Problemset problemset={problemset} />
        </div>
        <div className={styles.right}>
          <Button disabled={Number(pid) === 1} onClick={getPreviousProblem}><IconLeft />{t['description.footer.previous']}</Button>
          <Button disabled={Number(pid) === problemset.problemCount} onClick={getNextProblem}>{t['description.footer.next']}<IconRight /></Button>
        </div>
      </div>
    </div>
  );
};

function Problemset({problemset}) {
  const [visible, setVisible] = useState(false);
  const [problems, setProblems] = useState([]);
  const { updateProblem } = useContext(ProblemContext);
  const [loading, setLoading] = useState(true);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const router = useRouter();
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize]);

  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    const params = {
      page: current,
      perPage: pageSize,
    };
    listProblemsetProblems(problemset.id, params)
      .then((res) => {
        setProblems(res.data.data);
        setPatination({
          ...pagination,
          current,
          pageSize,
          total: res.data.total,
        });
        setLoading(false);
      });
  }
  function changeProblem(order) {
    router.push({
      pathname: router.pathname,
      query: { id: problemset.id, pid: order }
    }, undefined, {shallow: true});
    updateProblem(order);
  }
  return (
    <div>
      <Button
        onClick={() => { setVisible(true); }}
        type='outline'
      >
        题单
      </Button>
      <Drawer
        width={332}
        height={332}
        title={
          <Link href={`/problemsets/${problemset.id}`} target='_blank'>{problemset.name}</Link>
        }
        visible={visible}
        placement='left'
        footer={null}
        onCancel={() => {
          setVisible(false);
        }}
      >
        <List
          style={{ width: 622 }}
          size='small'
          dataSource={problems}
          render={(item, index) =>
            <List.Item key={index}>
              <Button type='text' onClick={() => changeProblem(item.order)}>
                {item.order}. {item.name}
              </Button>
            </List.Item>
          }
        />
      </Drawer>
    </div>
  );
}

export default Description;
