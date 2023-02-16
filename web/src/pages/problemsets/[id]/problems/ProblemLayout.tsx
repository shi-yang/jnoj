import React, { useContext, useEffect, useState } from 'react';
import { Layout, Message, Select, Tooltip } from '@arco-design/web-react';
import { useAppDispatch } from '@/hooks';
import UserAvatar from '@/components/Layouts/UserAvatar';
import styles from './styles/layout.module.less';
import Link from 'next/link';
import Logo from '@/assets/logo.png';
import { GlobalContext } from '@/context';
import { getUserInfo } from '@/store/reducers/user';
import IconButton from '@/components/Layouts/IconButton';
import { IconLanguage, IconMoonFill, IconSunFill } from '@arco-design/web-react/icon';
import defaultLocale from '@/locale';
import useLocale from '@/utils/useLocale';
import { isLogged } from '@/utils/auth';
const { Header, Content } = Layout;

const App = (page) => {
  const t = useLocale();
  const dispatch = useAppDispatch();
  const [isMounted, setIsMounted] = useState(false);
  const { lang, setLang, theme, setTheme } = useContext(GlobalContext);
  useEffect(() => {
    setIsMounted(true);
    dispatch(getUserInfo());
  }, []);
  return (
    <Layout>
      <Header>
        <div className={styles.navbar}>
          <div className={styles.left}>
            <Link href='/'>
              <img style={{height: 21, cursor: 'pointer' }} src={Logo.src} />
            </Link>
          </div>
          <ul className={styles.right}>
            <li>
              <Select
                triggerElement={<IconButton icon={<IconLanguage />} />}
                options={[
                  { label: '中文', value: 'zh-CN' },
                  { label: 'English', value: 'en-US' },
                ]}
                value={lang}
                triggerProps={{
                  autoAlignPopupWidth: false,
                  autoAlignPopupMinWidth: true,
                  position: 'br',
                }}
                trigger="hover"
                onChange={(value) => {
                  setLang(value);
                  const nextLang = defaultLocale[value];
                  Message.info(`${nextLang['message.lang.tips']}${value}`);
                }}
              />
            </li>
            <li>
              <Tooltip
                content={
                  theme === 'light'
                    ? t['settings.navbar.theme.toDark']
                    : t['settings.navbar.theme.toLight']
                }
              >
                <IconButton
                  icon={theme !== 'dark' ? <IconMoonFill /> : <IconSunFill />}
                  onClick={() => setTheme(theme === 'light' ? 'dark' : 'light')}
                />
              </Tooltip>
            </li>
            { isMounted && (isLogged()
                ? <li>
                  <UserAvatar />
                </li>
                : <li>
                  <Link href='/user/login'>{ t['login'] }</Link>
                </li>)
            }
          </ul>
        </div>
      </Header>
      <Content>
        {page}
      </Content>
    </Layout>
  );
};

export default App;
