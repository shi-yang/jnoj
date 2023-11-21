import { useAppSelector } from '@/hooks';
import { SettingState, setting } from '@/store/reducers/setting';
import { Divider, Grid } from '@arco-design/web-react';
import React, { useEffect, useState } from 'react';
import styles from './style/index.module.less';
import codeonline from '@/assets/home/codeonline.png';
import { IconGithub, IconShareAlt, IconThumbUp } from '@arco-design/web-react/icon';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import Image from 'next/image';
import { isLogged } from '@/utils/auth';
import LoginIndex from './login-index';
export default function Index() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting);
  const [isMounted, setIsMounted] = useState(false);
  useEffect(() => {
    setIsMounted(true);
  }, []);
  return (
    <div className='flex flex-col'>
      <header className={styles['hero']}>
        <div className='container'>
          <h1 className={styles['title']}>
            {settings.name}
          </h1>
          <p className={styles['subtitle']}>{settings.description}</p>
        </div>
      </header>
      {isMounted && isLogged() ? (
        <LoginIndex />
      ) : (
        <div className='w-full'>
          <section className={styles['features']}>
            <div className='container'>
              <Grid.Row gutter={32}>
                <Grid.Col span={8}>
                  <div>
                    <IconThumbUp fontSize={80} />
                  </div>
                  <div>
                    <h3>{t['easyToUse']}</h3>
                    <p>{t['features1.description']}</p>
                  </div>
                </Grid.Col>
                <Grid.Col span={8}>
                  <div>
                    <IconGithub fontSize={80} />
                  </div>
                  <div>
                    <h3>{t['openSource']}</h3>
                    <p>{t['features2.description']}</p>
                  </div>
                </Grid.Col>
                <Grid.Col span={8}>
                  <div>
                    <IconShareAlt fontSize={80} />
                  </div>
                  <div>
                    <h3>{t['manyProblems']}</h3>
                    <p>{t['features3.description']}</p>
                  </div>
                </Grid.Col>
              </Grid.Row>
            </div>
          </section>
          <Divider />
          <section>
            <div className='container'>
              <h2 className={styles['section-title']}>{t['runOnline']}</h2>
              <Image width={1320} height={770} src={codeonline.src} alt='Online Programming' />
            </div>
          </section>
        </div>
      )}
    </div>
  );
}
