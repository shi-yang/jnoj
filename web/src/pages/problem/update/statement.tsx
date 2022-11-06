import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, List, Message, Grid, Tabs, Tag, Popconfirm } from '@arco-design/web-react';
import MarkdownEditor from '@uiw/react-markdown-editor';
import { useEffect, useRef, useState } from 'react';
import locale from './locale';
import CreateStatementModal from './create-statement';
import { createProblemStatement, listProblemStatements } from '@/api/problem-statement';
import styles from './style/statement.module.less';
import { IconDelete, IconEdit } from '@arco-design/web-react/icon';
const FormItem = Form.Item;
const TabPane = Tabs.TabPane;
const Row = Grid.Row;
const Col = Grid.Col;
export default (props) => {
  const t = useLocale(locale);
  const [form] = Form.useForm();
  const [statements, setStatements] = useState([]);
  const [current, setCurrent] = useState(0);
  function fetchData() {
    listProblemStatements(props.problem.id).then(res => {
      const d = res.data.data;
      setStatements(res.data.data);
      if (d.length > 0) {
        form.setFieldsValue({
          name: d[0].name,
          legend: d[0].legend,
          input: d[0].input,
          output: d[0].output,
          notes: d[0].notes,
        });
      }
    })
  }
  function onSubmit(values) {
    createProblemStatement(props.problem.id, values).then(res => {
      Message.info('已保存')
    })
  }
  function editStatement(index) {
    setCurrent(0);
    form.setFieldsValue({
      name: statements[index].name,
      legend: statements[index].legend,
      input: statements[index].input,
      output: statements[index].output,
      notes: statements[index].notes,
    })
  }
  function deleteStatement(index) {
    Message.info('尚未上线，敬请期待')
  }
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <Card>
      <Row gutter={64}>
        <Col span={4}>
          <List
            className={styles['list-actions']}
            bordered
            footer={<CreateStatementModal />}
          >
            {statements.map((item, index) => (
                <List.Item key={index} actions={[
                  <Button onClick={() => editStatement(index)}>
                    <IconEdit />
                  </Button>,
                  <Popconfirm
                    title='Are you sure you want to delete?'
                    onOk={() => deleteStatement(index)}
                  >
                    <Button><IconDelete /></Button>
                  </Popconfirm>
                ]}>
                <List.Item.Meta
                  title={item.name}
                  description={(<Tag>{item.language}</Tag>)}
                />
              </List.Item>
            ))}
          </List>
        </Col>
        <Col span={20}>
          <Form form={form} layout='vertical' style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
            <FormItem field='name' label={t['name']}>
              <Input />
            </FormItem>
            <FormItem field='legend' label={t['legend']}>
              <MarkdownEditor />
            </FormItem>
            <FormItem field='input' label={t['input']}>
              <MarkdownEditor />
            </FormItem>
            <FormItem field='output' label={t['output']}>
              <MarkdownEditor />
            </FormItem>
            <FormItem field='notes' label={t['notes']}>
              <MarkdownEditor />
            </FormItem>
            <FormItem>
              <Button type='primary' htmlType='submit'>{t['save']}</Button>
            </FormItem>
          </Form>
        </Col>
      </Row>
    </Card>
  )
}