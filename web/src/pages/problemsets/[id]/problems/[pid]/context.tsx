import { createContext } from 'react';

const ProblemContext = createContext({
  problem: {
    id: 0,
    sampleTests: []
  },
  updateProblem: (any) => {}
});

export default ProblemContext;
