import React from 'react';
import ObjectiveProblem from './ObjectiveProblem';
import ProgrammingProblem from './ProgrammingProblem';

export default function ProblemContent({problem, language}: any) {
  if (problem.type === 'OBJECTIVE') {
    return <ObjectiveProblem problem={problem} language={language} />;
  } else {
    return <ProgrammingProblem problem={problem} language={language} />;
  }
}
