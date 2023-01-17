import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import { userInfo } from '@/store/reducers/user';
import useLocale from '@/utils/useLocale';
import {
  Button, Card, Descriptions, Form, Grid, Input, Message,
  Modal, Typography, Link, Pagination, PaginationProps, Tabs
} from '@arco-design/web-react'
import { IconPlus, IconUser } from '@arco-design/web-react/icon';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import locale from './locale';
import styles from './style/all.module.less';

export default () => {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);
  const [groups, setGroups] = useState([]);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: false,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100]
  });
  const user = useAppSelector(userInfo);
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize]);
  function fetchData() {
    const { current, pageSize } = pagination;
    const params = {
      page: current,
      perPage: pageSize,
    };
    setGroups([
      { id: 1, name: 'test1', description: '123123', userCount: '12'},
      { id: 2, name: 'test2', description: '123123', userCount: '12'},
      { id: 3, name: 'test3', description: '123123', userCount: '12'},
      { id: 4, name: 'test4', description: '123123', userCount: '12'},
      { id: 5, name: 'test5', description: '123123', userCount: '12'},
      { id: 6, name: 'test6', description: '123123', userCount: '12'},
      { id: 7, name: 'test7', description: '123123', userCount: '12'},
    ])
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
      <div className='container'>
        <Card
          title={t['page.title']}
          extra={
            <div>
              { user.id && <AddGroup />}
            </div>
          }
        >
          <Tabs
            type='rounded'
            style={{marginBottom: '10px'}}
            extra={
              <Input.Search
                style={{ width: '240px' }}
              />
            }
          >
            <Tabs.TabPane key="mygroup" title={t['index.tab.mygroup']} />
            <Tabs.TabPane key="all" title={t['index.tab.allgroup']} />
          </Tabs>
          <Grid.Row gutter={24} className={styles['card-content']}>
            {groups.map((item, index) => (
              <Grid.Col xs={24} sm={12} md={8} lg={6} xl={6} xxl={6} key={index}>
                <Link className={styles['card-block']} href={`/groups/${item.id}`}>
                  <Card
                    bordered={true}
                    size='small'
                    title={
                      item.name
                    }
                  >
                    <div className={styles.content}>
                      <Typography.Paragraph
                        className={styles['description']}
                        ellipsis={{ showTooltip: true, cssEllipsis: true, rows: 2 }}
                      >
                        {item.description}
                      </Typography.Paragraph>
                    </div>
                    <Descriptions
                      size="small"
                      data={[
                        { label: <IconUser />, value: item.userCount  },
                      ]}
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
  )
}

function AddGroup() {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const router = useRouter();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      Message.error('暂未支持');
    });
  }

  return (
    <div>
      <Button type='primary' style={{ marginBottom: 10 }} icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['index.createGroup']}
      </Button>
      <Modal
        title={t['index.createGroup']}
        visible={visible}
        onOk={onOk}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <Form
          form={form}
        >
          <Form.Item label={t['index.create.form.name']} required field='name' rules={[{ required: true }]}>
            <Input placeholder='' />
          </Form.Item>
          <Form.Item label={t['index.create.form.description']} field='description'>
            <Input.TextArea placeholder='' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
}
