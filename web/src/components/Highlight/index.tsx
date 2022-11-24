import hljs from 'highlight.js';
import 'highlight.js/styles/github.css';

export default function({content}) {
  return (
    <pre
      dangerouslySetInnerHTML={{ __html: hljs.highlightAuto(content).value }}
    />
  );
};
