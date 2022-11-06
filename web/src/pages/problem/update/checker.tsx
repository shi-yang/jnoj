import { listProblemCheckers } from '@/api/problem-checker';
import { Form, Input, Button, Checkbox, Select, Card } from '@arco-design/web-react';
import { useEffect, useState } from 'react';
const FormItem = Form.Item;
const Option = Select.Option;
const App = (props) => {
  const [userCheckers, setUserCheckers] = useState([]);
  const [stdCheckers, setStdCheckers] = useState([]);
  function fetchData() {
    listProblemCheckers(props.problem.id)
      .then((res) => {
        setUserCheckers(res.data.user_checkers);
        setStdCheckers(res.data.std_checkers);
      });
  }

  useEffect(() => {
    fetchData();
  }, []);

  return (
    <Card>
      <Form style={{ width: 600 }} autoComplete='off'>
        <FormItem label='选择'>
          <Select showSearch allowClear style={{ width: 300 }}>
            <Select.OptGroup label='std'>
              {stdCheckers.map((option, index) => (
                <Option key={option.id} value={option.id}>
                  {option.name}
                </Option>
              ))}
            </Select.OptGroup>
            <Select.OptGroup label='user'>
              {userCheckers.map((option, index) => (
                <Option key={option.id} value={option.id}>
                  {option.name}
                </Option>
              ))}
            </Select.OptGroup>
          </Select>
        </FormItem>
        <FormItem wrapperCol={{ offset: 5 }}>
          <Button type='primary'>Submit</Button>
        </FormItem>
      </Form>
    </Card>
  );
};

export default App;
