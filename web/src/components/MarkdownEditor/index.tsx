import React from 'react';
import dynamic from 'next/dynamic';
import '@uiw/react-md-editor/markdown-editor.css';
import '@uiw/react-markdown-preview/markdown.css';
import { Upload } from '@arco-design/web-react';
import * as commands from '@uiw/react-md-editor/lib/commands';
import { IconClose, IconFaceFrownFill, IconFileAudio, IconUpload } from '@arco-design/web-react/icon';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';
import rehypeHighlight from 'rehype-highlight';
import 'katex/dist/katex.min.css';
import { UploadItem, UploadRequestReturn } from '@arco-design/web-react/es/Upload';
import { RequestOptions, UploadRequest } from '@arco-design/web-react/es/Upload/interface';
const MarkdownEditor = dynamic(
  () => import('@uiw/react-md-editor').then((mod) => mod.default),
  { ssr: false }
);
const image = function(props:UploadProps) {
  return commands.group([], {
    name: 'update',
    groupName: 'update',
    icon: (
      <svg viewBox='0 0 1024 1024' width='12' height='12'>
        <path fill='currentColor' d='M716.8 921.6a51.2 51.2 0 1 1 0 102.4H307.2a51.2 51.2 0 1 1 0-102.4h409.6zM475.8016 382.1568a51.2 51.2 0 0 1 72.3968 0l144.8448 144.8448a51.2 51.2 0 0 1-72.448 72.3968L563.2 541.952V768a51.2 51.2 0 0 1-45.2096 50.8416L512 819.2a51.2 51.2 0 0 1-51.2-51.2v-226.048l-57.3952 57.4464a51.2 51.2 0 0 1-67.584 4.2496l-4.864-4.2496a51.2 51.2 0 0 1 0-72.3968zM512 0c138.6496 0 253.4912 102.144 277.1456 236.288l10.752 0.3072C924.928 242.688 1024 348.0576 1024 476.5696 1024 608.9728 918.8352 716.8 788.48 716.8a51.2 51.2 0 1 1 0-102.4l8.3968-0.256C866.2016 609.6384 921.6 550.0416 921.6 476.5696c0-76.4416-59.904-137.8816-133.12-137.8816h-97.28v-51.2C691.2 184.9856 610.6624 102.4 512 102.4S332.8 184.9856 332.8 287.488v51.2H235.52c-73.216 0-133.12 61.44-133.12 137.8816C102.4 552.96 162.304 614.4 235.52 614.4l5.9904 0.3584A51.2 51.2 0 0 1 235.52 716.8C105.1648 716.8 0 608.9728 0 476.5696c0-132.1984 104.8064-239.872 234.8544-240.2816C258.5088 102.144 373.3504 0 512 0z' />
      </svg>
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
                      handle.textApi.replaceSelection(`![${file.name}](${file.url})\n`);
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
            fileList={props.imageUploadedFile}
          />
        </div>
      );
    },
    buttonProps: { 'aria-label': 'Insert image'}
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
const Editor = ({imageRequest, imageUploadedFile, ...props}: JSX.IntrinsicAttributes & UploadProps) => {
  return (
    <MarkdownEditor
      {...props}
      style={{height: '100%'}}
      minHeight={300}
      commands={[
        imageRequest && image({imageRequest, imageUploadedFile}),
      ]}
      previewOptions={{
        remarkPlugins:[[remarkMath]],
        rehypePlugins:[[rehypeKatex, rehypeHighlight]]
      }}
    />
  );
};
export default Editor;
