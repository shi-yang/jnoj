import { listUsers } from '@/api/user';
import { AutoComplete, Divider, Input, Space } from '@arco-design/web-react';
import React, { useState } from 'react';
const { Option } = AutoComplete;

// 输入用户名，自动补全用户名
export default function SearchInputUsername(props: any) {
  const value = props.value || '';
  const [data, setData] = useState([]);

  const handleChange = (newValue) => {
    props.onChange && props.onChange(newValue);
  };

  const handleSearch = (inputValue) => {
    if (inputValue) {
      listUsers({keywords: inputValue}).then((res) => {
        setData(
          res.data.data.map((item, index) => {
            return (
              <Option key={index} value={item.username}>
                <Space split={<Divider type='vertical' />}>
                  <span>ID：{item.id}</span>
                  <span>用户名：{item.username}</span>
                  {item.realname !== '' && <span>真实姓名：{item.realname}</span>}
                  <span>昵称：{item.nickname}</span>
                </Space>
              </Option>
            );
          })
        );
      });
    } else {
      setData([]);
    }
  };

  return (
    <AutoComplete
      data={data}
      value={value}
      placeholder='请输入用户名'
      triggerElement={<Input.Search />}
      onSearch={handleSearch}
      onChange={handleChange}
    />
  );
}
