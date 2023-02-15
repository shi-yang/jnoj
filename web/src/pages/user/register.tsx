import React, { useEffect } from 'react';
import RegisterForm from './register-form';
import styles from './style/login.module.less';

function Register() {
  useEffect(() => {
    document.body.setAttribute('arco-theme', 'light');
  }, []);

  return (
    <div className={styles.container}>
      <div className={styles.content}>
        <div className={styles['content-inner']}>
          <RegisterForm />
        </div>
      </div>
    </div>
  );
}
Register.displayName = 'RegisterPage';

export default Register;
