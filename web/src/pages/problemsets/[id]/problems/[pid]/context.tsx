import { createContext } from 'react';

const ProblemContext = createContext({
  problem: {
    id: 0,
    type: 'DEFAULT',
    sampleTests: [],
    statements: [],
  },
  language: 0,
  fetchProblem: (params: any) => {}
});

export default ProblemContext;
