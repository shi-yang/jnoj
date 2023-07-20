import hljs, { HighlightOptions, HighlightResult } from 'highlight.js';
import React, { useEffect, useState } from 'react';
import 'highlight.js/styles/vs.css';

const LanguageMap = {
  0: 'c',
  1: 'cpp',
  2: 'java',
  3: 'py'
};

export default function Highlight({content, language}: {content: string, language?: number}) {
  const [v, setV] = useState<HighlightResult>({} as HighlightResult);
  useEffect(() => {
    if (language === undefined) {
      setV(hljs.highlightAuto(content));
    } else {
      setV(hljs.highlight(content, {
        language: LanguageMap[language],
      } as HighlightOptions));
    }
  }, [content]);
  return (
    <pre>
      {v.value === '' ? (
        <code>{v.code}</code>
      ) : (
        <code
          className={`hljs language-${v.language}`}
          dangerouslySetInnerHTML={{ __html: v.value }}
        />
      )}
    </pre>
  );
}
