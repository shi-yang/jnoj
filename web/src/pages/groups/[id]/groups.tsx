import React, { useContext, useEffect, useState } from 'react';
import { Avatar, Card, Empty, Grid, Input, Link, Pagination, Radio, Select, Space, Tabs, Typography, PaginationProps, Form, Message, Button, Modal } from '@arco-design/web-react';
import Layout from './Layout';
import context from './context';
import { isLogged } from '@/utils/auth';
import { IconPlus, IconUser } from '@arco-design/web-react/icon';
import { createGroup, listGroups } from '@/api/group';
import styles from './style/index.module.less';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { useRouter } from 'next/router';

function AddGroup({callback}: {callback: () => void}) {
  const group = useContext(context);
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const router = useRouter();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      values.parentId = group.id;
      values.type = 'GROUP';
      createGroup(values)
        .then(res => {
          callback();
          setVisible(false);
          Message.success(t['index.create.savedSuccessfully']);
          router.push(`/groups/${res.data.id}`);
        });
    });
  }

  return (
    <div>
      <Button type='outline' icon={<IconPlus />} onClick={() => setVisible(true)}>
        创建小组
      </Button>
      <Modal
        title='创建小组'
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

function Groups() {
  const group = useContext(context);
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
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize, JSON.stringify(formParams)]);

  function fetchData() {
    const { current, pageSize } = pagination;
    const params = {
      page: current,
      perPage: pageSize,
      parentId: group.id,
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
  function onChange(current, pageSize) {
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }
  return (
    <Card>
      <div className={styles['search-form-wrapper']}>
        <Grid.Row gutter={24}>
          <Grid.Col span={8}>
            <Input.Search
              style={{ width: '240px' }}
              onSearch={(value) => {
                setFormParams({...formParams, name: value});
              }}
            />
          </Grid.Col>
        </Grid.Row>
        <div className={styles['right-button']}>
          {group.role === 'ADMIN' && <AddGroup callback={fetchData} />}
        </div>
      </div>
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
  );
}

Groups.getLayout = Layout;
export default Groups;
