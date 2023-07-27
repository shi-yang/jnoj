import React from 'react';
import {
  Card,
  Typography,
  Divider,
  PageHeader,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import MainLayout from '@/components/Layouts/MainLayout';

export default function Index(page) {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);

  return (
    <MainLayout>
      <Head>
        <title>{`${t['page.title']} - ${settings.name}`}</title>
      </Head>
      <div style={{padding: '20px'}}>
        <Card className='container'>
          <PageHeader
            title={t['page.title']}
            subTitle={t['page.desc']}
          />
          <Typography.Text type='secondary'>{t['page.desc2']}</Typography.Text>
          <Divider />
          {page}
        </Card>
      </div>
    </MainLayout>
  );
}
