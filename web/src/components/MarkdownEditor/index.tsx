import React, { useEffect } from 'react';
import dynamic from 'next/dynamic';
import '@uiw/react-md-editor/markdown-editor.css';
import '@uiw/react-markdown-preview/markdown.css';
import { Card, Upload } from '@arco-design/web-react';
import * as commands from '@uiw/react-md-editor/lib/commands';
import { IconClose, IconDelete, IconFaceFrownFill, IconFileAudio, IconImage, IconUpload } from '@arco-design/web-react/icon';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';
import rehypeHighlight from 'rehype-highlight';
import 'katex/dist/katex.min.css';
import { UploadItem, UploadRequestReturn } from '@arco-design/web-react/es/Upload';
import { RequestOptions } from '@arco-design/web-react/es/Upload/interface';
const MarkdownEditor = dynamic(
  () => import('@uiw/react-md-editor').then((mod) => mod.default),
  { ssr: false }
);
const image = function(props:UploadProps) {
  return commands.group([], {
    name: 'insert image',
    groupName: 'insert image',
    buttonProps: {
      'aria-label': '插入图片',
      title: '插入图片'
    },
    icon: (
      <IconImage style={{verticalAlign: 0}} />
    ),
    children: (handle) => {
      return (
        <div style={{ padding: 10 }}>
          <Upload
            listType='picture-list'
            showUploadList={{
              reuploadIcon: <IconUpload />,
              cancelIcon: <IconClose />,
              fileIcon: <IconFileAudio />,
              removeIcon: <IconClose />,
              previewIcon: null,
              errorIcon: <IconFaceFrownFill />,
              fileName: (file) => {
                return (
                  <a
                    onClick={() => {
                    console.log(file);
                      handle.textApi.replaceSelection(`![${file.name}](${file.response})\n`);
                    }}
                  >
                    {file.name}
                  </a>
                );
              },
            }}
            progressProps={{
              formatText: (percent) => `${percent}%`,
            }}
            onRemove={props.imageRequest.onRemove}
            customRequest={props.imageRequest.onUpload}
            defaultFileList={props.imageUploadedFile}
          />
        </div>
      );
    },
  });
};
type UploadProps = {
  imageRequest?:{
    /**
     * @zh 通过覆盖默认的上传行为，可以自定义自己的上传实现
     * @en Provide an override for the default xhr behavior for additional customization
     */
    onUpload?: (options: RequestOptions) => UploadRequestReturn | void;
    /**
     * @zh 点击删除文件时的回调。返回 `false` 或者 `Promise.reject` 的时候不会执行删除。
     * @en Callback when the remove icon is clicked.Remove actions will be aborted when the return value is false or a Promise which resolve(false) or reject.
     */
    onRemove?: (file: UploadItem, fileList: UploadItem[]) => void;
  },
  imageUploadedFile?:UploadItem[],
}
const Editor = ({imageRequest, imageUploadedFile, ...props}: UploadProps & { height?: string } & JSX.IntrinsicAttributes) => {
  return (
    <div style={{height: props.height}}>
      <MarkdownEditor
        {...props}
        height={'100%'}
        commands={[
          commands.group([commands.title1, commands.title2, commands.title3, commands.title4, commands.title5, commands.title6], {
            name: 'title',
            groupName: 'title',
            buttonProps: { 'aria-label': 'Insert title'}
          }),
          commands.link,
          commands.bold,
          commands.codeBlock,
          imageRequest && image({imageRequest, imageUploadedFile}),
        ]}
        previewOptions={{
          remarkPlugins:[[remarkMath]],
          rehypePlugins:[[rehypeKatex, rehypeHighlight]]
        }}
      />
    </div>
  );
};
export default Editor;
