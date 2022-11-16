import {
  Form,
  Input,
  Checkbox,
  Button,
  Space,
  Message,
} from '@arco-design/web-react';
import { FormInstance } from '@arco-design/web-react/es/Form';
import { IconLock, IconUser } from '@arco-design/web-react/icon';
import { useNavigate } from 'react-router-dom';
import React, { useEffect, useRef, useState } from 'react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/login.module.less';
import { Register } from '@/api/user';

export default function RegisterForm() {
  const formRef = useRef<FormInstance>();
  const [errorMessage, setErrorMessage] = useState('');
  const [loading, setLoading] = useState(false);

  const t = useLocale(locale);
  const navigate = useNavigate();

  function afterRegisterSuccess(params) {
    // 跳转首页
    navigate('/')
  }

  function register(params) {
    setErrorMessage('');
    setLoading(true);
    Register(params)
      .then((res) => {
        const { status, msg } = res.data;
        if (status === 'ok') {
          afterRegisterSuccess(params);
        } else {
          setErrorMessage(msg || t['login.form.login.errMsg']);
        }
      })
      .catch(err => {
        Message.info(err.response.data.message)
      })
      .finally(() => {
        setLoading(false);
      });
  }

  function onSubmitClick() {
    formRef.current.validate().then((values) => {
      register(values);
    });
  }

  return (
    <div className={styles['login-form-wrapper']}>
      <div className={styles['login-form-title']}>{t['register.form.title']}</div>
      <div className={styles['login-form-error-msg']}>{errorMessage}</div>
      <Form
        className={styles['register-form']}
        layout="vertical"
        ref={formRef}
      >
        <Form.Item
          field="username"
          rules={[{ required: true, message: t['login.form.userName.errMsg'] }]}
        >
          <Input
            prefix={<IconUser />}
            placeholder={t['login.form.userName.placeholder']}
            onPressEnter={onSubmitClick}
          />
        </Form.Item>
        <Form.Item
          field="password"
          rules={[{ required: true, message: t['login.form.password.errMsg'] }]}
        >
          <Input.Password
            prefix={<IconLock />}
            placeholder={t['login.form.password.placeholder']}
            onPressEnter={onSubmitClick}
          />
        </Form.Item>
        <Space size={16} direction="vertical">
          <Button type="primary" long onClick={onSubmitClick} loading={loading}>
            {t['login.form.register']}
          </Button>
          <Button
            type="text"
            long
            className={styles['login-form-register-btn']}
            onClick={() => navigate('/login') }
          >
            {t['login.form.login']}
          </Button>
        </Space>
      </Form>
    </div>
  );
}
