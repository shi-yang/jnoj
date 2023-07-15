import Editor, { EditorProps, loader } from '@monaco-editor/react';
import React from 'react';

loader.config({
  paths: {
    vs: 'https://cdn.staticfile.org/monaco-editor/0.40.0/min/vs'
  }
});

function E(props: EditorProps) {
  return <Editor {...props} />;
}

export default E;
