import React, { useEffect, useRef, useState } from 'react';
import { Comment, Avatar, Message, Form, Button, Input, Divider, Drawer, Link, Typography, PaginationProps, Pagination } from '@arco-design/web-react';
import Editor from '@/components/MarkdownEditor';
import { createPost, getPost, listPosts, uploadPostImage } from '@/api/post';
import { useRouter } from 'next/router';
import { getProblemsetProblem } from '@/api/problemset';
import { IconUser } from '@arco-design/web-react/icon';
import MarkdownView from '@/components/MarkdownView';
import { FormatTime } from '@/utils/format';

export const SolutionList = ({problemset, problem}:{problemset:any, problem:any}) => {
  const [solutionList, setSolutionList] = useState([]);
  const [solution, setSolution] = useState({
    title: '',
    content: '',
    createdAt: '',
    user: {
      id: 0,
      nickname: '',
      username: '',
      avatar: ''
    }
  });
  const [visible, setVisible] = useState(false);
  const [pagination, setPagination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 25,
    current: 1,
    pageSizeChangeResetCurrent: true,
    sizeOptions: [25, 50, 100],
    hideOnSinglePage: true,
    onChange: (current, pageSize) => {
      setPagination({
        ...pagination,
        current,
        pageSize,
      });
    }
  });
  const refWrapper = useRef(null);
  const router = useRouter();
  const {id, pid} = router.query;
  useEffect(() => {
    fetchData();
  }, []);
  function onClickSolution(id) {
    getPost(id).then(res => {
      setSolution(res.data);
      setVisible(true);
    });
  }
  function fetchData() {
    const { current, pageSize } = pagination;
    listPosts({entityType: 'PROBLEM_SOLUTION', entityId: problem.id, page: current, perPage: pageSize})
      .then(res => {
        setPagination({
          ...pagination,
          current,
          pageSize,
          total: res.data.total,
        });
        setSolutionList(res.data.data);
      });
  }
  return (
    <div className='bg-white h-full p-5' ref={refWrapper}>
      <Link href={`/problemsets/${id}/problems/${pid}/solution`} target='_blank'>发表题解</Link>
      <Divider />
      {solutionList.map((item, index) =>
        <div key={index}>
          {index !== 0 && <Divider />}
          <div className='cursor-pointer' onClick={() => onClickSolution(item.id)}>
            <Comment
              align='right'
              author={<Link href={`/u/${item.user.id}`} target='_blank'>{item.user.nickname}</Link>}
              avatar={
                <Avatar>
                  {item.user.avatar ? (
                    <img
                      alt='avatar'
                      src={item.user.avatar}
                    />
                  ) : (
                    <IconUser />
                  )}
                </Avatar>
              }
              content={
                <Typography.Paragraph ellipsis={{rows: 2}}>
                  {item.content}
                </Typography.Paragraph>
              }
              datetime={FormatTime(item.createdAt, 'YYYY-MM-DD')}
            />
          </div>
        </div>
      )}
      <Pagination {...pagination} />
      <Drawer
        title={solution.title}
        visible={visible}
        width='100%'
        getPopupContainer={() => refWrapper && refWrapper.current}
        footer={null}
        onOk={() => {
          setVisible(false);
        }}
        onCancel={() => {
          setVisible(false);
        }}
      >
        <Comment
          author={<Link href={`/u/${solution.user.id}`}>{solution.user.nickname}</Link>}
          avatar={
            <Avatar>
              {solution.user.avatar ? (
                <img
                  alt='avatar'
                  src={solution.user.avatar}
                />
              ) : (
                <IconUser />
              )}
            </Avatar>
          }
          datetime={FormatTime(solution.createdAt, 'YYYY-MM-DD')}
        />
        <Typography.Paragraph>
          <MarkdownView content={solution.content} />
        </Typography.Paragraph>
      </Drawer>
    </div>
  );
};

const Page = () => {
  const [uploadFiles, setUploadFiles] = useState([]);
  const [form] = Form.useForm();
  const [problem, setProblem] = useState({
    id: 0,
    name: '',
  });
  const router = useRouter();
  const { id, pid } = router.query;
  useEffect(() => {
    getProblemsetProblem(id, pid).then(res => {
      setProblem(res.data);
    });
  }, []);
  function uploadFile(option) {
    const { onError, onSuccess, file } = option;
    const formData = new FormData();
    formData.append('file', file);
    uploadPostImage(formData)
      .then(res => {
        setUploadFiles(previous => [...previous, {
          uid: res.data,
          url: res.data,
          name: file.name,
        }]);
        onSuccess(res.data);
        Message.success('上传成功');
      })
      .catch(err => {
        onError();
      });
  }
  function onSubmit() {
    form.validate().then((values) => {
      const data = {
        entity_id: problem.id,
        entity_type: 'PROBLEM_SOLUTION',
        title: values.title,
        content: values.content,
      };
      createPost(data)
        .then(res => {
          Message.success('已发表');
          router.push(`/problemsets/${id}/problems/${pid}`);
        })
        .finally(() => {
        });
    });
  }
  return (
    <div className='h-[calc(100vh-122px)] flex justify-center overflow-hidden'>
      <div className='bg-white shadow-md flex flex-col max-w-[888px] w-full'>
        <Form form={form} className='h-full' layout='vertical' autoComplete='off' onSubmit={onSubmit}>
          <div className='p-4'>
            <div className='flex gap-2'>
              <div className='grow'>
                <Form.Item noStyle field='title' rules={[{ required: true }]}>
                  <Input size='large' placeholder='请输入标题' />
                </Form.Item>
              </div>
              <div className='flex'>
                <Button type='primary' htmlType='submit'>发布题解</Button>
              </div>
            </div>
          </div>
          <Form.Item noStyle required field='content' rules={[{ required: true }]}>
            <Editor height='100%'
              imageRequest={{
                onUpload: uploadFile,
              }}
              imageUploadedFile={uploadFiles}
            />
          </Form.Item>
        </Form>
      </div>      
    </div>
  );
};

export default Page;
