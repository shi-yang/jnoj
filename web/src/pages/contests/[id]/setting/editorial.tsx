import { createContestProblem, deleteContestProblem, listContestProblems, updateContest } from '@/api/contest';
import Editor from '@/components/MarkdownEditor';
import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Input,  Modal, Message } from '@arco-design/web-react';
import { IconDelete, IconPlus } from '@arco-design/web-react/icon';
import React, { useContext, useEffect, useState } from 'react';
import ContestContext from '../context';
import locale from '../locale';
import { createPost, listPosts, updatePost } from '@/api/post';

const CreatePost = ({contestId, callback}: {contestId: number, callback: () => void}) => {
  const t = useLocale(locale);
  const [visible, setVisible] = useState(false);
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [form] = Form.useForm();
  function onOk() {
    form.validate().then((values) => {
      const data = {
        problemId: values.problemId,
      };
      setConfirmLoading(true);
      createContestProblem(contestId, data)
        .then(res => {
          setVisible(false);
          callback();
        })
        .catch(res => {
          Message.error(res.response.data.message || '出错了');
        })
        .finally(() => {
          setConfirmLoading(false);
        });
    });
  }
  return (
    <div>
      <Button type='primary' icon={<IconPlus />} onClick={() => setVisible(true)}>
        {t['setting.info.addProblem']}
      </Button>
      <Modal
        title={t['setting.info.addProblem']}
        visible={visible}
        onOk={onOk}
        confirmLoading={confirmLoading}
        onCancel={() => setVisible(false)}
      >
        <Form form={form}>
          <Form.Item label='题目ID' required field='problemId' rules={[{ required: true }]}>
            <Input placeholder='' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
};

const SettingInfo = () => {
  const contest = useContext(ContestContext);
  const t = useLocale(locale);
  const [form] = Form.useForm();
  const [confirmLoading, setConfirmLoading] = useState(false);
  const [post, setPost] = useState({id: 0, title: '', content: ''});
  const [isNewRecord, setIsNewRecord] = useState(true);
  function onSubmit() {
    form.validate().then((values) => {
      const data = {
        entity_id: contest.id,
        entity_type: 'CONTEST_EDITORIAL',
        title: values.title,
        content: values.content,
      };
      setConfirmLoading(true);
      if (isNewRecord) {
        createPost(data)
          .then(res => {
            Message.success('已保存');
            setIsNewRecord(false);
          })
          .finally(() => {
            setConfirmLoading(false);
          });
      } else {
        updatePost(post.id, data)
          .then(res => {
            Message.success('已保存');
          })
          .finally(() => {
            setConfirmLoading(false);
          });
      }
    });
  }
  useEffect(() => {
    listPosts({entityType: 'CONTEST_EDITORIAL', entityId: contest.id}).then(res => {
      if (res.data.data.length === 1) {
        setIsNewRecord(false);
        const data = res.data.data[0];
        setPost(data);
        form.setFieldsValue({
          title: data.title,
          content: data.content,
        });
      }
    });
  }, []);
  return (
    <div>
      <Card title='您可在此编写比赛题解，比赛结束后用户方可查看题解'>
        <Form form={form} autoComplete='off' onSubmit={onSubmit}>
          <Form.Item label={t['setting.editorial.title']} required field='title' rules={[{ required: true }]}>
            <Input placeholder='' />
          </Form.Item>
          <Form.Item label={t['setting.editorial.content']} required field='content' rules={[{ required: true }]}>
            <Editor />
          </Form.Item>
          <Form.Item>
            <Button type='primary' htmlType='submit'>{t['save']}</Button>
          </Form.Item>
        </Form>
      </Card>
    </div>
  );
};
export default SettingInfo;
