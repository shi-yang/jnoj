import { createContext } from 'react';

const ContestContext = createContext({
  id: 0,
  name: '',
  startTime: new Date(),
  endTime: new Date(),
  frozenTime: null,
  virtualStart: null,
  privacy: '',
  membership: '',
  role: '',
  type: '',
  groupId: 0,
  participantCount: 0,
  runningStatus: '',
  invitationCode: '',
  description: '',
  owner: {
    id: 0,
    type: '',
    name: '',
  },
  feature: '',
  problems: [],
  updateContest: ({} : any) => {},
});

export default ContestContext;
