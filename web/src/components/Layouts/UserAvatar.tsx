import React from 'react';
import { useAppSelector } from '@/hooks';
import { userInfo } from '@/store/reducers/user';
import { removeAccessToken } from '@/utils/auth';
import useLocale from '@/utils/useLocale';
import { Avatar, Button, Divider, Dropdown, Menu } from '@arco-design/web-react';
import { IconPoweroff, IconSettings, IconUser } from '@arco-design/web-react/icon';
import { useRouter } from 'next/router';
import styles from './style/main-layouts.module.less';

export default function UserAvatar() {
  const t = useLocale();
  const user = useAppSelector(userInfo);
  const router = useRouter();
  function logout() {
    removeAccessToken();
    window.location.href = '/user/login';
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
  const droplist = (
    <Menu onClickMenuItem={onDropListMenuItemClick}>
      <Menu.Item key='homepage'>
        <IconUser className={styles['dropdown-icon']} />
        {t['menu.user.home']}
      </Menu.Item>
      <Menu.Item key='setting'>
        <IconSettings className={styles['dropdown-icon']} />
        {t['menu.user.setting']}
      </Menu.Item>
      <Divider style={{ margin: '4px 0' }} />
      <Menu.Item key='logout'>
        <IconPoweroff className={styles['dropdown-icon']} />
        {t['logout']}
      </Menu.Item>
    </Menu>
  );
  
  return (
    <Dropdown droplist={droplist} position='br'>
      <Button type='text'>
        <Avatar size={32} style={{ cursor: 'pointer' }}>
          <IconUser />
        </Avatar>
        <span>{ user.nickname }</span>
      </Button>
    </Dropdown>
  );
}
