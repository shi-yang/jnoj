import React from 'react';
import ObjectiveProblem from './ObjectiveProblem';
import ProgrammingProblem from './ProgrammingProblem';

export default function ProblemContent({problem, statement}: any) {
  if (problem.type === 'OBJECTIVE') {
    return <ObjectiveProblem problem={problem} statement={statement} />;
  } else {
    return <ProgrammingProblem problem={problem} statement={statement} />;
  }
}
