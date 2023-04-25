import React, { useContext, useEffect, useState } from 'react';
import {
  Form,
  Input,
  Grid,
} from '@arco-design/web-react';
import { GlobalContext } from '@/context';
import locale from './locale';
import useLocale from '@/utils/useLocale';
import styles from './style/index.module.less';
import CreateModal from './create';
import { getGroup } from '@/api/group';

const { Row, Col } = Grid;
const { useForm } = Form;

function SearchForm(props: {
  onSearch: (values: Record<string, any>) => void;
  groupId?: number
}) {
  const { lang } = useContext(GlobalContext);

  const t = useLocale(locale);
  const [form] = useForm();

  const handleSubmit = () => {
    const values = form.getFieldsValue();
    props.onSearch(values);
  };

  const [canCreateContest, setCanCreateContest] = useState(true);

  useEffect(() => {
    if (props.groupId) {
      getGroup(props.groupId).then(res => {
        if (res.data.role === 'GUEST' || res.data.role === 'MEMBER')
        setCanCreateContest(false);
      });
    }
  }, []);

  const colSpan = lang === 'zh-CN' ? 8 : 12;

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
          <Col span={colSpan}>
            <Form.Item label={t['problem.columns.name']} field="name">
              <Input.Search
                searchButton
                allowClear
                placeholder={t['searchForm.name.placeholder']}
                onSearch={handleSubmit}
              />
            </Form.Item>
          </Col>
        </Row>
      </Form>
      <div className={styles['right-button']}>
        { canCreateContest && <CreateModal groupId={props.groupId} /> }
      </div>
    </div>
  );
}

export default SearchForm;
