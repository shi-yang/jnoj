import useLocale from "@/utils/useLocale";
import { Tabs } from "@arco-design/web-react";
import Info from "./info";
import locale from "../locale";
import Users from "./users";

const Setting = ({contest}) => {
  const t = useLocale(locale);
  return (
    <Tabs defaultActiveTab='info'>
      <Tabs.TabPane key='info' title={t['setting.tab.info']} destroyOnHide>
        <Info contest={contest} />
      </Tabs.TabPane>
      <Tabs.TabPane key='user' title={t['setting.tab.users']} destroyOnHide>
        <Users contest={contest} />
      </Tabs.TabPane>
    </Tabs>
  )
}
export default Setting;
