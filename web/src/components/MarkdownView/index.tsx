import React from 'react';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';
import rehypeHighlight from 'rehype-highlight';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import 'katex/dist/katex.min.css';
import 'highlight.js/styles/vs.css';
import 'github-markdown-css/github-markdown.css';

export default function Markdown({content}: {content: string}) {
  return (
    <ReactMarkdown
      className='markdown-body'
      remarkPlugins={[remarkGfm, remarkMath]}
      rehypePlugins={[rehypeHighlight, [rehypeKatex, {strict : false}]]}
    >
      {content}
    </ReactMarkdown>
  );
};
