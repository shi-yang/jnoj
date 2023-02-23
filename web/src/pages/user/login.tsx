import React, { useEffect } from 'react';
import LoginForm from './login-form';
import styles from './style/login.module.less';
import Head from 'next/head';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import locale from './locale';
import useLocale from '@/utils/useLocale';

function Login() {
  const settings = useAppSelector<SettingState>(setting);
  const t = useLocale(locale);
  useEffect(() => {
    document.body.setAttribute('arco-theme', 'light');
  }, []);

  return (
    <>
      <Head>
          <title>{`${t['login']} - ${settings.name}`}</title>
      </Head>
      <div className={styles.container}>
        <div className={styles.content}>
          <div className={styles['content-inner']}>
            <LoginForm />
          </div>
        </div>
      </div>
    </>
  );
}
Login.displayName = 'LoginPage';

export default Login;
