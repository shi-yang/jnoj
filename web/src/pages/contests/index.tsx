import Index from '@/modules/contest/list';
import Head from 'next/head';
import { setting, SettingState } from '@/store/reducers/setting';
import { useAppSelector } from '@/hooks';
import useLocale from '@/utils/useLocale';
import locale from './locale';

export default function() {
  const settings = useAppSelector<SettingState>(setting);
  const t = useLocale(locale)
  return (
    <>
      <div className='container' style={{padding: '20px'}}>
        <Head>
          <title>{`${t['page.title']} - ${settings.name}`}</title>
        </Head>
        <Index />
      </div>
    </>
  )  
};
