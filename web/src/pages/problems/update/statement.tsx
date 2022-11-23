import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, List, Message, Grid, Tag, Popconfirm } from '@arco-design/web-react';
import { useEffect, useState } from 'react';
import locale from './locale';
import CreateStatementModal from './create-statement';
import { deleteProblemStatement, listProblemStatements, updateProblemStatement } from '@/api/problem-statement';
import styles from './style/statement.module.less';
import { IconDelete, IconEdit } from '@arco-design/web-react/icon';
import Editor from '@/components/MarkdownEditor';

const FormItem = Form.Item;
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
    updateProblemStatement(props.problem.id, statements[current].id, values)
      .then(res => {
        Message.info('已保存')
      })
  }
  function editStatement(index) {
    setCurrent(index);
    form.setFieldsValue({
      name: statements[index].name,
      legend: statements[index].legend,
      input: statements[index].input,
      output: statements[index].output,
      notes: statements[index].notes,
    })
  }
  function deleteStatement(index) {
    deleteProblemStatement(props.problem.id, statements[index].id)
      .then(res => {
        fetchData();
        Message.success('已删除');
      })
  }
  useEffect(() => {
    fetchData();
  }, []);
  const mdKaTeX = `This is to display the 
  \`$c = \\pm\\sqrt{a^2 + b^2}$\`
   in one line
  
  \`\`\`KaTeX
  c = \\pm\\sqrt{a^2 + b^2}
  \`\`\`
  
  \`\`\`KaTeX
  \\f\\relax{x} = \\int_{-\\infty}^\\infty
      \\f\\hat\\xi\\,e^{2 \\pi i \\xi x}
      \\,d\\xi
  \`\`\`
  `;
  return (
    <Card>
      <Row gutter={64}>
        <Col xs={24} sm={8} md={6} lg={4}>
          <List
            className={styles['list-actions']}
            bordered
            footer={<CreateStatementModal problem={props.problem} callback={fetchData} />}
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
        <Col xs={24} sm={16} md={18} lg={20}>
          { statements.length > 0 &&
          <Form form={form} layout='vertical' style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
            <FormItem field='name' label={t['name']}>
              <Input />
            </FormItem>
            <FormItem field='legend' label={t['legend']}>
              <Editor />
            </FormItem>
            <FormItem field='input' label={t['input']}>
              <Editor />
            </FormItem>
            <FormItem field='output' label={t['output']}>
              <Editor />
            </FormItem>
            <FormItem field='note' label={t['notes']}>
              <Editor />
            </FormItem>
            <FormItem>
              <Button type='primary' htmlType='submit'>{t['save']}</Button>
            </FormItem>
          </Form>}
        </Col>
      </Row>
    </Card>
  )
}
