import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
import useLocale from '@/utils/useLocale';
import Head from 'next/head';
import React, { useEffect } from 'react';
import RegisterForm from './register-form';
import styles from './style/login.module.less';

function Register() {
  const t = useLocale();
  const settings = useAppSelector<SettingState>(setting);
  useEffect(() => {
    document.body.setAttribute('arco-theme', 'light');
  }, []);

  return (
    <>
      <Head>
          <title>{`${t['register']} - ${settings.name}`}</title>
      </Head>
      <div className={styles.container}>
        <div className={styles.content}>
          <div className={styles['content-inner']}>
            <RegisterForm />
          </div>
        </div>
      </div>
    </>
  );
}
Register.displayName = 'RegisterPage';

export default Register;
