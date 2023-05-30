import hljs, { HighlightOptions, HighlightResult } from 'highlight.js';
import 'highlight.js/styles/atom-one-dark.css';
import React, { useEffect, useState } from 'react';

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
      <code
        className={`hljs language-${v.language}`}
        dangerouslySetInnerHTML={{ __html: v.value }}
      />
    </pre>
  );
}
