import RenderRouter from './routes/routes';
import { Suspense } from 'react';

function App() {
  return (
    <Suspense fallback={<h2>Loading..</h2>}>
      <RenderRouter />
    </Suspense>
  )
}

export default App
