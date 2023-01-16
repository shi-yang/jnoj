import React, { useState, useEffect, useMemo } from 'react';
import {
  Table,
  Card,
  PaginationProps,
  Button,
  Space,
  Typography,
  Message,
  Link,
  Drawer,
  Select,
} from '@arco-design/web-react';
import { IconDownload, IconLanguage } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import SearchForm from './form';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import CreateModal from './create';
import { getProblem, listProblems } from '@/api/problem';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import { FormatTime } from '@/utils/format';
import ProblemContent from '@/components/Problem/ProblemContent';
const { Title } = Typography;

export default function() {
  const t = useLocale(locale);
  const user = useAppSelector(userInfo);
  const [data, setData] = useState([]);
  const [pagination, setPagination] = useState<PaginationProps>({
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const [loading, setLoading] = useState(true);
  const [formParams, setFormParams] = useState({});
  const [id, setId] = useState(0);
  const [visible, setVisible] = useState(false);
  const Status = ['', '私有', '公开'];
  const columns = [
    {
      title: t['searchTable.columns.id'],
      dataIndex: 'id',
      align: 'center' as 'center',
      render: (value) => <Typography.Text copyable>{value}</Typography.Text>,
    },
    {
      title: t['searchTable.columns.name'],
      dataIndex: 'name',
    },
    {
      title: t['searchTable.columns.source'],
      dataIndex: 'source',
      align: 'center' as 'center',
    },
    {
      title: t['searchTable.columns.status'],
      dataIndex: 'status',
      align: 'center' as 'center',
      render: (x) => Status[x],
    },
    {
      title: t['searchTable.columns.operations'],
      dataIndex: 'operations',
      align: 'center' as 'center',
      headerCellStyle: { paddingLeft: '15px' },
      render: (_, record) => (
        <>
          <Button type='text' size='small' onClick={() => {
            setId(record.id);
            setVisible(true);
          }}>
            {t['searchTable.columns.operations.view']}
          </Button>
          {
            user.id === record.userId &&
              <Button
                type="text"
                size="small"
              >
                <Link href={`/problems/${record.id}/update`}>{t['searchTable.columns.operations.update']}</Link>
              </Button>
          }
        </>
      ),
    },
  ];
  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize, JSON.stringify(formParams)]);
  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    listProblems({
      page: current,
      perPage: pageSize,
      userId: user.id,
      ...formParams,
    })
      .then((res) => {
        setData(res.data.data);
        setPagination({
          ...pagination,
          current,
          pageSize,
          total: res.data.total,
        });
        setLoading(false);
      });
  }

  function onChangeTable({ current, pageSize }) {
    setPagination({
      ...pagination,
      current,
      pageSize,
    });
  }

  function handleSearch(params) {
    setPagination({ ...pagination, current: 1 });
    setFormParams(params);
  }

  return (
    <Card className='container'>
      <Title heading={6}>{t['menu.list.createProblem']}</Title>
      <SearchForm onSearch={handleSearch} />
      <div className={styles['button-group']}>
        <Space>
          <CreateModal />
        </Space>
        <Space>
          <Button icon={<IconDownload />} onClick={() => {Message.info('开发中，尽情期待')}}>
            {t['searchTable.operation.download']}
          </Button>
        </Space>
      </div>
      <Table
        rowKey="id"
        loading={loading}
        onChange={onChangeTable}
        pagination={pagination}
        columns={columns}
        data={data}
      />
      <ProblemView id={id} visible={visible} onCancel={() => {setVisible(false)}} />
    </Card>
  );
}

function ProblemView({id, visible, onCancel}) {
  const [data, setData] = useState({
    id: 0,
    name: '',
    statements: [],
    sampleTests: []
  });
  const [language, setLanguage] = useState(0);
  const [languageOptions, setLanguageOptions] = useState([]);
  useEffect(() => {
    if (id !== 0) {
      getProblem(id)
      .then(res => {
        setData(res.data);
        const langs = res.data.statements.map((item, index) => {
          return {
            label: item.language,
            value: index,
          }
        });
        setLanguageOptions(langs);
      })
    }
  }, [id])
  return (
    <Drawer
      width={800}
      title={<span>{data.id} - {data.name}</span>}
      visible={visible}
      footer={null}
      onCancel={onCancel}
    >
      { 
        languageOptions.length > 0 &&
        <div>
          <Select
            bordered={false}
            size='small'
            defaultValue={language}
            onChange={(value) =>
              setLanguage(value)
            }
            triggerProps={{
              autoAlignPopupWidth: false,
              autoAlignPopupMinWidth: true,
              position: 'bl',
            }}
            triggerElement={
              <span className={styles['header-language']}>
                <IconLanguage /> {languageOptions[language].label}
              </span>
            }
          >
            {languageOptions.map((option, index) => (
              <Select.Option key={index} value={option.value}>
                {option.label}
              </Select.Option>
            ))}
          </Select>
          <Typography.Title heading={4}>{data.statements[language].name}</Typography.Title>
          <ProblemContent problem={data} language={language} />
        </div>
      }
    </Drawer>
  )
}
