import { Descriptions, Modal, Tooltip } from '@arco-design/web-react';
import React, { useState, forwardRef } from 'react';
import { useImperativeHandle } from 'react';
import Logo from '@/assets/logo.png';
import dayjs from 'dayjs';
import duration from 'dayjs/plugin/duration';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/zh-cn';
import { queryContestSpecialEffects } from '@/api/contest';

dayjs.locale('zh-cn');
dayjs.extend(relativeTime);
dayjs.extend(duration);

function formatDurationFromString(durationString) {
  const seconds = parseInt(durationString, 10);
  return formatDuration(seconds);
}

function formatDuration(seconds) {
  const durationObject = dayjs.duration(seconds, 'seconds');
  return durationObject.humanize();
}

const ContestAnimationModal = function(props: any, ref: any) {
  const [visible, setVisible] = useState(false);
  const [data, setData] = useState([]);

  function run(id: number) {
    queryContestSpecialEffects(id).then(res => {
      const d = res.data;
      if (d.akTime) {
        setVisible(true);
        setData([
          {
            label: '比赛名称',
            value: d.contestName,
          },
          {
            label: '选手',
            value: d.userName,
          },
          {
            label: '比赛时长',
            value: formatDurationFromString(d.contestDuration),
          },
          {
            label: 'AK用时',
            value: formatDurationFromString(d.akTime),
          },
        ]);
      }
    });
  }
  function onCancel() {
    setVisible(false);
  }

  useImperativeHandle(ref, () => ({
    run: run,
  }));

  return (
    <Modal
      title={
        <img style={{height: 21 }} src={Logo.src} alt='logo' />
      }
      visible={visible}
      maskClosable={false}
      onCancel={onCancel}
      footer={null}
    >
      <h2 style={{textAlign: 'center'}}>恭喜你在本场比赛中
        <Tooltip content='All-Killed的缩写，即做对了所有题目'>
          AK！
        </Tooltip>
      </h2>
      <Descriptions
        column={1}
        data={data}
        style={{ marginBottom: 20 }}
        labelStyle={{ paddingRight: 36 }}
      />
    </Modal>
  );
};

export default forwardRef(ContestAnimationModal);
