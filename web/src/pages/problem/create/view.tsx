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
import { useParams } from 'react-router-dom';
const TabPane = Tabs.TabPane;

function BasicProfile() {
  const t = useLocale(locale);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState({ id: 0, name: '', sampleTests: [] });
  const [activeTab, setActiveTab] = useState('1');
  const params = useParams();
  function fetchData() {
    setLoading(true);
    axios
      .get(`/problems/${params}`)
      .then((res) => {
        setData(res.data || {});
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

          </div>,
          <div key='second' className={styles.right}>

          </div>,
        ]}
      />
    </div>
  );
}

export default BasicProfile;
