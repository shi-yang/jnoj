import React, { useContext } from 'react';
import {
  Form,
  Input,
  Button,
  Grid,
  Radio,
  Switch,
  Tooltip,
} from '@arco-design/web-react';
import { GlobalContext } from '@/context';
import locale from './locale';
import useLocale from '@/utils/useLocale';
import { IconRefresh, IconSearch } from '@arco-design/web-react/icon';
import styles from './style/index.module.less';

const { Row, Col } = Grid;
const { useForm } = Form;

function SearchForm(props: {
  onSearch: (values: Record<string, any>) => void;
}) {
  const t = useLocale(locale);
  const [form] = useForm();

  const handleSubmit = () => {
    const values = form.getFieldsValue();
    props.onSearch(values);
  };

  const handleReset = () => {
    form.resetFields();
    props.onSearch({});
  };

  return (
    <div className={styles['search-form-wrapper']}>
      <Form
        form={form}
        className={styles['search-form']}
        labelAlign="left"
        labelCol={{ span: 5 }}
        wrapperCol={{ span: 19 }}
      >
        <Row gutter={24}>
          <Col flex='300px'>
            <Form.Item label={t['searchTable.columns.user']} field="userId" >
              <Radio.Group defaultValue={0} type='button'>
                <Tooltip content={t['searchTable.columns.user.tip']}>
                  <Radio value={0}>{t['searchTable.columns.user.all']}</Radio>
                </Tooltip>
                <Radio value={1}>{t['searchTable.columns.user.me']}</Radio>
              </Radio.Group>
            </Form.Item>
          </Col>
          <Col span={8}>
            <Form.Item label={t['searchTable.columns.id']} field="id">
              <Input placeholder={t['searchForm.id.placeholder']} allowClear />
            </Form.Item>
          </Col>
          <Col span={8}>
            <Form.Item label={t['searchTable.columns.keyword']} field="keyword">
              <Input
                allowClear
                placeholder={t['searchForm.keyword.placeholder']}
              />
            </Form.Item>
          </Col>
        </Row>
      </Form>
      <div className={styles['right-button']}>
        <Button type="primary" icon={<IconSearch />} onClick={handleSubmit}>
          {t['searchTable.form.search']}
        </Button>
        <Button icon={<IconRefresh />} onClick={handleReset}>
          {t['searchTable.form.reset']}
        </Button>
      </div>
    </div>
  );
}

export default SearchForm;