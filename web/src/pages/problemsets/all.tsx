import { createProblemset, listProblemsets } from '@/api/problemset';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import { userInfo } from '@/store/reducers/user';
import useLocale from '@/utils/useLocale';
import { Button, Card, Descriptions, Form, Grid, Input, Message, Modal, Typography, Link, Pagination, PaginationProps } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import Head from 'next/head';
import { useRouter } from 'next/router';
import React, { useEffect, useState } from 'react';
import locale from './locale';
import styles from './style/all.module.less';

export default function All() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);
  const [problemsets, setProblemsets] = useState([]);
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
              { user.id && <AddProblemset />}
            </div>
          }
        >
          <Grid.Row gutter={24} className={styles['card-content']}>
            {problemsets.map((item, index) => (
              <Grid.Col xs={24} sm={12} md={8} lg={6} xl={6} xxl={6} key={index}>
                <Link className={styles['card-block']} href={`/problemsets/${item.id}`}>
                  <Card
                    bordered={true}
                    size='small'
                    title={
                      item.name
                    }
                  >
                    <div className={styles.content}>
                      <Typography.Paragraph className={styles['description']} ellipsis={{ showTooltip: true, cssEllipsis: true, rows: 2 }}>
                        {item.description}
                      </Typography.Paragraph>
                    </div>
                    <Descriptions
                      size="small"
                      data={[
                        { label: '题目数量', value: item.problemCount  },
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
  );
}

function AddProblemset() {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  const router = useRouter();

  function onOk() {
    form.validate().then((values) => {
      setConfirmLoading(true);
      createProblemset(values)
        .then(res => {
          setVisible(false);
          Message.success(t['all.create.savedSuccessfully']);
          router.push(`/problemsets/${res.data.id}/update`);
        })
        .catch(err => {
          Message.error(err.response.data.message);
        })
        .finally(() => {
          setConfirmLoading(false);
        });
    });
  }

  return (
    <div>
      <Button type='primary' style={{ marginBottom: 10 }} icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['all.createProblemset']}
      </Button>
      <Modal
        title={t['all.createProblemset']}
        visible={visible}
        onOk={onOk}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <Form
          form={form}
        >
          <Form.Item label={t['all.create.form.name']} required field='name' rules={[{ required: true }]}>
            <Input placeholder='' />
          </Form.Item>
          <Form.Item label={t['all.create.form.description']} field='description'>
            <Input.TextArea placeholder='' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
}
