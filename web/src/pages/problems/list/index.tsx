import React, { useState, useEffect, useRef } from 'react';
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
  Tag,
  Tooltip,
  Divider,
  Input,
} from '@arco-design/web-react';
import { IconDownload, IconLanguage, IconSearch } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import SearchForm from './form';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import CreateModal from './create';
import { downloadProblems, getProblem, listProblems } from '@/api/problem';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import { setting, SettingState } from '@/store/reducers/setting';
import ProblemContent from '@/modules/problem/ProblemContent';
import Head from 'next/head';
import { FormatTime } from '@/utils/format';
const { Title } = Typography;

export default function Index() {
  const t = useLocale(locale);
  const user = useAppSelector(userInfo);
  const settings = useAppSelector<SettingState>(setting);
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
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const inputRef = useRef(null);

  const columns = [
    {
      title: t['searchTable.columns.id'],
      dataIndex: 'id',
      sorter: true,
      align: 'center' as 'center',
      width: 120,
      render: (value, record) => (
      <>
        <Typography.Text copyable>{value}</Typography.Text>
        {record.allowDownload && <Tooltip mini content={t['allowDownload']}>
          <IconDownload />
        </Tooltip>}
      </>
      ),
    },
    {
      title: t['searchTable.columns.name'],
      dataIndex: 'name',
      render: (col, record) =>
        <div style={{display: 'flex', justifyContent: 'space-between'}}>
          {col}
          <Space>{record.tags.map((item, index) => <Tag key={index}>{item}</Tag>)}</Space>
        </div>
    },
    {
      title: t['searchTable.columns.source'],
      dataIndex: 'source',
      align: 'center' as 'center',
    },
    {
      title: t['searchTable.columns.type'],
      dataIndex: 'type',
      align: 'center' as 'center',
      render: (x) => x === 'DEFAULT' ? t['searchTable.columns.type.default'] : t['searchTable.columns.type.function'],
      width: 150,
      filters: [
        {
          text: t['searchTable.columns.type.default'],
          value: 0,
        },
        {
          text: t['searchTable.columns.type.function'],
          value: 1,
        },
      ],
      filterMultiple: true,
    },
    {
      title: t['searchTable.columns.status'],
      dataIndex: 'status',
      align: 'center' as 'center',
      render: (x) => x === 1 ? t['searchTable.columns.status.private'] : t['searchTable.columns.status.public'],
      width: 80,
      filters: [
        {
          text: t['searchTable.columns.status.private'],
          value: 1,
        },
        {
          text: t['searchTable.columns.status.public'],
          value: 2,
        },
      ],
      filterMultiple: true,
    },
    {
      title: t['searchTable.columns.createdBy'],
      dataIndex: 'username',
      align: 'center' as 'center',
      render: (_, record) => <Link href={`/u/${record.userId}`}>{record.nickname}</Link>,
      filterMultiple: false,
      filterIcon: <IconSearch />,
      filterDropdown: ({ filterKeys, setFilterKeys, confirm }) => {
        return (
          <div className='arco-table-custom-filter'>
            <Input.Search
              ref={inputRef}
              searchButton
              placeholder='Please enter name'
              value={filterKeys[0] || ''}
              onChange={(value) => {
                setFilterKeys(value ? [value] : []);
              }}
              onSearch={() => {
                confirm();
              }}
            />
          </div>
        );
      },
      onFilterDropdownVisibleChange: (visible) => {
        if (visible) {
          setTimeout(() => inputRef.current.focus(), 150);
        }
      },
    },
    {
      title: t['searchTable.columns.createdAt'],
      dataIndex: 'createdAt',
      align: 'center' as 'center',
      render: col => FormatTime(col, 'YYYY-MM-DD')
    },
    {
      title: t['searchTable.columns.operations'],
      dataIndex: 'operations',
      align: 'center' as 'center',
      width: 200,
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

  function onChangeTable({ current, pageSize }, sorter, filters) {
    const params = {};
    if (sorter.direction) {
      if (sorter.direction == 'descend') {
        Object.assign(params, {orderBy: 'id desc'});
      } else {
        Object.assign(params, {orderBy: 'id'});
      }
    }
    Object.assign(params, filters);
    setFormParams({...formParams, ...params});
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

  function downloadProblem() {
    downloadProblems(selectedRowKeys)
      .then(res => {
        const a = document.createElement('a');
        a.href = res.data.url;
        a.click();
        document.body.removeChild(a);
      })
      .catch(err => {
        Message.error(err.response.data.message);
      });
  }

  return (
    <>
      <Head>
        <title>{`${t['page.title']} - ${settings.name}`}</title>
      </Head>
      <div className={styles['list-container']}>
        <Card className='container'>
          <Title heading={3}>{t['page.title']}</Title>
          <Typography.Paragraph>
            {t['page.desc']}
          </Typography.Paragraph>
          <Typography.Text type='secondary'>{t['page.desc2']}</Typography.Text>
          <Divider />
          <SearchForm onSearch={handleSearch} />
          <div className={styles['button-group']}>
            <Space>
              <CreateModal />
            </Space>
            <Space>
              <Button
                icon={<IconDownload />}
                disabled={selectedRowKeys.length === 0}
                onClick={downloadProblem}
              >
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
            rowSelection={{
              type: 'radio',
              selectedRowKeys,
              onChange: (selectedRowKeys, selectedRows) => {
                setSelectedRowKeys(selectedRowKeys);
              },
              onSelect: (selected, record, selectedRows) => {
              },
            }}
          />
          <ProblemView id={id} visible={visible} onCancel={() => {setVisible(false);}} />
        </Card>
      </div>
    </>
  );
}

function ProblemView({id, visible, onCancel}: {id: number, visible: boolean, onCancel?: (e: MouseEvent | Event) => void;}) {
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
          };
        });
        setLanguageOptions(langs);
      });
    }
  }, [id]);
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
  );
}
