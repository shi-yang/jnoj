import { Button, Drawer, Link, List, PaginationProps } from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/description.module.less';
import ProblemContent from '@/components/Problem/ProblemContent';
import React, { useContext, useEffect, useState } from 'react';
import { IconLeft, IconRight } from '@arco-design/web-react/icon';
import { listProblemsetProblems } from '@/api/problemset';
import ProblemContext from './context';
import { useRouter } from 'next/router';
import { ProblemStatus } from '@/modules/problemsets/list/constants';

const Description = ({ problemset }: any) => {
  const {language, problem, fetchProblem} = useContext(ProblemContext);
  const t = useLocale(locale);
  const router = useRouter();
  const problemId = router.query.pid;
  function changeProblem(problemId) {
    router.query.pid = problemId;
    const newURL = `/problemsets/${problemset.id}/problems/${problemId}`;
    window.history.replaceState({ ...window.history.state, as: newURL, url: newURL }, '', newURL);
    fetchProblem(problemId);
  }
  return (
    <div className={styles.descriptionContent}>
      <div className={styles.description}>
        { problem.statements.length > 0 && <ProblemContent problem={problem} language={language} />}
      </div>
      <div className={styles.footer}>
        <div className={styles.left}>
          <Problemset problemset={problemset} />
        </div>
        <div className={styles.right}>
          <Button disabled={Number(problemId) === 1} onClick={(e) => changeProblem(Number(problemId)-1)}>
            <IconLeft />
            {t['description.footer.previous']}
          </Button>
          <Button disabled={Number(problemId) === problemset.problemCount} onClick={(e) => changeProblem(Number(problemId)+1)}>
            {t['description.footer.next']}<IconRight />
          </Button>
        </div>
      </div>
    </div>
  );
};

function Problemset({problemset}: any) {
  const [visible, setVisible] = useState(false);
  const [problems, setProblems] = useState([]);
  const { fetchProblem } = useContext(ProblemContext);
  const [loading, setLoading] = useState(true);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: false,
    pageSize: 20,
    current: 1,
    sizeOptions: [20, 50, 100],
    hideOnSinglePage: true, 
    pageSizeChangeResetCurrent: true,
    onChange: (current, pageSize) => {
      setPatination({
        ...pagination,
        current,
        pageSize,
      });
    }
  });
  const router = useRouter();
  useEffect(() => {
    if (!visible) {
      return;
    }
    fetchData();
  }, [visible, pagination.current, pagination.pageSize]);

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
    fetchProblem(order);
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
          size='small'
          dataSource={problems}
          loading={loading}
          render={(item, index) =>
            <List.Item key={index} extra={ProblemStatus[item.status]}>
              <Button type='text' onClick={() => changeProblem(item.order)}>
                {item.order}. {item.name}
              </Button>
            </List.Item>
          }
          pagination={pagination}
        />
      </Drawer>
    </div>
  );
}

export default Description;
