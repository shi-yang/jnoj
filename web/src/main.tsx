import { ConfigProvider } from '@arco-design/web-react'
import ReactDOM from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import zhCN from '@arco-design/web-react/es/locale/zh-CN';
import enUS from '@arco-design/web-react/es/locale/en-US';
import App from './App'
import useStorage from './utils/useStorage';
import { GlobalContext } from './context';
import { Provider } from 'react-redux';
import store from './store';
import './style/global.less';

const Index = () => {
  const [lang, setLang] = useStorage('arco-lang', 'zh-CN');
  const [theme, setTheme] = useStorage('arco-theme', 'light');

  function getArcoLocale() {
    switch (lang) {
      case 'zh-CN':
        return zhCN;
      case 'en-US':
        return enUS;
      default:
        return zhCN;
    }
  }

  const contextValue = {
    lang,
    setLang,
    theme,
    setTheme,
  };

  return (
    <BrowserRouter>
      <ConfigProvider
        locale={getArcoLocale()}
        componentConfig={{
          Card: {bordered: false},
          List: {bordered: false},
          Table: {border: false},
        }}
      >
        <Provider store={store}>
          <GlobalContext.Provider value={contextValue}>
            <App />
          </GlobalContext.Provider>
        </Provider>
      </ConfigProvider>
    </BrowserRouter>
  )
}

ReactDOM.createRoot(document.getElementById('root') as HTMLElement).render(<Index />)
