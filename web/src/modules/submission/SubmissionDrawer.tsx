import useLocale from '@/utils/useLocale';
import { Drawer, Link } from '@arco-design/web-react';
import React from 'react';
import locale from './locale';
import Submission from './Submission';

export default function SubmissionDrawer ({id, visible, onCancel}: {id: number, visible: boolean, onCancel?: (e: MouseEvent | Event) => void}) {
  const t = useLocale(locale);
  return (
    <Drawer
      width={900}
      title={<Link href={`/submissions/${id}`} target='_blank'>{t['submission']}: {id}</Link>}
      visible={visible}
      onCancel={onCancel}
      footer={null}
    >
      <div style={{padding: 20}}>
        <Submission id={id} />
      </div>
    </Drawer>
  );
}
