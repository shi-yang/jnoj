import { Result } from '@arco-design/web-react';

const App = () => {
  return (
    <div>
      <Result
        status='404'
        subTitle='Whoops, that page is gone. '
      ></Result>
    </div>
  );
};

export default App;
