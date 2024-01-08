import { Descriptions, Modal, Tooltip } from '@arco-design/web-react';
import React, { useState, forwardRef } from 'react';
import { useImperativeHandle } from 'react';
import Logo from '@/assets/logo.png';
import { listContestEvents, getContest, getContestEvent } from '@/api/contest';
import { FormatTime } from '@/utils/format';

const ContestEventModal = function(props: any, ref: any) {
  const [visible, setVisible] = useState(false);
  const [data, setData] = useState([]);
  const [event, setEvent] = useState({
    user: {} as any
  } as any);

  async function fetchData(contestId: number, id?:number, userId?:number) {
    const contest = await getContest(contestId);
    if (id && id != 0) {
      const event = await getContestEvent(contestId, id);
      setVisible(true);
      setEvent(event.data);
      setData([
        {
          label: '比赛名称',
          value: contest.data.name,
        },
        {
          label: '选手',
          value: event.data.user.name,
        },
        {
          label: '取得时间',
          value: FormatTime(event.data.createdAt)
        }
      ]);
    } else {
      const eventList = await listContestEvents(contestId, {userId: userId});
      for (const e of eventList.data.data) {
        if (e.type === 'AK') {
          setVisible(true);
          setEvent(e);
          setData([
            {
              label: '比赛名称',
              value: contest.data.name,
            },
            {
              label: '选手',
              value: e.user.name,
            },
            {
              label: '取得时间',
              value: FormatTime(e.createdAt)
            }
          ]);
          return;
        }
      }
    }
  }

  function run(contestId: number, id?:number, userId?:number) {
    fetchData(contestId, id, userId);
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
      escToExit
      footer={null}
    >
      <h2 style={{textAlign: 'center'}}>恭喜[{event.user.name}]在本场比赛中
        {event.type === 'AK' ? (
          <Tooltip content='All-Killed的缩写，即做对了所有题目'>
            AK！
          </Tooltip>
        ) : (
          <span>
            最快答题
          </span>
        )}
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

export default forwardRef(ContestEventModal);
