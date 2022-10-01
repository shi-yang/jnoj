import React from 'react';
import { Card, Message } from '@arco-design/web-react';
import {
  IconFile,
} from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/shortcuts.module.less';

function Shortcuts() {
  const t = useLocale(locale);

  const shortcuts = [
    {
      title: t['problem.createProblem'],
      key: '/problem/create',
      icon: <IconFile />,
    },
  ];

  function onClickShortcut(key) {
    window.location.href = key;
  }

  return (
    <Card>
      <div className={styles.shortcuts}>
        {shortcuts.map((shortcut) => (
          <div
            className={styles.item}
            key={shortcut.key}
            onClick={() => onClickShortcut(shortcut.key)}
          >
            <div className={styles.icon}>{shortcut.icon}</div>
            <div className={styles.title}>{shortcut.title}</div>
          </div>
        ))}
      </div>
    </Card>
  );
}

export default Shortcuts;
