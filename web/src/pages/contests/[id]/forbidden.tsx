import { Empty } from "@arco-design/web-react"
import { IconExclamation } from "@arco-design/web-react/icon"

export default () => {
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
        description='您尚未报名参加该比赛，请先参赛，或比赛结束后再来访问'
      />
    </div>
  )
}
