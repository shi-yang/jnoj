import {
  Form,
  Input,
  Checkbox,
  Button,
  Link,
  Space,
  Message,
} from '@arco-design/web-react';
import { FormInstance } from '@arco-design/web-react/es/Form';
import { IconLock, IconUser } from '@arco-design/web-react/icon';
import React, { useEffect, useRef, useState } from 'react';
import useStorage from '@/utils/useStorage';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/login.module.less';
import { Login, getCaptcha, verifyCaptcha } from '@/api/user';
import { setAccessToken } from '@/utils/auth';
import { useRouter } from 'next/router';
import CaptchaBtn from '@/components/CaptchaBtn';
import Lodash from 'lodash';

export default function LoginForm() {
  const formRef = useRef<FormInstance>();
  const [errorMessage, setErrorMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [loginParams, setLoginParams, removeLoginParams] = useStorage('loginParams');
  const [captcha, setCaptcha] = useState({
    thumbBase64: '',
    imageBase64: '',
    captchaKey: '',
  });
  const [captStatus, setCaptStatus] = useState('default');
  const [captAutoRefreshCount, setCaptAutoRefreshCount] = useState(0);
  const router = useRouter();

  const t = useLocale(locale);

  const [rememberPassword, setRememberPassword] = useState(!!loginParams);
  function afterLoginSuccess(params) {
    // 记住密码
    if (rememberPassword) {
      setLoginParams(JSON.stringify(params));
    } else {
      removeLoginParams();
    }
    window.location.href = '/';
  }

  function handleRequestCaptCode() {
    getCaptcha({username: 'x'}).then(res => {
      setCaptcha(res.data);
    });
  }

  /**
   * 处理验证码校验请求
   */
  function handleConfirm(dots) {
    if (Lodash.size(dots) <= 0) {
      Message.warning(`请进行人机验证再操作`);
      return;
    }

    let dotArr = [];
    Lodash.forEach(dots, (dot) => {
      dotArr.push(dot.x, dot.y);
    });
    verifyCaptcha({captchaKey: captcha.captchaKey, dots: dotArr.join(',')}).then((res)=>{
      const {data = {}} = res;
      if (data.ok) {
        Message.success(`人机验证成功`);
        setCaptStatus('success');
        setCaptAutoRefreshCount(0);
      } else {
        Message.warning(`人机验证失败`);
        if (captAutoRefreshCount > 5) {
          setCaptStatus('overing');
          setCaptAutoRefreshCount(0);
          return;
        }
        handleRequestCaptCode();
        setCaptStatus('error');
        setCaptAutoRefreshCount(v => v + 1);
      }
    });
  }

  function login(params) {
    setErrorMessage('');
    setLoading(true);
    params.captchaKey = captcha.captchaKey;
    Login(params)
      .then((res) => {
        setAccessToken(res.data.token);
        afterLoginSuccess(params);
      })
      .catch((res) => {
        if (res.response.data.reason === 'USER_DISABLE') {
          Message.error(t['login.form.login.errMsg2']);
        } else if (res.response.data.reason === 'CAPTCHA_ERROR') {
          Message.error(t['login.form.login.errMsg3']);
        } else {
          Message.error(t['login.form.login.errMsg']);
        }
        setCaptStatus('default');
      })
      .finally(() => {
        setLoading(false);
      });
  }

  function onSubmitClick() {
    formRef.current.validate().then((values) => {
      login(values);
    });
  }

  // 读取 localStorage，设置初始值
  useEffect(() => {
    const rememberPassword = !!loginParams;
    setRememberPassword(rememberPassword);
    if (formRef.current && rememberPassword) {
      const parseParams = JSON.parse(loginParams);
      formRef.current.setFieldsValue(parseParams);
    }
  }, [loginParams]);

  return (
    <div className={styles['login-form-wrapper']}>
      <div className={styles['login-form-title']}>{t['login.form.title']}</div>
      <div className={styles['login-form-error-msg']}>{errorMessage}</div>
      <Form
        className={styles['login-form']}
        layout="vertical"
        ref={formRef}
      >
        <Form.Item
          field="username"
          rules={[{ required: true, message: t['form.userName.errMsg'] }]}
        >
          <Input
            prefix={<IconUser />}
            placeholder={t['form.userName.placeholder']}
            onPressEnter={onSubmitClick}
          />
        </Form.Item>
        <Form.Item
          field="password"
          rules={[{ required: true, message: t['form.password.errMsg'] }]}
        >
          <Input.Password
            prefix={<IconLock />}
            placeholder={t['form.password.placeholder']}
            onPressEnter={onSubmitClick}
          />
        </Form.Item>
        <Space size={16} direction="vertical">
          <div className={styles['login-form-password-actions']}>
            <Checkbox checked={rememberPassword} onChange={setRememberPassword}>
              {t['login.form.rememberPassword']}
            </Checkbox>
            <Link>{t['login.form.forgetPassword']}</Link>
          </div>
          <CaptchaBtn
            value={captStatus}
            width="100%"
            height="50px"
            imageBase64={captcha.imageBase64}
            thumbBase64={captcha.thumbBase64}
            changeValue={(val) => setCaptStatus(val)}
            confirm={handleConfirm}
            refresh={handleRequestCaptCode}
          />
          <Button type="primary" long onClick={onSubmitClick} loading={loading}>
            {t['login.form.login']}
          </Button>
          <Button
            type="text"
            long
            className={styles['login-form-register-btn']}
            onClick={() => router.push('/user/register') }
          >
            {t['login.form.register']}
          </Button>
        </Space>
      </Form>
    </div>
  );
}
