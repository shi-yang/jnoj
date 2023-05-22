import React, { useContext, useEffect, useState } from 'react';
import { Menu, Message, Select, Tooltip } from '@arco-design/web-react';
import styles from './style/layout-header.module.less';
import useLocale from '@/utils/useLocale';
import { GlobalContext } from '@/context';
import IconButton from './IconButton';
import { IconCodeSquare, IconHeart, IconHome, IconLanguage, IconList, IconMoonFill, IconSunFill, IconTrophy, IconUserGroup } from '@arco-design/web-react/icon';
import defaultLocale from '@/locale';
import { useRouter } from 'next/router';
import Link from 'next/link';
import { getUserInfo } from '@/store/reducers/user';
import { useAppDispatch } from '@/hooks';
import { isLogged } from '@/utils/auth';
import Logo from '@/assets/logo.png';
import UserAvatar from './UserAvatar';
import PermissionWrapper from '../PermissionWrapper';

const MenuItem = Menu.Item;

const LayoutHeader = () => {
  const t = useLocale();
  const dispatch = useAppDispatch();
  const router = useRouter();
  const [isMounted, setIsMounted] = useState(false);
  const { lang, setLang, theme, setTheme } = useContext(GlobalContext);

  function onMainClickMenuItem(key) {
    router.push(key);
  }

  useEffect(() => {
    setIsMounted(true);
    dispatch(getUserInfo());
  }, []);

  return (
    <div className={styles.navbar}>
      <div className={styles.left}>
        <Menu mode='horizontal' ellipsis={false} defaultSelectedKeys={['1']} onClickMenuItem={onMainClickMenuItem}>
          <MenuItem
            key='0'
            disabled
          >
            <Link href='/'>
              <img style={{height: 21,  cursor: 'pointer' }} src={Logo.src} alt='logo' />
            </Link>
          </MenuItem>
          <MenuItem key='/'>
            <Link href='/'><IconHome /> { t['menu.home'] }</Link>
          </MenuItem>
          <MenuItem key='/problemsets'>
            <Link href='/problemsets'><IconCodeSquare /> { t['menu.problem'] }</Link>
          </MenuItem>
          <MenuItem key='/groups'>
            <Link href='/groups'><IconUserGroup /> { t['menu.group'] }</Link>
          </MenuItem>
          <MenuItem key='/contests'>
            <Link href='/contests'><IconList />{ t['menu.contest'] }</Link>
          </MenuItem>
          <MenuItem key='/rankings'>
            <Link href='/rankings'><IconTrophy />{ t['menu.ranking'] }</Link>
          </MenuItem>
          <MenuItem key='/home/about'>
            <Link href='/home/about'><IconHeart /> { t['menu.about'] }</Link>
          </MenuItem>
        </Menu>
      </div>
      <ul className={styles.right}>
        <PermissionWrapper
          requiredPermissions={[{resource: '*', actions: ['read']}]}
        >
          <li>
            <Link href='/admin'>后台</Link>
          </li>
        </PermissionWrapper>
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
  );
};

export default LayoutHeader;
