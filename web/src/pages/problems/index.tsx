import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import Head from 'next/head';
import Create from './create';
export default function() {
  const settings = useAppSelector<SettingState>(setting);
  return (
    <>
      <Head>
        <title>{`题库列表 - ${settings.name}`}</title>
      </Head>
      <Create />
    </>
  )
}
