import { Button, Card, Form, Input, Tabs, Typography } from "@arco-design/web-react"
import FormItem from "@arco-design/web-react/es/Form/form-item"
import TabPane from "@arco-design/web-react/es/Tabs/tab-pane"

export default () => {
  return (
    <div className="container">
      <Card>
        <Tabs defaultActiveTab='1'>
          <TabPane key='1' title='简介'>
            <Card>
              <Form style={{ width: 600 }} autoComplete='off'>
                <FormItem label='昵称'>
                  <Input placeholder='请输入你的昵称' />
                </FormItem>
                <FormItem wrapperCol={{ offset: 5 }}>
                  <Button type='primary'>保存</Button>
                </FormItem>
              </Form>
            </Card>
          </TabPane>
          <TabPane key='2' title='安全'>
            <Card>
              <Typography.Paragraph>正在开发中</Typography.Paragraph>
            </Card>
          </TabPane>
        </Tabs>
      </Card>
    </div>
  )
}
