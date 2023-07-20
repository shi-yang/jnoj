import Editor from '@/components/MarkdownEditor';
import useLocale from '@/utils/useLocale';
import { Button, Card, Form, Message } from '@arco-design/web-react';
import React, { useContext, useEffect, useState } from 'react';
import ContestContext from '../context';
import locale from '../locale';
import { createPost, listPosts, updatePost } from '@/api/post';

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
