import React, { useState, useEffect, useRef } from 'react';
import {
  Table,
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
  Modal,
  Form,
  Alert,
} from '@arco-design/web-react';
import { IconDownload, IconLanguage, IconPlus, IconSearch } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import SearchForm from './form';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import CreateModal from './create';
import { downloadProblems, getProblem, getProblemVerification, listProblems } from '@/api/problem';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import ProblemContent from '@/modules/problem/ProblemContent';
import { FormatTime } from '@/utils/format';
import SubmissionList from '@/modules/submission/SubmissionList';
import CodeMirror from '@uiw/react-codemirror';
import { getProblemLanguage, listProblemLanguages } from '@/api/problem-file';
import { createSubmission } from '@/api/submission';
import useStorage from '@/utils/useStorage';

function Page() {
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
      render: (x) => t['searchTable.columns.type.' + x.toLowerCase()],
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
      type: [0, 1],
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
    <div>
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
    </div>
  );
}

export default Page;

function ProblemView({id, visible, onCancel}: {id: number, visible: boolean, onCancel?: (e: MouseEvent | Event) => void;}) {
  const [problem, setProblem] = useState({
    id: 0,
    name: '',
    type: '',
    statements: [],
    sampleTests: []
  });
  const [codeLanguage, setCodeLanguage] = useStorage('CODE_LANGUAGE', '1');
  const [language, setLanguage] = useState(0);
  const [statementLanguageOptions, setStatementLanguageOptions] = useState([]);
  const [modalVisible, setModalVisible] = useState(false);
  const [form] = Form.useForm();
  const [languageOptions, setLanguageOptions] = useState([]);
  const [lastSubmissionID, setLastSubmissionID] = useState(0);
  const [verification, setVerification] = useState({verificationStatus: 0, verificaitonInfo: []});
  const ref = useRef(null);
  function onOk() {
    form.validate().then((values) => {
      const data = {
        problemNumber: problem.id,
        source: values.content,
        language: codeLanguage,
        entityId: problem.id,
        entityType: 'PROBLEM_VERIFY'
      };
      createSubmission(data).then(res => {
        Message.success('已提交');
        setLastSubmissionID(res.data.id);
        setModalVisible(false);
        ref.current.fetchData();
      }).catch(err => {
        if (err.response.data.reason === 'SUBMISSION_RATE_LIMIT') {
          Message.error('您的提交过于频繁');
        }
      });
    });
  }
  function onLanguageChange(e) {
    setCodeLanguage(e);
    if (problem.type === 'FUNCTION') {
      const lang = languageOptions.find(item => {
        return item.languageCode === Number(e);
      });
      getProblemLanguage(problem.id, lang.id)
        .then(res => {
          form.setFieldValue('content', res.data.userContent);
        });
    }
  }
  useEffect(() => {
    if (id === 0) {
      return;
    }
    getProblem(id).then(res => {
      setProblem(res.data);
      const langs = res.data.statements.map((item, index) => {
        return {
          label: item.language,
          value: index,
        };
      });
      setStatementLanguageOptions(langs);
    });
    listProblemLanguages(id)
      .then(res => {
        const langs = res.data.data;
        setLanguageOptions(langs);
      });
    getProblemVerification(id)
      .then(res => {
        setVerification(res.data);
      });
  }, [id]);
  return (
    <Drawer
      width={1100}
      title={<span>{problem.id} - {problem.name}</span>}
      visible={visible}
      footer={null}
      onCancel={onCancel}
    >
      {
        statementLanguageOptions.length > 0 &&
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
                <IconLanguage /> {statementLanguageOptions[language].label}
              </span>
            }
          >
            {statementLanguageOptions.map((option, index) => (
              <Select.Option key={index} value={option.value}>
                {option.label}
              </Select.Option>
            ))}
          </Select>
          <Typography.Title heading={4}>{problem.statements[language].name}</Typography.Title>
          <ProblemContent problem={problem} statement={problem.statements[language]} />
          <Divider />
          <Typography.Title heading={4}>测试-验题区域</Typography.Title>
          {
            verification.verificationStatus !== 3 ? (
              <Alert
                type='warning'
                content='本题目尚未通过校验，请先到题目基本信息页进行题目校验，通过校验后方可进行测试'
              />
            ) : (
              <div>
                <Button type='primary' icon={<IconPlus />} onClick={() => setModalVisible(true)}>提交代码</Button>
                <Modal
                  title='添加'
                  style={{width: '800px'}}
                  visible={modalVisible}
                  onOk={onOk}
                  onCancel={() => setModalVisible(false)}
                  autoFocus={false}
                  focusLock={true}
                >
                  <Form
                    form={form}
                  >
                    <Form.Item field='language' label='语言' required>
                      <Select onChange={onLanguageChange}>
                        {languageOptions.map((item, index) => {
                          return (
                            <Select.Option key={index} value={`${item.languageCode}`}>
                              {item.languageName}
                            </Select.Option>
                          );
                        })}
                      </Select>
                    </Form.Item>
                    <Form.Item field='content' label='源码' required>
                      <CodeMirror
                        height="400px"
                      />
                    </Form.Item>
                  </Form>
                </Modal>
                <SubmissionList ref={ref} pid={problem.id} entityType={3} />
              </div>
            )
          }
        </div>
      }
    </Drawer>
  );
}
