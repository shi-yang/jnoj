import React from 'react';
import { createGroup, listGroups } from '@/api/group';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import { userInfo } from '@/store/reducers/user';
import useLocale from '@/utils/useLocale';
import {
  Button, Card, Form, Grid, Input, Message,
  Modal, Typography, Link, Pagination, PaginationProps, Tabs, Empty, Select, Space, Avatar, Radio, Switch
} from '@arco-design/web-react';
import { IconPlus, IconUser } from '@arco-design/web-react/icon';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import locale from './locale';
import styles from './style/all.module.less';
import './mock';
import { isLogged } from '@/utils/auth';
import PermissionWrapper from '@/components/PermissionWrapper';

export default function Index() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);
  const [groups, setGroups] = useState([]);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: false,
    showTotal: true,
    pageSize: 24,
    current: 1,
    pageSizeChangeResetCurrent: true,
    hideOnSinglePage: true,
  });
  const [formParams, setFormParams] = useState({
    name: '',
    sort: 'joinedAt',
    mygroup: true,
    type: 'GROUP'
  });
  const [activeTab, setActiveTab] = useState('all');
  const user = useAppSelector(userInfo);
  useEffect(() => {
    if (isLogged()) {
      setActiveTab('mygroup');
      setFormParams({...formParams, mygroup: true });
    }
  }, []);
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
    listGroups(params)
      .then(res => {
        setGroups(res.data.data);
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
    setFormParams({...formParams, mygroup: key === 'mygroup' });
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
          extra={
            <PermissionWrapper
              requiredPermissions={[{resource: 'group', actions: ['write']}]}
            >
              <AddGroup />
            </PermissionWrapper>
          }
        >
          <Tabs
            type='rounded'
            style={{marginBottom: '10px'}}
            activeTab={activeTab}
            extra={
              <Space>
                <Radio.Group
                  type='button'
                  name='group'
                  defaultValue='GROUP'
                  onChange={(value) => setFormParams({...formParams, type: value})}
                >
                  <Radio value='GROUP'>小组</Radio>
                  <Radio value='TEAM'>团队</Radio>
                </Radio.Group>
                { activeTab === 'mygroup' &&
                  <Select
                    style={{ width: 120 }}
                    defaultValue='joinedAt'
                    onChange={(value) => setFormParams({...formParams, sort: value})}
                  >
                    <Select.Option value='joinedAt'>
                      {t['index.sort.joinedAt']}
                    </Select.Option>
                    <Select.Option value='createdAt'>
                      {t['index.sort.createdAt']}
                    </Select.Option>
                  </Select>
                }
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
            {isLogged() && <Tabs.TabPane key="mygroup" title={t['index.tab.mygroup']} />}
            <Tabs.TabPane key="all" title={t['index.tab.allgroup']} />
          </Tabs>
          <Grid.Row gutter={24} className={styles['card-content']}>
            {groups.length > 0 && groups.map((item, index) => (
              <Grid.Col xs={24} sm={12} md={8} lg={6} xl={6} xxl={6} key={index}>
                <Link className={styles['card-block']} href={`/groups/${item.id}`}>
                  <Card
                    bordered={true}
                    size='small'
                    actions={[
                      <span key={index} className='icon-hover'>
                        <IconUser /> {item.memberCount}
                      </span>,
                    ]}
                  >
                    <Card.Meta
                      avatar={
                        <Space>
                          <Avatar size={24} style={{ backgroundColor: '#3370ff' }}>
                            <IconUser />
                          </Avatar>
                          <Typography.Text>{item.userNickname}</Typography.Text>
                        </Space>
                      }
                      title={item.name}
                      description={
                        <div className={styles.content}>
                          <Typography.Paragraph
                            className={styles['description']}
                            ellipsis={{ showTooltip: true, cssEllipsis: true, rows: 2 }}
                          >
                            {item.description}
                          </Typography.Paragraph>
                        </div>
                      }
                    />
                  </Card>
                </Link>
              </Grid.Col>
            ))}
            {groups.length === 0 && (
              <Empty />
            )}
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
};

function AddGroup() {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const router = useRouter();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      createGroup(values)
        .then(res => {
          setVisible(false);
          Message.success(t['index.create.savedSuccessfully']);
          router.push(`/groups/${res.data.id}`);
        });
    });
  }

  return (
    <div>
      <Button type='outline' icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['index.create']}
      </Button>
      <Modal
        title={t['index.create']}
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
          <Form.Item label={t['index.create.form.type']} required field='type' rules={[{ required: true}]}
            help='创建后不可修改。小组与团队的区别：一个团队可以包含多个小组，即在团队下还可以创建多个小组'
          >
            <Radio.Group
              type='button'
              defaultValue='GROUP'
            >
              <Radio value='GROUP'>小组</Radio>
              <Radio value='TEAM'>团队</Radio>
            </Radio.Group>
          </Form.Item>
          <Form.Item label={t['index.create.form.description']} field='description'>
            <Input.TextArea placeholder='' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
}
