import type { AppProps } from 'next/app';
import { wrapper } from '@/store';
import MainLayout from '@/components/Layouts/MainLayout';
import useStorage from '@/utils/useStorage';
import { useRouter } from 'next/router';
import React, { useEffect, useMemo } from 'react';
import NProgress from 'nprogress';
import { ConfigProvider } from '@arco-design/web-react';
import zhCN from '@arco-design/web-react/es/locale/zh-CN';
import enUS from '@arco-design/web-react/es/locale/en-US';
import { GlobalContext } from '../context';
import changeTheme from '@/utils/changeTheme';
import cookies from 'next-cookies';
import { Provider } from 'react-redux';
import '../style/global.less';

interface RenderConfig {
  arcoLang?: string;
  arcoTheme?: string;
}

const PageLayout = ({ Component, pageProps }: any) => {
  if (Component.getLayout) {
    return Component.getLayout(<Component {...pageProps} />);
  } else {
    // default layout
    return (
      <MainLayout>
        <Component {...pageProps} />
      </MainLayout>
    );
  }
};

function MyApp({ renderConfig, Component, ...rest }: { renderConfig: RenderConfig } & AppProps) {
  const { arcoLang, arcoTheme } = renderConfig;
  const [lang, setLang] = useStorage('arco-lang', arcoLang || 'zh-CN');
  const [theme, setTheme] = useStorage('arco-theme', arcoTheme || 'light');
  const router = useRouter();
  const { store, props } = wrapper.useWrappedStore(rest);
  const { pageProps } = props;
  const locale = useMemo(() => {
    switch (lang) {
      case 'zh-CN':
        return zhCN;
      case 'en-US':
        return enUS;
      default:
        return zhCN;
    }
  }, [lang]);
  
  useEffect(() => {
    const handleStart = () => {
      NProgress.set(0.4);
      NProgress.start();
    };

    const handleStop = () => {
      NProgress.done();
    };

    router.events.on('routeChangeStart', handleStart);
    router.events.on('routeChangeComplete', handleStop);
    router.events.on('routeChangeError', handleStop);

    return () => {
      router.events.off('routeChangeStart', handleStart);
      router.events.off('routeChangeComplete', handleStop);
      router.events.off('routeChangeError', handleStop);
    };
  }, [router]);

  useEffect(() => {
    document.cookie = `arco-lang=${lang}; path=/`;
    document.cookie = `arco-theme=${theme}; path=/`;
    changeTheme(theme);
  }, [lang, theme]);

  const contextValue = {
    lang,
    setLang,
    theme,
    setTheme,
  };

  return (
    <ConfigProvider
      locale={locale}
      componentConfig={{
        Card: {bordered: false},
        List: {bordered: false},
        Table: {border: false},
      }}
    >
      <Provider store={store}>
        <GlobalContext.Provider value={contextValue}>
          <PageLayout Component={Component} pageProps={pageProps} />
        </GlobalContext.Provider>
      </Provider>
    </ConfigProvider>
  );
}

MyApp.getInitialProps = async (appContext) => {
  const { ctx } = appContext;
  const serverCookies = cookies(ctx);
  return {
    renderConfig: {
      arcoLang: serverCookies['arco-lang'],
      arcoTheme: serverCookies['arco-theme'],
    },
  };
};

export default MyApp;
