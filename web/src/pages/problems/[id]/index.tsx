import React, { useEffect, useState } from 'react';
import {
  Tabs,
  Typography,
  Grid,
  ResizeBox,
  Select,
} from '@arco-design/web-react';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import Editor from './editor';
import Description from './description';
import Submission from './submission';
import { getProblem } from '@/api/problem';
import { IconLanguage } from '@arco-design/web-react/icon';
import { useRouter } from 'next/router';
import Head from 'next/head';
import { useAppSelector } from '@/hooks';
import { setting, SettingState } from '@/store/reducers/setting';
const TabPane = Tabs.TabPane;

function Index() {
  const t = useLocale(locale);
  const settings = useAppSelector<SettingState>(setting)
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState({
    id: 0,
    name: '',
    statements: []
  });
  const [language, setLanguage] = useState(0);
  const [languageOptions, setLanguageOptions] = useState([]);
  const router = useRouter();
  const { id } = router.query
  function fetchData() {
    setLoading(true);
    getProblem(id)
      .then((res) => {
        setData(res.data);
        res.data.statements.forEach((item, index) => {
          setLanguageOptions((prev) => [...prev, {
            label: item.language,
            value: index,
          }])
        })
      })
      .finally(() => {
        setLoading(false);
      });
  }

  useEffect(() => {
    fetchData();
  }, []);

  return (
    <>
      { !loading && (
        <>
          <Head>
            <title>{ data.id }.{ data.statements[language].name } - {settings.name}</title>
          </Head>
          <div className={styles.container}>
            <Grid.Row className={styles.header} justify="space-between" align="center">
              <Grid.Col span={24}>
                <Typography.Title className={styles.title} heading={5}>
                  { data.id } - { data.statements[language].name }
                </Typography.Title>
              </Grid.Col>
            </Grid.Row>
            <ResizeBox.Split
              max={0.8}
              min={0.2}
              style={{ height: 'calc(100% - 69px)' }}
              panes={[
                <div key='first' className={styles.left}>
                  <Tabs
                    className={styles['tabs-container']}
                    destroyOnHide
                    extra={
                      languageOptions.length > 1 &&
                      <>
                        <Select
                          bordered={false}
                          size='small'
                          defaultValue={language}
                          onChange={(value) =>
                            setLanguage(value)
                          }
                          triggerProps={{
                            autoAlignPopupWidth: false,
                            autoAlignPopupMinWidth: true,
                            position: 'bl',
                          }}
                          triggerElement={
                            <span className={styles['header-language']}>
                              <IconLanguage /> {languageOptions[language].label}
                            </span>
                          }
                        >
                          {languageOptions.map((option, index) => (
                            <Select.Option key={index} value={option.value}>
                              {option.label}
                            </Select.Option>
                          ))}
                        </Select>
                      </>
                    }
                  >
                    <TabPane key='problem' className={styles['tabs-pane']} title={t['tab.description']}>
                      <Description problem={data} language={language} />
                    </TabPane>
                    <TabPane key='submission' className={styles['tabs-pane']} title={t['tab.submissions']}>
                      <Submission problem={data} />
                    </TabPane>
                  </Tabs>
                </div>,
                <div key='second' className={styles.right}>
                  <Editor problem={data} />
                </div>,
              ]}
            />
          </div>
        </>
      )}
    </>
  );
}

export default Index;
