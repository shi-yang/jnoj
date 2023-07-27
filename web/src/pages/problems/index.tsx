import React from 'react';
import ProgrammingList from './programming-list';
import ObjectiveList from './objective-list';
import ProblemsetList from './problemset-list';
import Layout from './Layout';
import { Tabs, Tooltip } from '@arco-design/web-react';

function Index() {
  return (
    <Tabs>
      <Tabs.TabPane key='programming' title={
        <Tooltip content='代码编写在线测评题，含标准输入输出、函数题'>
          编程题
        </Tooltip>
      }>
        <div style={{marginTop: 20}}>
          <ProgrammingList />
        </div>
      </Tabs.TabPane>
      <Tabs.TabPane key='objective' title={
        <Tooltip content='客观题，含多选题、判断题、填空题'>
          客观题
        </Tooltip>
      }>
        <div style={{marginTop: 20}}>
          <ObjectiveList />
        </div>
      </Tabs.TabPane>
      <Tabs.TabPane key='exam' title={
        <Tooltip content='创建题单、整张试卷，可包含多道客观题、编程题，可批量创建客观题'>
          题单&试卷集
        </Tooltip>
      }>
        <div style={{marginTop: 20}}>
          <ProblemsetList />
        </div>
      </Tabs.TabPane>
    </Tabs>
  );
}
Index.getLayout = Layout;

export default Index;
