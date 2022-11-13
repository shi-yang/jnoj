import React, { useEffect } from 'react';
import Logo from '@/assets/logo.svg';
import LoginForm from './login-form';
import LoginBanner from './login-banner';
import styles from './style/login.module.less';

function Login() {
  useEffect(() => {
    document.body.setAttribute('arco-theme', 'light');
  }, []);

  return (
    <div className={styles.container}>
      <div className={styles.banner}>
        <div className={styles['banner-inner']}>
          <LoginBanner />
        </div>
      </div>
      <div className={styles.content}>
        <div className={styles['content-inner']}>
          <LoginForm />
        </div>
      </div>
    </div>
  );
}
Login.displayName = 'LoginPage';

export default Login;
