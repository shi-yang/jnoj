import { Button, Card, Tabs, Modal, PaginationProps, Steps, Upload, Space, Typography, Message } from '@arco-design/web-react';
import locale from './locale';
import { batchAddProblemToProblemset, batchAddProblemToProblemsetPreview, listProblemsetProblems } from '@/api/problemset';
import useLocale from '@/utils/useLocale';
import { useState, useEffect } from 'react';
import { IconDownload, IconPlus, IconUpload } from '@arco-design/web-react/icon';
import React from 'react';
import ProblemsList from '@/modules/problemsets/ExamProblemList';

const TabPane = Tabs.TabPane;

function fileToBase64(file, callback) {
  const reader = new FileReader();
  reader.onloadend = function() {
    const base64String = (reader.result as string).split(',')[1];
    callback(base64String);
  };
  reader.readAsDataURL(file);
}

const BatchCreateModal = ({ problemset, callback }: {problemset: any, callback: () => void}) => {
  const [visible, setVisible] = useState(false);
  const [modal, contextHolder] = Modal.useModal();
  function customRequest(option) {
    const { onProgress, onError, onSuccess, file } = option;
    fileToBase64(file, (base64String) => {
      const data = {
        method: 0,
        content: base64String,
      };
      batchAddProblemToProblemsetPreview(problemset.id, data).then(res => {
        onSuccess();
        modal.confirm({
          title: '确认添加',
          style: {width: '800px'},
          content: <div>
            <div>
              <Typography.Paragraph>总数：{res.data.total}，点击“确定”，将会添加解析成功数，如果需要修正解析失败，请点击“取消”或关闭当前对话框，并修正Excel文件后再重新上传。</Typography.Paragraph>
              <Typography.Paragraph>解析成功：{res.data.total - res.data.failedReason.length}</Typography.Paragraph>
              <Typography.Paragraph bold>解析失败：{res.data.failedReason.length}</Typography.Paragraph>
              {res.data.failedReason && res.data.failedReason.map((item, index) => (
                <Typography.Paragraph key={index} style={{ color: 'red' }}>{item}</Typography.Paragraph>
              ))}
            </div>
            <ProblemsList problems={res.data.problems}  />
          </div>,
          onOk: () => {
            batchAddProblemToProblemset(problemset.id, { problems: res.data.problems }).then(res => {
              setVisible(false);
              callback();
            });
          }
        });
      });
    });
  }
  return (
    <>
      {contextHolder}
      <Button onClick={() => setVisible(true)} type='primary' icon={<IconPlus />} long>批量添加</Button>
      <Modal
        title='批量添加'
        style={{ width: 1100 }}
        visible={visible}
        onOk={() => {
          setVisible(false);
        }}
        onCancel={() => {
          setVisible(false);
        }}
      >
        <Tabs defaultActiveTab='template'>
          <TabPane key='template' title='模板上传'>
            <Steps current={0} style={{ maxWidth: 780, marginTop: 40 }}>
              <Steps.Step title='第一步' description={
                <div>
                  将您的文档按照模版中的格式调整好
                  <Button icon={<IconDownload />} onClick={() => Message.info('暂不可用')}>下载Excel模板</Button>
                </div>
              } />
              <Steps.Step title='第二步' description={
                <div>
                  调整好格式后，就可以开始上传文档
                  <Upload customRequest={customRequest} limit={1}>
                    <Space>
                      <Button icon={<IconUpload />} type='primary'>上传Excel文档</Button>
                    </Space>
                  </Upload>
                </div>
              } />
              <Steps.Step title='第三步' description={
                <div>
                  根据Excel生成的内容，确认添加
                </div>
              } />
           </Steps>
          </TabPane>
          <TabPane key='problems' title='题库添加' disabled>
          </TabPane>
        </Tabs>
      </Modal>
    </>
  );
};

const Page = ({problemset}: {problemset:any}) => {
  const t = useLocale(locale);
  const [problems, setProblems] = useState([]);
  const [pagination, setPatination] = useState<PaginationProps>({
    sizeCanChange: true,
    showTotal: true,
    pageSize: 100,
    current: 1,
    pageSizeChangeResetCurrent: true,
  });
  const [loading, setLoading] = useState(true);
  const [formParams, setFormParams] = useState({});

  useEffect(() => {
    fetchData();
  }, [pagination.current, pagination.pageSize, JSON.stringify(formParams)]);

  function fetchData() {
    const { current, pageSize } = pagination;
    setLoading(true);
    const params = {
      page: current,
      perPage: pageSize,
      ...formParams,
    };
    listProblemsetProblems(problemset.id, params)
      .then((res) => {
        setProblems(res.data.data);
        setPatination({
          ...pagination,
          current,
          pageSize,
          total: res.data.total,
        });
        setLoading(false);
      });
  }

  return (
    <Card title='题目列表'>
      <ProblemsList problems={problems} />
      <BatchCreateModal problemset={problemset} callback={fetchData} />
    </Card>
  );
};

export default Page;
