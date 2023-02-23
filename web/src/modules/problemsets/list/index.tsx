import React, { useState, useEffect, useMemo, useContext } from 'react';
import {
  Table,
  Card,
  PaginationProps,
  Grid,
  Button,
  Checkbox,
  Dropdown,
  Input,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import { getColumns } from './constants';
import { listProblemsetProblems } from '@/api/problemset';
import { GlobalContext } from '@/context';
import { IconSettings } from '@arco-design/web-react/icon';
import useStorage from '@/utils/useStorage';
const { Row, Col } = Grid;

function ProblemList({problemsetID = 0}: {problemsetID: number}) {
  const t = useLocale(locale);
  const { lang } = useContext(GlobalContext);
  const [displayFields, setDisplayFields] = useStorage('displayField', '');
  const columns = useMemo(() => getColumns(t, displayFields), [t, displayFields]);
  const [problems, setProblems] = useState([]);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100]
  });
  const [loading, setLoading] = useState(true);
  const [formParams, setFormParams] = useState({});
  const colSpan = lang === 'zh-CN' ? 8 : 12;

  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize, JSON.stringify(formParams)]);

  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    const params = {
      page: current,
      perPage: pageSize,
      ...formParams,
    };
    listProblemsetProblems(problemsetID, params)
      .then((res) => {
        setProblems(res.data.data);
        setPatination({
          ...pagination,
          current,
          pageSize,
          total: res.data.total,
        });
        setLoading(false);
      });
  }

  function onChangeTable({ current, pageSize }) {
    setPatination({
      ...pagination,
      current,
      pageSize,
    });
  }

  function handleSearch(value) {
    setPatination({ ...pagination, current: 1 });
    setFormParams({keyword: value});
  }

  return (
    <Card>
      <div className={styles['search-form-wrapper']}>
        <div
          className={styles['search-form']}
        >
          <Row gutter={24}>
            <Col span={colSpan}>
              <Input.Search
                searchButton
                placeholder={t['searchForm.name.placeholder']}
                onSearch={handleSearch}
              />
            </Col>
          </Row>
        </div>
        <div className={styles['right-button']}>
          <Dropdown droplist={
            <Card bordered>
              <Checkbox.Group
                direction='vertical'
                value={displayFields.split(',')}
                options={[{label: t['searchForm.show.showTags'], value: 'tag'}, {label: t['searchForm.show.showSource'], value: 'source'}]}
                onChange={(v) => setDisplayFields(v.join(','))}
              />
            </Card>
          }>
            <Button type='text'>
              <IconSettings />
            </Button>
          </Dropdown>
        </div>
      </div>
      <Table
        rowKey="id"
        loading={loading}
        onChange={onChangeTable}
        pagination={pagination}
        columns={columns}
        data={problems}
      />
    </Card>
  );
}

export default ProblemList;
