import { getUserProfileCalendar } from '@/api/user';
import useLocale from '@/utils/useLocale';
import { Card, Space, Select, Statistic } from '@arco-design/web-react';
import React, { useState, useEffect } from 'react';
import locale from './locale';
import CalHeatmap from 'cal-heatmap';
import 'cal-heatmap/cal-heatmap.css';
// @ts-ignore https://github.com/wa0x6e/cal-heatmap/issues/366
import CalTooltip from 'cal-heatmap/plugins/Tooltip';

export default function SubmissionCalHeatmap({id}:{id:Number}) {
  const t = useLocale(locale);
  const [calendarSelectYear, setCalendarSelectYear] = useState(0);
  const [calendarOptions, setCalendarOptions] = useState([]);
  const [profileCalendar, setProfileCalendar] = useState({
    submissionCalendar: [],
    totalSubmission: 0,
    totalProblemSolved: 0,
    totalActiveDays: 0,
    start: '',
    end: '',
  });
  const cal = new CalHeatmap();
  useEffect(() => {
    if (id === 0) {
      return;
    }
    getUserProfileCalendar(id).
      then(res => {
        const { data } = res;
        setProfileCalendar(data);
        paint(data);
        data.activeYears.forEach(item => {
          setCalendarOptions(current => [...current, {
            name: item,
            value: item
          }]);
        });
      });
    return () => {
      cal.destroy();
    };
  }, [id]);
  function paint(data:any) {
    const div = document.getElementById('cal-heatmap');
    if (div) {
      while (div.firstChild) {
        div.removeChild(div.firstChild);
      }
    }
    cal.paint(
      {
        data: {
          source: data.submissionCalendar,
          x: 'date',
          y: 'count',
        },
        date: { start: new Date(data.start), locale: 'zh' },
        range: 12,
        animationDuration: 100,
        scale: { color: { type: 'diverging', scheme: 'PRGn', domain: [-10, 15] } },
        domain: {
          type: 'month',
        },
        subDomain: { type: 'day', radius: 2, height: 12, width: 12 },
        itemSelector: '#cal-heatmap',
      },
      [
        [
          CalTooltip,
          {
            // @ts-ignore
            text: function (date, value, dayjsDate) {
              return (
                (value ? value + '次提交' : '没有提交') + ' - ' + dayjsDate.format('LL')
              );
            },
          },
        ],
      ]
    );
  }
  function onCalendarSelectChange(e) {
    setCalendarSelectYear(e);
    getUserProfileCalendar(id, { year: e })
      .then(res => {
        const { data } = res;
        setProfileCalendar(data);
        paint(data);
      });
  }
  return (
    <Card
      title={(calendarSelectYear === 0 ? t['pastYear'] : calendarSelectYear) + '年度做题统计'}
      extra={
        <div>
          <Space>
            <Select style={{ width: 154 }} defaultValue={0} onChange={onCalendarSelectChange}>
              <Select.Option value={0}>
                {t['pastYear']}
              </Select.Option>
              {calendarOptions.map((option, index) => (
                <Select.Option key={index} value={option.value}>
                  {option.name}
                </Select.Option>
              ))}
            </Select>
          </Space>
        </div>
      }
    >
      <Space style={{minWidth: '355px', marginBottom: '20px'}}>
        <Statistic title={t['problemSolved']} value={profileCalendar.totalProblemSolved} groupSeparator style={{ marginRight: 60 }} />
        <Statistic title={t['totalSubmission']} value={profileCalendar.totalSubmission} groupSeparator style={{ marginRight: 60 }} />
        <Statistic title={t['activeDays']} value={profileCalendar.totalActiveDays} groupSeparator style={{ marginRight: 60 }} />
      </Space>
      <div id='cal-heatmap'></div>
    </Card>
  );
}
