import React from 'react';
import Layout from '@theme/Layout';
import Link from '@docusaurus/Link';
import useBaseUrl from '@docusaurus/useBaseUrl';
import clsx from 'clsx';
import styles from './apis.module.css';

const sections = [
  {
    title: <>用户</>,
    link: '/api/interface/v1/user',
    description: (
      <>
        用户登录
      </>
    ),
  },
  {
    title: <>小组</>,
    link: '/api/interface/v1/group',
    description: (
      <>
        小组列表、小组管理
      </>
    ),
  },
  {
    title: <>比赛</>,
    link: '/api/interface/v1/contest',
    description: (
      <>
        新增比赛、比赛管理、比赛提交、比赛榜单
      </>
    ),
  },
  {
    title: <>题目</>,
    link: '/api/interface/v1/problem',
    description: (
      <>
        题目列表、新增题目、编辑题目
      </>
    ),
  },
  {
    title: <>提交</>,
    link: '/api/interface/v1/submission',
    description: (
      <>
        新增提交、提交列表
      </>
    ),
  },
  {
    title: <>Sandbox</>,
    link: '/api/interface/v1/sandboxs',
    description: (
      <>
        Sandbox
      </>
    ),
  },
];

/**
 *
 * @param {{
 *   title: string | React.ReactNode;
 *   description: string | React.ReactNode;
 *   link?: string;
 * }} param0
 */
function Section({ title, description, link }) {
  const sectionComponent = <h3>{title}</h3>;
  const fullLink = useBaseUrl(link);
  return (
    <div className={clsx('col col--6', styles.feature, styles.featuresCol)}>
      {link ? <Link to={fullLink}>{sectionComponent}</Link> : sectionComponent}
      <p>{description}</p>
    </div>
  );
}

function Docs() {
  return (
    <Layout title="Redocusaurus Example" description="With different use-cases">
      <header className={clsx('hero hero--primary', styles.heroBanner)}>
        <div className="container">
          <h1 className="hero__title">JNOJ API Document</h1>
          <p>API 列表</p>
        </div>
      </header>
      <main>
        {sections && sections.length > 0 && (
          <section className={styles.features}>
            <div className="container">
              <div className="row">
                {sections.map((props, idx) => (
                  <Section key={idx} {...props} />
                ))}
              </div>
            </div>
          </section>
        )}
      </main>
    </Layout>
  );
}

export default Docs;
