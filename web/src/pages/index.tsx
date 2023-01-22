import { useAppSelector } from '@/hooks'
import { setting, SettingState } from '@/store/reducers/setting'
import Head from 'next/head'
import HomeContent from '@/pages/home/index';
export default function() {
  const settings = useAppSelector<SettingState>(setting);
  return <>
    <Head>
      <title>{settings.name + ' - ' + settings.briefDescription}</title>
    </Head>
    <HomeContent />
  </>
}
