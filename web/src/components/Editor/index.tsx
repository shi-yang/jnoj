import Editor, { EditorProps, loader } from '@monaco-editor/react';
import React from 'react';

loader.config({
  paths: {
    vs: 'https://cdn.bootcdn.net/ajax/libs/monaco-editor/0.37.1/min/vs'
  }
});

function E(props: EditorProps) {
  return <Editor {...props} />;
}

export default E;
