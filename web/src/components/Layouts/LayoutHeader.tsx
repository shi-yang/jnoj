import { useContext, useEffect } from 'react';
import { Avatar, Divider, Dropdown, Menu, Message, Select, Tooltip } from '@arco-design/web-react';
import styles from './style/layout-header.module.less';
import useLocale from '@/utils/useLocale';
import { GlobalContext } from '@/context';
import IconButton from './IconButton';
import { IconLanguage, IconMoonFill, IconPoweroff, IconSettings, IconSunFill, IconUser } from '@arco-design/web-react/icon';
import defaultLocale from '@/locale';
import useStorage from '@/utils/useStorage';
import { Link, useNavigate } from 'react-router-dom';
import { getUserInfo, userInfo } from '@/store/reducers/user';
import { useAppDispatch, useAppSelector } from '@/hooks';
import { isLogged } from '@/utils/auth';

const MenuItem = Menu.Item;

const LayoutHeader = () => {
  const t = useLocale();
  const user = useAppSelector(userInfo);
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  
  const [_, setUserStatus] = useStorage('userStatus');

  const { setLang, lang, theme, setTheme } = useContext(GlobalContext);

  function logout() {
    setUserStatus('logout');
    localStorage.removeItem('token');
    window.location.href = '/login';
  }

  function onMenuItemClick(key) {
    if (key === 'logout') {
      logout();
    } else {
      navigate(`/user/${key}`);
    }
  }
  useEffect(() => {
    dispatch(getUserInfo());
  }, []);

  const droplist = (
    <Menu onClickMenuItem={onMenuItemClick}>
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
        <Menu mode='horizontal' ellipsis={false} defaultSelectedKeys={['1']}>
          <MenuItem
            key='0'
            style={{ padding: 0, marginRight: 38, }}
            disabled
          >
            <div
              style={{
                width: 80,
                height: 30,
                borderRadius: 2,
                background: 'var(--color-fill-3)',
                cursor: 'text',
              }}
            />
          </MenuItem>
          <MenuItem key='1'>
            <Link to='/'>{ t['menu.home'] }</Link>
          </MenuItem>
          <MenuItem key='2'>
            <Link to='/problems'>{ t['menu.problem'] }</Link>
          </MenuItem>
          <MenuItem key='3'>
            <Link to='/contests'>{ t['menu.contest'] }</Link>
          </MenuItem>
          {/* <MenuItem key='4'>
            <Link to='/about'>{ t['menu.about'] }</Link>
          </MenuItem> */}
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
        { isLogged()
            ? <li>
              <Dropdown droplist={droplist} position="br">
                <div style={{width: '100px'}}>
                  <Avatar size={32} style={{ cursor: 'pointer' }}>
                    <IconUser />
                  </Avatar>
                  <span>{ user.nickname }</span>
                </div>
              </Dropdown>
            </li>
            : <li>
              <Link to='/login'>{ t['navbar.login'] }</Link>
            </li>
        }
      </ul>
    </div>
  );
}

export default LayoutHeader;
