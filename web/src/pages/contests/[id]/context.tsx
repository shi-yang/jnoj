import { createContext } from 'react';

const ContestContext = createContext({
  id: 0,
  name: '',
  startTime: new Date(),
  endTime: new Date(),
  privacy: '',
  membership: '',
  role: '',
  type: 0,
  groupId: 0,
  participantCount: 0,
  runningStatus: '',
  invitationCode: '',
  description: '',
  owner: {
    id: 0,
    type: '',
    name: '',
  }
});

export default ContestContext;
