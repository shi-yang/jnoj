import { updateProblemChecker } from '@/api/problem';
import { createProblemFile, ListProblemFiles, listProblemStdCheckers } from '@/api/problem-file';
import { Form, Input, Button, Select, Card, Divider, Modal, Message } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import { useEffect, useState } from 'react';
const FormItem = Form.Item;
const Option = Select.Option;
const App = (props) => {
  const [userCheckers, setUserCheckers] = useState([]);
  const [stdCheckers, setStdCheckers] = useState([]);
  const [visible, setVisible] = useState(false);
  const [form] = Form.useForm();
  const [checkerId, setCheckerId] = useState(0);
  function fetchData() {
    ListProblemFiles(props.problem.id, {fileType: 'checker'})
      .then((res) => {
        setUserCheckers(res.data.data);
      });
    listProblemStdCheckers(props.problem.id)
      .then((res) => {
        setStdCheckers(res.data.data);
      });
  }

  function onOk() {
    form.validate().then((res) => {
      const values = {
        name: res.name,
        content: res.content,
        type: res.type,
        fileType: 'checker'
      };
      createProblemFile(props.problem.id, values)
        .then(res => {
          Message.success('已保存')
          setVisible(false)
          fetchData()
        });
    });
  }
  function onSave() {
    updateProblemChecker(props.problem.id, { checkerId })
      .then(res => {
        Message.info('已保存')
      })
  }
  useEffect(() => {
    fetchData();
  }, []);

  return (
    <Card>
      <Form style={{ width: 600 }} autoComplete='off'>
        <FormItem label='选择'>
          <Select
            style={{ width: 240 }}
            placeholder='Select checker'
            defaultValue={props.problem.checkerId}
            dropdownRender={(menu) => (
              <div>
                {menu}
                <Divider style={{ margin: 0 }} />
                <div
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    padding: '10px 12px',
                  }}
                >
                  <Button
                    style={{ fontSize: 14, padding: '0 6px' }}
                    type='text'
                    size='mini'
                    onClick={() => setVisible(true)}
                  >
                    <IconPlus />
                    Add item
                  </Button>
                  <Modal
                    title='添加'
                    visible={visible}
                    onOk={onOk}
                    onCancel={() => setVisible(false)}
                    autoFocus={false}
                    focusLock={true}
                  >
                    <Form
                      form={form}
                    >
                      <FormItem field='name' label='名称' required>
                        <Input />
                      </FormItem>
                      <FormItem field='content' label='源码' required>
                        <Input.TextArea rows={10} />
                      </FormItem>
                    </Form>
                  </Modal>
                </div>
              </div>
            )}
            dropdownMenuStyle={{ maxHeight: 200 }}
            onChange={(e) => setCheckerId(e)}
          >
            <Select.OptGroup label='std'>
              {stdCheckers.map((option, index) => (
                <Option key={option.id} value={option.id}>
                  {option.name}
                </Option>
              ))}
            </Select.OptGroup>
            <Select.OptGroup label='user'>
              {userCheckers.map((option, index) => (
                <Option key={option.id} value={option.id}>
                  {option.name}
                </Option>
              ))}
            </Select.OptGroup>
          </Select>
        </FormItem>
        <FormItem wrapperCol={{ offset: 5 }}>
          <Button type='primary' onClick={onSave}>Save</Button>
        </FormItem>
      </Form>
    </Card>
  );
};

export default App;
