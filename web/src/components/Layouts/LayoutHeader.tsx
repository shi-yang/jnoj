import { useContext, useEffect, useState } from 'react';
import { Avatar, Button, Divider, Dropdown, Menu, Message, Select, Tooltip } from '@arco-design/web-react';
import styles from './style/layout-header.module.less';
import useLocale from '@/utils/useLocale';
import { GlobalContext } from '@/context';
import IconButton from './IconButton';
import { IconCodeSquare, IconHeart, IconHome, IconLanguage, IconList, IconMoonFill, IconPoweroff, IconSettings, IconSunFill, IconUser, IconUserGroup } from '@arco-design/web-react/icon';
import defaultLocale from '@/locale';
import { useRouter } from 'next/router';
import Link from 'next/link';
import { getUserInfo, userInfo } from '@/store/reducers/user';
import { useAppDispatch, useAppSelector } from '@/hooks';
import { isLogged, removeAccessToken } from '@/utils/auth';
import Logo from '@/assets/logo.png';

const MenuItem = Menu.Item;

const LayoutHeader = () => {
  const t = useLocale();
  const user = useAppSelector(userInfo);
  const dispatch = useAppDispatch();
  const router = useRouter()
  const [isMounted, setIsMounted] = useState(false)
  const { lang, setLang, theme, setTheme } = useContext(GlobalContext);

  function logout() {
    removeAccessToken();
    window.location.href = '/user/login';
  }

  function onMainClickMenuItem(key) {
    router.push(key);
  }

  function onDropListMenuItemClick(key) {
    if (key === 'logout') {
      logout();
    } else if (key === 'homepage') {
      router.push(`/u/${user.id}`);
    } else {
      router.push(`/user/${key}`);
    }
  }
  useEffect(() => {
    setIsMounted(true);
    dispatch(getUserInfo());
  }, []);

  const droplist = (
    <Menu onClickMenuItem={onDropListMenuItemClick}>
      <Menu.Item key="homepage">
        <IconUser className={styles['dropdown-icon']} />
        {t['menu.user.home']}
      </Menu.Item>
      <Menu.Item key="setting">
        <IconSettings className={styles['dropdown-icon']} />
        {t['menu.user.setting']}
      </Menu.Item>
      <Divider style={{ margin: '4px 0' }} />
      <Menu.Item key="logout">
        <IconPoweroff className={styles['dropdown-icon']} />
        {t['navbar.logout']}
      </Menu.Item>
    </Menu>
  );
  return (
    <div className={styles.navbar}>
      <div className={styles.left}>
        <Menu mode='horizontal' ellipsis={false} defaultSelectedKeys={['1']} onClickMenuItem={onMainClickMenuItem}>
          <MenuItem
            key='0'
            disabled
          >
            <Link href='/'>
              <img style={{height: 21, cursor: 'pointer' }} src={Logo.src} />
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
          <MenuItem key='/home/about'>
            <Link href='/home/about'><IconHeart /> { t['menu.about'] }</Link>
          </MenuItem>
        </Menu>
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
              <Dropdown droplist={droplist} position="br">
                <Button type='text' style={{width: '100px'}}>
                  <Avatar size={32} style={{ cursor: 'pointer' }}>
                    <IconUser />
                  </Avatar>
                  <span>{ user.nickname }</span>
                </Button>
              </Dropdown>
            </li>
            : <li>
              <Link href='/user/login'>{ t['navbar.login'] }</Link>
            </li>)
        }
      </ul>
    </div>
  );
}

export default LayoutHeader;
