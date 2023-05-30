import { updateProblemChecker } from '@/api/problem';
import { createProblemFile, getProblemFile, listProblemFiles, listProblemStdCheckers } from '@/api/problem-file';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { Form, Input, Button, Select, Card, Divider, Modal, Message, Alert } from '@arco-design/web-react';
import { IconPlus } from '@arco-design/web-react/icon';
import React, { useEffect, useState } from 'react';
import Highlight from '@/components/Highlight';
import styles from './style/checker.module.less';
const FormItem = Form.Item;
const Option = Select.Option;
const App = (props: any) => {
  const t = useLocale(locale);
  const [userCheckers, setUserCheckers] = useState([]);
  const [stdCheckers, setStdCheckers] = useState([]);
  const [checker, setChecker] = useState({name: '', content: ''});
  const [visible, setVisible] = useState(false);
  const [form] = Form.useForm();
  const [checkerId, setCheckerId] = useState(0);
  const [note, setNote] = useState(''); 
  function fetchData() {
    listProblemFiles(props.problem.id, {fileType: 'checker'})
      .then((res) => {
        setUserCheckers(res.data.data);
      });
    listProblemStdCheckers(props.problem.id)
      .then((res) => {
        setStdCheckers(res.data.data);
        setNotes(res.data.data, props.problem.checkerId);
      });
    if (props.problem.checkerId !== 0) {
      getProblemFile(props.problem.id, props.problem.checkerId)
        .then(res => {
          setChecker(res.data);
        });
    }
  }
  function setNotes(stdCheckers, checkerId) {
    const std = stdCheckers.find(value => {
      return value.id === checkerId;
    });
    if (std) {
      setNote(t[`checker.std.${std.name}.intro`]);
    } else {
      setNote('');
    }
  }
  function onOk() {
    form.validate().then((res) => {
      const values = {
        name: res.name,
        content: res.content,
        type: res.type,
        fileType: 'checker',
        language: 1,
      };
      createProblemFile(props.problem.id, values)
        .then(res => {
          Message.success('已保存');
          setVisible(false);
          fetchData();
        });
    });
  }
  function onSave() {
    updateProblemChecker(props.problem.id, { checkerId })
      .then(res => {
        Message.info('已保存');
      });
  }
  function onSelectedChange(e) {
    setNotes(stdCheckers, e);
    setCheckerId(e);
    getProblemFile(props.problem.id, e)
      .then(res => {
        setChecker(res.data);
      });
  }
  useEffect(() => {
    fetchData();
  }, []);

  return (
    <Card>
      <Alert
        type='info'
        content={
          <div>
            裁判程序是用来比较用户的答案和标程的答案，也即俗称的SPJ（Special Judge）。当一道题有多组解时，用来判断答案合法性的程序。
            每道题均需要有裁判程序，即时您的题目没有多组解，也应该选择一个。如果您不确定选什么，请选择 lcmp.cpp 或者 fcmp.cpp 均可。
          </div>
        }
      />
      <div style={{marginTop: '10px'}}>
        <Form style={{ width: 600 }} autoComplete='off'>
          <FormItem label='选择' help={note}>
            <Select
              style={{ width: 500 }}
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
              onChange={onSelectedChange}
            >
              <Select.OptGroup label='std'>
                {stdCheckers.map((option, index) => (
                  <Option key={option.id} value={option.id}>
                    {option.name} - {t[`checker.std.${option.name}.title`]}
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
            <Button type='primary' onClick={onSave}>{t['save']}</Button>
          </FormItem>
        </Form>
        <Divider />
        <Form style={{ width: 600 }} disabled>
          <FormItem label='名称'>
            <Input value={checker.name} />
          </FormItem>
          <FormItem label='源码'>
            <Alert
              showIcon={false}
              className={styles['checker-content']}
              content={<Highlight content={checker.content} />}
            />
          </FormItem>
        </Form>
      </div>
    </Card>
  );
};

export default App;
