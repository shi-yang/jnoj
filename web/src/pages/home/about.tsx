import React, { ReactNode } from 'react';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import { Card, Link, Space, Typography } from '@arco-design/web-react';
import Head from 'next/head';
import ReactMarkdown from 'react-markdown';
import { IconArrowRight, IconList } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';

const content = `
## 什么是 OJ
Online Judge系统（简称OJ）是一个在线的判题系统。 用户可以在线提交程序多种程序（如C、C++、Java）源代码，系统对源代码进行编译和执行， 并通过预先设计的测试数据来检验程序源代码的正确性。

## 在线测评系统整体架构

前台页面从数据库获取题目、比赛列表在浏览器上显示，用户通过浏览器提交的代码直接保存到数据库。 评测程序负责从数据库中取出用户刚刚提交的代码，保存到文件，然后编译，执行，评判，最后将评判结果写回数据库。

## 评测程序
评测程序就是对用户提交的代码进行编译，执行，将执行结果和OJ后台正确的测试数据进行比较，如果答案和后台数据完全相同就是 Accept，也就是你的程序是正确的。否则返回错误信息。 测评程序会不断扫描数据库，一旦出现没有评判的题目会立即进行评判。
同时，为了防止用户提交恶意代码破坏系统，测评程序会对所提交程序调用的函数及程序运行权限进行限制。
`;
export default function About() {
  const settings = useAppSelector<SettingState>(setting);
  return (
    <div className='container' style={{padding: '20px'}}>
      <Head>
        <title>{`About - ${settings.name}`}</title>
      </Head>
      <Card>
        <ReactMarkdown>
          {content}
        </ReactMarkdown>
      </Card>
      <Link href='/problems' className={styles['problem-link']}>
        <Card
          className={styles['card-with-icon-hover']}
          hoverable
          bordered
        >
          <Content>
            <span className={styles['icon-hover']}>
              <IconArrowRight
                style={{
                  cursor: 'pointer',
                }}
              />
            </span>
          </Content>
        </Card>
      </Link>
    </div>
  );
}

const Content = ({ children }:{ children: ReactNode }) => {
  const t = useLocale(locale);
  return (
    <Space
      style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
      }}
    >
      <Space>
        <IconList />
        <Typography.Text>{t['problemList']}</Typography.Text>
      </Space>
      {children}
    </Space>
  );
};