import { Button } from '@arco-design/web-react';
import QueueAnim from 'rc-queue-anim';
import TweenOne from 'rc-tween-one';
import React from 'react';
import styles from './style/index.module.less';

const isImg = /^http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w-./?%&=]*)?/;
export const getChildrenToRender = (item, i) => {
  let tag = item.name.indexOf('title') === 0 ? 'h1' : 'div';
  tag = item.href ? 'a' : tag;
  let children = typeof item.children === 'string' && item.children.match(isImg)
    ? React.createElement('img', { src: item.children, alt: 'img' })
    : item.children;
  if (item.name.indexOf('button') === 0 && typeof item.children === 'object') {
    children = React.createElement(Button, {
      ...item.children
    });
  }
  return React.createElement(tag, { key: i.toString(), ...item }, children);
};


const bannerWrapper = [
  {
    name: 'title',
    children: (
      <span>
        <p>JNOJ</p>
      </span>
    ),
    className: styles['banner-title'],
  },
  {
    name: 'explain',
    className: styles['banner-explain'],
    children: '在线测评系统',
  },
  {
    name: 'content',
    className: styles['banner-content'],
    children: '支持OI、ICPC模式，基于React、Golang，开源，好用的测评系统',
  },
];
export default function Index() {
  const animType = {
    queue: 'bottom',
    one: {
      y: '+=30',
      opacity: 0,
      type: 'from' as const,
      ease: 'easeOutQuad',
    },
  };
  return (
    <>
      <div className={styles['banner']}>
        <div className={styles['banner-page']}>
          <QueueAnim
            key="text"
            leaveReverse
            ease={['easeOutQuad', 'easeInQuad']}
            className={styles['banner-title-wrapper']}
          >
            {bannerWrapper.map(getChildrenToRender)}
          </QueueAnim>
          <TweenOne animation={animType.one} key="title" className={styles['banner-image']}>
            <img src="https://gw.alipayobjects.com/mdn/rms_ae7ad9/afts/img/A*-wAhRYnWQscAAAAAAAAAAABkARQnAQ" width="100%" alt="img" />
          </TweenOne>
        </div>
      </div>
    </>
  );
}
