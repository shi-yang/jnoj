import { createContext } from 'react';

const ProblemContext = createContext({
  problem: {
    id: 0,
    type: 'DEFAULT',
    sampleTests: []
  },
  updateProblem: (any) => {}
});

export default ProblemContext;
