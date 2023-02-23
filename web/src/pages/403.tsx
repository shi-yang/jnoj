import { Result } from '@arco-design/web-react';
import React from 'react';

const App = () => {
  return (
    <div>
      <Result
        status='403'
        subTitle='Access to this resource on the server is denied.'
      ></Result>
    </div>
  );
};

export default App;
