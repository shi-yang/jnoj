import hljs, { AutoHighlightResult } from 'highlight.js';
import 'highlight.js/styles/atom-one-dark.css';
import { useEffect, useState } from 'react';

export default function({content}) {
  const [v, setV] = useState<AutoHighlightResult>({} as AutoHighlightResult);
  useEffect(() => {
    setV(hljs.highlightAuto(content))
  }, [content])
  return (
    <pre>
      <code
        className={`hljs language-${v.language}`}
        dangerouslySetInnerHTML={{ __html: v.value }}
      />
    </pre>
  );
};
