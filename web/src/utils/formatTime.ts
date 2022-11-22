import dayjs from 'dayjs'

export const FormatTime = (time) => {
    return dayjs(time).format('YYYY-MM-DD HH:mm:ss')
}
