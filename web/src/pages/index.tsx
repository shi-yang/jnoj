import dynamic from 'next/dynamic'

export default dynamic(() => import('./home/index'), {
  ssr: false,
})
