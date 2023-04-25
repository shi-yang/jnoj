import useLocale from '@/utils/useLocale';
import { Drawer } from '@arco-design/web-react';
import React from 'react';
import locale from './locale';
import Submission from './Submission';

export default function SubmissionDrawer ({id, visible, onCancel}: {id: number, visible: boolean, onCancel?: (e: MouseEvent | Event) => void}) {
  const t = useLocale(locale);
  return (
    <Drawer
      width={900}
      title={<span>{t['submission']}: {id}</span>}
      visible={visible}
      onCancel={onCancel}
      footer={null}
    >
      <Submission id={id} />
    </Drawer>
  );
}
