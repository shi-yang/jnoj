import {
  Form,
  Input,
  Checkbox,
  Button,
  Space,
  Message,
  Radio,
} from '@arco-design/web-react';
import { IconCodeSquare, IconEmail, IconLock, IconPhone, IconUser } from '@arco-design/web-react/icon';
import React, { useEffect, useRef, useState } from 'react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/login.module.less';
import { getCaptcha, Register } from '@/api/user';
import { setAccessToken } from '@/utils/auth';
import { useRouter } from 'next/router';

export default function RegisterForm() {
  const [form] = Form.useForm();
  const [errorMessage, setErrorMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [registerMethod, setRegisterMethod] = useState('email'); 
  const t = useLocale(locale);
  const router = useRouter();

  const [time, setTime] = useState(0)
  const timer = useRef(null)

  useEffect(() => {
    timer.current && clearInterval(timer.current);
    return () => timer.current && clearInterval(timer.current);
  }, []);

  useEffect(()=> {
    if (time === 60) {
      timer.current = setInterval(() => setTime(time => --time), 1000);
    } else if (time === 0) {
      timer.current && clearInterval(timer.current);
    }
  }, [time])

  const getCode = () => {
    setTime(60)
    getCaptcha({
      email: form.getFieldValue('email'),
      phone: form.getFieldValue('phone'),
    })
  }
  
  function onSubmit() {
    form.validate().then((values) => {
      setErrorMessage('');
      setLoading(true);
      Register(values)
        .then((res) => {
          const { token } = res.data;
          if (token) {
            setAccessToken(token);
            window.location.href = '/';
          } else {
            setErrorMessage(t['login.form.login.errMsg']);
          }
        })
        .catch(err => {
          Message.info(err.response.data.message)
        })
        .finally(() => {
          setLoading(false);
        });
    });
  }

  return (
    <div className={styles['login-form-wrapper']}>
      <div className={styles['login-form-title']}>{t['register.form.title']}</div>
      <div className={styles['login-form-error-msg']}>{errorMessage}</div>
      <Form
        className={styles['register-form']}
        layout="vertical"
        form={form}
        onSubmit={onSubmit}
      >
        <Form.Item field='registerMethod'>
          <Radio.Group size='large' type='button' defaultValue={registerMethod} value={registerMethod} onChange={setRegisterMethod}>
            <Radio value='email'>
              <IconEmail /> {t['register.form.type.email']}
            </Radio>
            <Radio value='phone' disabled>
              <IconPhone /> {t['register.form.type.phone']}
            </Radio>
          </Radio.Group>
        </Form.Item>
        {
          registerMethod === 'email' ? (
            <Form.Item field='email' rules={[{type: 'email'}, {required: true, type: 'string'}]}>
              <Input prefix={<IconEmail />} placeholder={t['form.email.placeholder']} />
            </Form.Item>
          ) : (
            <Form.Item field='phone' rules={[{required: true}]}>
              <Input prefix={<IconPhone />} placeholder={t['form.phone.placeholder']} />
            </Form.Item>
          )
        }
        <Form.Item
          field="username"
          rules={[
            {
              required: true,
              message: t['form.userName.errMsg'],
            },
            {
              match: /^[a-zA-Z][a-zA-Z0-9_-]{4,15}$/,
              message: t['form.userName.errMsg2']
            },
          ]}
        >
          <Input
            prefix={<IconUser />}
            placeholder={t['form.userName.placeholder']}
          />
        </Form.Item>
        <Form.Item
          field="password"
          rules={[
            { required: true, message: t['form.password.errMsg'] },
            { minLength: 6, maxLength: 16 }
          ]}
        >
          <Input.Password
            prefix={<IconLock />}
            placeholder={t['form.password.placeholder']}
          />
        </Form.Item>
        <Form.Item
          field="captcha"
          rules={[{ required: true, message: t['form.captcha.errMsg'] }]}
        >
          <Input
            prefix={<IconCodeSquare />}
            placeholder={t['form.captcha.placeholder']}
            addAfter={
            <Button disabled={time > 0}
              onClick={async () => {
                try {
                  if (registerMethod === 'email') {
                    await form.validate(['email']);
                  } else {
                    await form.validate(['phone']);
                  }
                  getCode()
                } catch (e) {
                  console.log(e)
                }
              }}
            >{ time ? `${time}秒后获取`: '获取验证码' }</Button>
            }
          />
        </Form.Item>
        <Space size={16} direction="vertical">
          <Button type="primary" htmlType='submit' long loading={loading}>
            {t['login.form.register']}
          </Button>
          <Button
            type="text"
            long
            className={styles['login-form-register-btn']}
            onClick={() => router.push('/user/login') }
          >
            {t['login.form.login']}
          </Button>
        </Space>
      </Form>
    </div>
  );
}
