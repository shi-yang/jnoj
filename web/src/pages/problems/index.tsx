import React from 'react';
import { Space } from '@arco-design/web-react';
import styles from './style/index.module.less';
import List from './list';
import Shortcuts from './shortcuts';

function Problem() {
  return (
    <div className='container'>
      <div className={styles.wrapper}>
        <Space size={16} direction="vertical" className={styles.left}>
          <List />
        </Space>
        <Space className={styles.right} size={16} direction="vertical">
          <Shortcuts />
        </Space>
      </div>
    </div>
  );
}

export default Problem;
