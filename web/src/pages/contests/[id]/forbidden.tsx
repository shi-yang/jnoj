import { createContestUser } from "@/api/contest"
import { Button, Divider, Empty, Typography } from "@arco-design/web-react"
import { IconExclamation } from "@arco-design/web-react/icon"

export default ({contest}) => {
  function register() {
    createContestUser(contest.id)
      .then(res => {
        
      })
  }
  return (
    <div>
      <Empty
        icon={
          <div
            style={{
              background: '#f2994b',
              display: 'inline-flex',
              borderRadius: '50%',
              width: 50,
              height: 50,
              fontSize: 30,
              alignItems: 'center',
              color: 'white',
              justifyContent: 'center',
            }}
          >
            <IconExclamation />
          </div>
        }
        description={
          (contest.status === 'HIDDEN' && <>该比赛仅参赛人员可见。</>)
          || (contest.status === 'PRIVATE' && <>该比赛仅参赛人员可见。</>)
          || (contest.status === 'PUBLIC' &&
            <div>
              您尚未报名参加该比赛，请先参赛，或比赛结束后再来访问
              <Divider>参赛协议：</Divider>
              <Typography.Paragraph>1. 不与其他人分享解决方案</Typography.Paragraph>
              <Typography.Paragraph>2. 不以任何形式破坏和攻击测评系统</Typography.Paragraph>
              <Button onClick={register}>同意以上并参加</Button>
            </div>
          )
        }
      />
    </div>
  )
}
