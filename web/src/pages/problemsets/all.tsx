import { listProblemsets } from '@/api/problemset';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import useLocale from '@/utils/useLocale';
import { Card, Descriptions, Grid, Typography, Link, Pagination, PaginationProps, Tag, Tabs, Space, Radio, Select, Input } from '@arco-design/web-react';
import Head from 'next/head';
import React, { useEffect, useState } from 'react';
import locale from './locale';
import styles from './style/all.module.less';
import { IconUser } from '@arco-design/web-react/icon';

export default function All() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);
  const [problemsets, setProblemsets] = useState([]);
  const [activeTab, setActiveTab] = useState('all');
  const [formParams, setFormParams] = useState({
    name: '',
    type: undefined,
  });
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: false,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100],
    hideOnSinglePage: true,
  });
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize, JSON.stringify(formParams)]);
  function fetchData() {
    const { current, pageSize } = pagination;
    const params = {
      page: current,
      perPage: pageSize,
      ...formParams,
    };
    listProblemsets(params)
      .then((res) => {
        setProblemsets(res.data.data);
        setPatination({
          ...pagination,
          current,
          pageSize,
          total: Number(res.data.total),
        });
      });
  }
  function onTabsChange(key) {
    setActiveTab(key);
    if (key === 'all') {
      setFormParams({...formParams, type: undefined});
    } else {
      setFormParams({...formParams, type: key });
    }
  }
  function onChange(current, pageSize) {
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }
  return (
    <>
      <Head>
        <title>{`${t['page.title']} - ${settings.name}`}</title>
      </Head>
      <div className='container' style={{padding: '20px'}}>
        <Card
          title={t['page.title']}
        >
          <Tabs
            type='rounded'
            style={{marginBottom: '10px'}}
            activeTab={activeTab}
            extra={
              <Space>
                <Input.Search
                  style={{ width: '240px' }}
                  onSearch={(value) => {
                    setFormParams({...formParams, name: value});
                  }}
                />
              </Space>
            }
            onChange={onTabsChange}
          >
            <Tabs.TabPane key='all' title={t['all.tab.all']} />
            <Tabs.TabPane key='SIMPLE' title={t['all.problemset.type.SIMPLE']} />
            <Tabs.TabPane key='EXAM' title={t['all.problemset.type.EXAM']} />
          </Tabs>
          <Grid.Row gutter={24} className={styles['card-content']}>
            {problemsets.map((item, index) => (
              <Grid.Col xs={24} sm={12} md={8} lg={6} xl={6} xxl={6} key={index}>
                <Link hoverable={false} className={styles['card-block']} href={`/problemsets/${item.id}`}>
                  <Card
                    bordered={true}
                    hoverable
                    size='small'
                    title={
                      item.name
                    }
                    actions={[
                      <span key={index} className='icon-hover'>
                        <IconUser /> {item.memberCount}
                      </span>,
                    ]}
                    extra={<Tag>{t[`all.problemset.type.${item.type}`]}</Tag>}
                  >
                    <div className={styles.content}>
                      <Typography.Paragraph className={styles['description']} ellipsis={{ showTooltip: true, cssEllipsis: true, rows: 2 }}>
                        {item.description}
                      </Typography.Paragraph>
                    </div>
                    <Card.Meta
                      avatar={
                        <Descriptions
                          size="small"
                          data={[
                            { label: '题目数量', value: item.problemCount  },
                          ]}
                        />
                      }
                    />
                  </Card>
                </Link>
              </Grid.Col>
            ))}
          </Grid.Row>
          <Pagination
            style={{ width: 800, marginBottom: 20 }}
            {...pagination}
            onChange={onChange}
          />
        </Card>
      </div>
    </>
  );
}
