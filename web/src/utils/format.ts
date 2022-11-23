import dayjs from 'dayjs'

export const FormatTime = (time: string | number | Date | dayjs.Dayjs):string => {
  return dayjs(time).format('YYYY-MM-DD HH:mm:ss')
}

export const FormatStorageSize = (size:number):string =>  {
  if (size < 1024) {
    return size + ' Byte';
  } else if (size < 1024 * 1024) {
    return (size / 1024).toFixed(2) + 'KB';
  } else if (size < 1024 * 1024 * 1024) {
    return (size / (1024 * 1024)).toFixed(2) + 'MB';
  }
  return (size / (1024 * 1024 * 1024)).toFixed(2) + 'GB';
}
