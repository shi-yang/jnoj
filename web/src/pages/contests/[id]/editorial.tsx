import { listPosts } from '@/api/post';
import React, { useContext, useEffect, useState } from 'react';
import ContestContext from './context';
import { Empty, Typography } from '@arco-design/web-react';
import MarkdownView from '@/components/MarkdownView';
import ContestLayout from './Layout';

function Editorial() {
  const [post, setPost] = useState({id: 0, title: '', content: ''});
  const [hasEditorial, setHasEditorial] = useState(false);
  const contest = useContext(ContestContext);
  useEffect(() => {
    listPosts({entityType: 'CONTEST_EDITORIAL', entityId: contest.id })
      .then(res => {
        if (res.data.data.length === 1) {
          setPost(res.data.data[0]);
          setHasEditorial(true);
        }
      });
  }, []);
  return (
    <div className='container' style={{overflow: 'auto'}}>
      {
        hasEditorial ?
        <div>
          <Typography>
          <Typography.Title>{post.title}</Typography.Title>
          <Typography.Paragraph>
            <MarkdownView content={post.content} />
          </Typography.Paragraph>
          </Typography>
        </div>
        : <Empty />
      }
    </div>
  );
}

Editorial.getLayout = ContestLayout;
export default Editorial;
