import React, { useEffect, useState } from 'react';
import {
  Tabs,
  Typography,
  Grid,
  ResizeBox,
  Select,
} from '@arco-design/web-react';
import axios from 'axios';
import useLocale from '@/utils/useLocale';
import locale from './locale';
import styles from './style/index.module.less';
import './mock';
import Editor from './editor';
import Description from './description'
const TabPane = Tabs.TabPane;

function BasicProfile() {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState({ id: 0, name: '', sampleTests: [] });
  const [activeTab, setActiveTab] = useState('1');
  function fetchData() {
    setLoading(true);
    axios
      .get('/problems/123')
      .then((res) => {
        setData(res.data || {});
        console.log('123', res)
      })
      .finally(() => {
        setLoading(false);
      });
  }

  useEffect(() => {
    fetchData();
  }, []);

  const languageOptions = ['C++', 'C', 'Java', 'Python']

  return (
    <div className={styles.container}>
      <Grid.Row className={styles.header} justify="space-between" align="center">
        <Grid.Col span={24}>
          <Typography.Title className={styles.title} heading={5}>
           { data.id } - { data.name }
          </Typography.Title>
        </Grid.Col>
      </Grid.Row>
      <ResizeBox.Split
        max={0.8}
        min={0.2}
        style={{ height: '100%' }}
        panes={[
          <div key='first' className={styles.left}>
            <Tabs className={styles['tabs-container']} style={{ height: '100%' }} activeTab={activeTab} onChange={setActiveTab}>
              <TabPane key='1' className={styles['tabs-pane']} style={{ height: '100%' }} title='题目描述'>
                <Description problem={data} />
              </TabPane>
            </Tabs>
          </div>,
          <div key='second' className={styles.right}>
            <div className={styles['code-header']}>
              <Select placeholder='请选择语言' style={{ width: 154 }} className={styles['aaa']}>
                {languageOptions.map((item, index) => {
                  return (
                    <Select.Option key={item} disabled={index === 3} value={item}>
                      {item}
                    </Select.Option>
                  )
                })}
              </Select>
            </div>
            <Editor />
          </div>,
        ]}
      />
    </div>
  );
}

export default BasicProfile;
