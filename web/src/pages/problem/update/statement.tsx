import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input, Message, Select, Tabs } from '@arco-design/web-react';
import MarkdownEditor from '@uiw/react-markdown-editor';
import { useEffect, useState } from 'react';
import locale from './locale';
import CreateStatementModal from './create-statement';
import { createProblemStatement, listProblemStatements } from '@/api/problem-statement';
const FormItem = Form.Item;
const TabPane = Tabs.TabPane;
export default (props) => {
  const t = useLocale(locale);
  const [statements, setStatements] = useState([]);
  function fetchData() {
    listProblemStatements(props.problem.id).then(res => {
      setStatements(res.data.data)
    })
  }
  function onSubmit(values) {
    console.log(values)
    createProblemStatement(props.problem.id, values).then(res => {
      Message.info('已保存')
    })
  }
  useEffect(() => {
    fetchData();
  }, []);
  return (
    <Card>
      <CreateStatementModal />
        <Tabs type='text' defaultActiveTab='0'>
          { statements.length > 0 && statements.map((item, index) => {
            return (
              <TabPane key={index} title={item.name}>
                <Form layout='vertical' style={{ width: 600 }} autoComplete='off' onSubmit={onSubmit}>
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
              </TabPane>
            )
          }) }
        </Tabs>
    </Card>
  )
}
