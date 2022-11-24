import { useAppSelector } from '@/hooks'
import { setting, SettingState } from '@/store/reducers/setting'
import dynamic from 'next/dynamic'
import Head from 'next/head'
const HomeContent = dynamic(() => import('./home/index'), {
  ssr: false,
})
export default function() {
  const settings = useAppSelector<SettingState>(setting);
  return <>
    <Head>
      <title>{settings.name + ' - ' + settings.briefDescription}</title>
    </Head>
    <HomeContent />
  </>
}
